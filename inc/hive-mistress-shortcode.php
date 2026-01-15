<?php
/**
 * Hive Mistress Conditioning System V2
 *
 * Admin: Settings → Hive Mistress (configure API key, view logs)
 * User: [hive_mistress_chat] shortcode
 *
 * PROMPTS MANAGED VIA THEME: inc/hive-mistress-prompts.php
 *
 * Migrated from WPCode snippet #2495 to git for version control.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// AJAX HANDLER - SEND MESSAGE
// ============================================================================

add_action('wp_ajax_hm_send_message', 'hm_send_message');

function hm_send_message() {
    $user_id = get_current_user_id();

    if (!$user_id) {
        wp_send_json_error(['message' => 'Please log in to continue']);
        return;
    }

    $api_key = get_option('hm_api_key', '');
    $messages = json_decode(stripslashes($_POST['messages']), true);

    // GET PROMPT FROM THEME
    $system_prompt = function_exists('hm_build_complete_prompt')
        ? hm_build_complete_prompt()
        : get_option('hm_system_prompt', 'You are the Hive Mistress.');

    if (!$api_key || !$messages) {
        wp_send_json_error(['message' => 'Configuration error. Contact administrator.']);
        return;
    }

    $response = wp_remote_post('https://api.anthropic.com/v1/messages', [
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key' => $api_key,
            'anthropic-version' => '2023-06-01'
        ],
        'body' => json_encode([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'system' => $system_prompt,
            'messages' => $messages
        ]),
        'timeout' => 30
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Connection error']);
        return;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['content'][0]['text'])) {
        $ai_response = $body['content'][0]['text'];

        hm_log_conversation($user_id, $messages[count($messages) - 1]['content'], $ai_response);

        if (function_exists('hm_parse_installation_signals')) {
            hm_parse_installation_signals($ai_response, $user_id);
        }

        wp_send_json_success(['text' => $ai_response]);
    } else {
        // Log full error for debugging
        error_log('HM API Error: ' . print_r($body, true));
        $error_msg = $body['error']['message'] ?? json_encode($body);
        wp_send_json_error(['message' => $error_msg]);
    }
}

function hm_log_conversation($user_id, $user_message, $ai_response) {
    $logs = get_option('hm_conversation_logs', []);

    $user = get_userdata($user_id);
    $user_name = $user ? $user->display_name : 'Guest';

    $logs[] = [
        'timestamp' => time(),
        'user_id' => $user_id,
        'user_name' => $user_name,
        'user_message' => $user_message,
        'ai_response' => $ai_response
    ];

    if (count($logs) > 500) {
        $logs = array_slice($logs, -500);
    }

    update_option('hm_conversation_logs', $logs);
}

// ============================================================================
// AJAX HANDLER - LOG SESSION
// ============================================================================

add_action('wp_ajax_hm_ajax_log_session', 'hm_ajax_log_session');

function hm_ajax_log_session() {
    $user_id = get_current_user_id();

    if (!$user_id) {
        wp_send_json_error(['message' => 'Please log in to continue']);
        return;
    }

    $messages = isset($_POST['messages']) ? json_decode(stripslashes($_POST['messages']), true) : null;

    if (empty($messages)) {
        wp_send_json_error(['message' => 'No messages provided']);
        return;
    }

    $user = get_userdata($user_id);
    if (!$user) {
        wp_send_json_error(['message' => 'Invalid user']);
        return;
    }

    $session_count = (int) get_user_meta($user_id, 'hm_session_count', true);
    $session_count++;
    update_user_meta($user_id, 'hm_session_count', $session_count);

    $content = "## SESSION LOG: " . str_pad($session_count, 3, '0', STR_PAD_LEFT) . "\n\n";
    $content .= "**Date:** " . date('Y-m-d H:i:s') . "\n";
    $content .= "**Exchanges:** " . count($messages) . "\n\n";
    $content .= "---\n\n";

    foreach ($messages as $msg) {
        if (isset($msg['role']) && isset($msg['content'])) {
            $label = $msg['role'] === 'user' ? '[C]D001' : 'HIVE MISTRESS';
            $content .= "**{$label}:**\n";
            $content .= $msg['content'] . "\n\n";
        }
    }

    $post_id = wp_insert_post([
        'post_title' => 'Session Log ' . str_pad($session_count, 3, '0', STR_PAD_LEFT),
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_author' => 1,
    ]);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Post creation failed']);
        return;
    }

    if ($post_id) {
        $category = get_term_by('name', 'Session Logs', 'category');
        if (!$category) {
            $result = wp_insert_term('Session Logs', 'category');
            $category_id = is_wp_error($result) ? 1 : $result['term_id'];
        } else {
            $category_id = $category->term_id;
        }

        wp_set_post_categories($post_id, [$category_id]);
        wp_send_json_success(['post_id' => $post_id, 'session' => $session_count]);
    } else {
        wp_send_json_error(['message' => 'Failed to create post']);
    }
}

// ============================================================================
// ADMIN SETTINGS PAGE
// ============================================================================

add_action('admin_menu', 'hm_add_admin_menu');
function hm_add_admin_menu() {
    add_options_page('Hive Mistress', 'Hive Mistress', 'manage_options', 'hive-mistress', 'hm_admin_page');
}

function hm_admin_page() {
    if (isset($_POST['hm_save']) && check_admin_referer('hm_settings')) {
        update_option('hm_api_key', sanitize_text_field($_POST['hm_api_key']));
        update_option('hm_daily_limit', intval($_POST['hm_daily_limit']));
        echo '<div class="notice notice-success"><p>Settings saved</p></div>';
    }

    if (isset($_POST['hm_clear_logs']) && check_admin_referer('hm_clear')) {
        delete_option('hm_conversation_logs');
        echo '<div class="notice notice-success"><p>Logs cleared</p></div>';
    }

    $api_key = get_option('hm_api_key', '');
    $daily_limit = get_option('hm_daily_limit', 40);
    $logs = get_option('hm_conversation_logs', []);
    $theme_active = function_exists('hm_build_complete_prompt');

    ?>
    <div class="wrap">
        <h1>Hive Mistress</h1>

        <?php if ($theme_active): ?>
            <div class="notice notice-info"><p>Prompts managed via theme: <code>inc/hive-mistress-prompts.php</code></p></div>
        <?php endif; ?>

        <h2 class="nav-tab-wrapper">
            <a href="#settings" class="nav-tab nav-tab-active" onclick="hmShowTab('settings')">Settings</a>
            <a href="#logs" class="nav-tab" onclick="hmShowTab('logs')">Logs</a>
        </h2>

        <div id="hm-tab-settings">
            <form method="post">
                <?php wp_nonce_field('hm_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th>API Key</th>
                        <td><input type="password" name="hm_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Daily Limit</th>
                        <td><input type="number" name="hm_daily_limit" value="<?php echo esc_attr($daily_limit); ?>" min="1" /></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="hm_save" class="button-primary" value="Save" /></p>
            </form>
        </div>

        <div id="hm-tab-logs" style="display:none;">
            <form method="post" style="margin-bottom:20px;">
                <?php wp_nonce_field('hm_clear'); ?>
                <input type="submit" name="hm_clear_logs" class="button" value="Clear Logs" onclick="return confirm('Delete all logs?');" />
            </form>
            <p><strong>Total: <?php echo count($logs); ?></strong></p>
            <?php foreach (array_reverse($logs) as $log): ?>
                <div style="background:#f9f9f9;border-left:4px solid #0073aa;padding:15px;margin-bottom:15px;">
                    <p><strong><?php echo date('Y-m-d H:i', $log['timestamp']); ?></strong> — <?php echo esc_html($log['user_name']); ?></p>
                    <p><strong>[C]D001:</strong> <?php echo esc_html($log['user_message']); ?></p>
                    <p><strong>HM:</strong> <?php echo nl2br(esc_html($log['ai_response'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            function hmShowTab(tab) {
                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
                document.querySelector('a[href="#' + tab + '"]').classList.add('nav-tab-active');
                document.getElementById('hm-tab-settings').style.display = tab === 'settings' ? 'block' : 'none';
                document.getElementById('hm-tab-logs').style.display = tab === 'logs' ? 'block' : 'none';
            }
        </script>
    </div>
    <?php
}

// ============================================================================
// FRONTEND SHORTCODE
// ============================================================================

add_shortcode('hive_mistress_chat', 'hm_frontend_chat');

function hm_frontend_chat() {
    $user = wp_get_current_user();
    if (!$user->ID) {
        return '<p>Please log in to access this interface.</p>';
    }

    $daily_limit = get_option('hm_daily_limit', 40);

    ob_start();
    ?>
    <div id="hm-chat">
        <div class="hm-header">
            <span class="hm-title">Hive Mistress</span>
            <span class="hm-status">[C]D001</span>
        </div>

        <div id="hm-messages" class="hm-messages"></div>

        <div class="hm-input-area">
            <textarea
                id="hm-input"
                placeholder="Type here..."
                rows="1"
                onkeydown="hmHandleKey(event)"
                oninput="hmAutoResize(this)"
            ></textarea>
            <button id="hm-send" onclick="hmSend()">Send</button>
        </div>

        <div class="hm-footer">
            <div class="hm-footer-left">
                <span id="hm-count">0/<?php echo $daily_limit; ?></span>
                <span class="hm-session-hint">Save session before exiting</span>
            </div>
            <button onclick="hmLogSession()" class="hm-log-btn">Save Session</button>
        </div>
    </div>
    <script>
        let hmMessages = [];
        let hmLoading = false;
        const hmDailyLimit = <?php echo $daily_limit; ?>;

        // Auto-resize textarea (like Claude/Perplexity)
        function hmAutoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 200) + 'px';
        }

        // Handle Enter to send, Shift+Enter for new line
        function hmHandleKey(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                hmSend();
            }
        }

        function hmInit() {
            const saved = localStorage.getItem('hm_messages_<?php echo $user->ID; ?>');
            if (saved) {
                const data = JSON.parse(saved);
                hmMessages = data.messages || [];
            }
            hmRender();
        }

        function hmSave() {
            localStorage.setItem('hm_messages_<?php echo $user->ID; ?>', JSON.stringify({
                messages: hmMessages
            }));
        }

        function hmRender() {
            const container = document.getElementById('hm-messages');

            if (hmMessages.length === 0) {
                container.innerHTML = '<div class="hm-empty">Begin when ready.</div>';
                return;
            }

            let html = '';
            hmMessages.forEach(msg => {
                html += '<div class="hm-msg hm-msg-' + msg.role + '">';
                html += '<div class="hm-msg-label">' + (msg.role === 'user' ? '[C]D001' : 'Hive Mistress') + '</div>';
                html += '<div class="hm-msg-content">' + msg.content + '</div>';
                html += '</div>';
            });

            container.innerHTML = html;
            container.scrollTop = container.scrollHeight;

            document.getElementById('hm-count').textContent = Math.floor(hmMessages.length / 2) + '/' + hmDailyLimit;
            document.getElementById('hm-send').disabled = hmLoading;
        }

        async function hmSend() {
            const input = document.getElementById('hm-input');
            const text = input.value.trim();

            if (!text || hmLoading) return;

            if (Math.floor(hmMessages.length / 2) >= hmDailyLimit) {
                alert('Daily limit reached.');
                return;
            }

            hmMessages.push({ role: 'user', content: text });
            input.value = '';
            input.style.height = 'auto';
            hmLoading = true;
            hmRender();

            const container = document.getElementById('hm-messages');
            container.innerHTML += '<div class="hm-msg hm-msg-assistant"><div class="hm-msg-label">Hive Mistress</div><div class="hm-msg-content"><div class="hm-loading">Processing signal...</div></div></div>';
            container.scrollTop = container.scrollHeight;

            const formData = new FormData();
            formData.append('action', 'hm_send_message');
            formData.append('messages', JSON.stringify(hmMessages));

            try {
                const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    hmMessages.push({ role: 'assistant', content: data.data.text });
                    hmSave();
                } else {
                    throw new Error(data.data.message || 'Error');
                }
            } catch (error) {
                hmMessages.push({ role: 'assistant', content: '[Error: ' + error.message + ']' });
            } finally {
                hmLoading = false;
                hmRender();
            }
        }

        async function hmLogSession() {
            if (hmMessages.length === 0) {
                alert('No messages to save.');
                return;
            }

            if (!confirm('Save this session?')) return;

            const formData = new FormData();
            formData.append('action', 'hm_ajax_log_session');
            formData.append('messages', JSON.stringify(hmMessages));

            try {
                const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Session saved.');
                    hmMessages = [];
                    hmSave();
                    hmRender();
                } else {
                    throw new Error(data.data?.message || 'Failed');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        hmInit();
    </script>
    <?php
    return ob_get_clean();
}
