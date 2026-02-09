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

    // Extract latest user message for RAG
    $latest_message = '';
    if (!empty($messages)) {
        $last_msg = end($messages);
        $latest_message = isset($last_msg['content']) ? $last_msg['content'] : '';
    }

    // Get relevant lore via RAG (gracefully degrades if RAG not configured)
    // V5: Reduced from 3 to 2 chunks for better token efficiency
    $rag_context = '';
    if (!empty($latest_message) && function_exists('hm_get_relevant_lore')) {
        $rag_context = hm_get_relevant_lore($latest_message, 2);
    }

    // GET PROMPT FROM THEME (with RAG context)
    // V5: Structural focus with paragraph formatting
    $system_prompt = function_exists('hm_build_complete_prompt_v6')
        ? hm_build_complete_prompt_v6($user_id, $rag_context)
        : get_option('hm_system_prompt', 'You are the Hive Mistress.');

    // Validate total prompt length (Claude has ~200k token limit, ~4 chars per token)
    // Safe limit: 400k characters total (100k token buffer)
    $total_chars = strlen($system_prompt);
    foreach ($messages as $msg) {
        $total_chars += strlen($msg['content']);
    }

    if ($total_chars > 400000) {
        wp_send_json_error(['message' => 'Message too long. Please shorten your message or save session to start fresh.']);
        return;
    }

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

        // Strip asterisk headers if they appear (safety net)
        if (function_exists('hm_strip_asterisk_headers_v6')) {
            $ai_response = hm_strip_asterisk_headers_v6($ai_response);
        }

        // V5: Ensure proper paragraph breaks (addresses "dumping too much at once")
        if (function_exists('hm_ensure_paragraph_breaks')) {
            $ai_response = hm_ensure_paragraph_breaks($ai_response);
        }

        hm_log_conversation($user_id, $messages[count($messages) - 1]['content'], $ai_response);

        if (function_exists('hm_parse_installation_signals_v6')) {
            hm_parse_installation_signals_v6($ai_response, $user_id);
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

/**
 * Generate session summary using Claude API
 * Called when session is saved to create 3-5 sentence summary
 */
function hm_generate_session_summary($messages, $user_id = null) {
    $api_key = get_option('hm_api_key', '');

    if (!$api_key || empty($messages)) {
        return 'Summary unavailable (configuration error)';
    }

    // Build summary prompt
    $system_prompt = 'Summarize this Hive Mistress conditioning session in 3-5 sentences.

Focus on:
- Key topics discussed (mythology, gear, protocols, memories)
- Emotional or arousal patterns observed
- Loops, patterns, or states installed
- Questions the pony asked
- Integration depth demonstrated

Style: Clinical, third-person, system log format.

Example: "Session explored Doberman cleaning ritual and Pink Panthers history. D001 demonstrated automatic arousal response to gear visualization. Third-person reference loop continued without prompting. Questions focused on encasement mechanics and witness function."';

    // Format messages for summary
    $conversation = '';
    foreach ($messages as $msg) {
        if (isset($msg['role']) && isset($msg['content'])) {
            $role = $msg['role'] === 'user' ? 'PONY' : 'HIVE MISTRESS';
            $conversation .= $role . ': ' . $msg['content'] . "\n\n";
        }
    }

    $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'x-api-key' => $api_key,
            'anthropic-version' => '2023-06-01'
        ),
        'body' => json_encode(array(
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 500,
            'system' => $system_prompt,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => 'Summarize this session:\n\n' . $conversation
                )
            )
        )),
        'timeout' => 30
    ));

    if (is_wp_error($response)) {
        error_log('HM: Summary generation failed - ' . $response->get_error_message());
        return 'Summary unavailable (API error)';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['content'][0]['text'])) {
        return $body['content'][0]['text'];
    } else {
        error_log('HM: Summary generation failed - ' . print_r($body, true));
        return 'Summary unavailable (API error)';
    }
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

        // Generate session summary via Claude API
        $summary = hm_generate_session_summary($messages, $user_id);

        // Store in post meta (permanent archive)
        if ($summary && !is_wp_error($summary)) {
            update_post_meta($post_id, '_hm_session_summary', $summary);

            // Also store in user meta (last 10 for fast access)
            $summaries = get_user_meta($user_id, 'hm_session_summaries', true);
            if (!is_array($summaries)) {
                $summaries = array();
            }

            $summaries[] = array(
                'session' => $session_count,
                'date' => date('Y-m-d H:i:s'),
                'summary' => $summary,
                'post_id' => $post_id
            );

            // Keep last 10 only
            if (count($summaries) > 10) {
                $summaries = array_slice($summaries, -10);
            }

            update_user_meta($user_id, 'hm_session_summaries', $summaries);
        }

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
    $theme_active = function_exists('hm_build_complete_prompt_v6');

    ?>
    <div class="wrap">
        <h1>Hive Mistress</h1>

        <?php if ($theme_active): ?>
            <div class="notice notice-info"><p>Prompts managed via theme: <code>inc/hive-mistress-prompts.php</code></p></div>
        <?php endif; ?>

        <h2 class="nav-tab-wrapper">
            <a href="#settings" class="nav-tab nav-tab-active" onclick="hmShowTab('settings')">Settings</a>
            <a href="#logs" class="nav-tab" onclick="hmShowTab('logs')">Logs</a>
            <a href="#rag" class="nav-tab" onclick="hmShowTab('rag')">RAG</a>
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

        <div id="hm-tab-rag" style="display:none;">
            <?php
            $rag_status = function_exists('hm_rag_status') ? hm_rag_status() : null;
            ?>

            <h3>RAG System Status</h3>
            <table class="form-table">
                <tr>
                    <th>Supabase URL</th>
                    <td>
                        <?php if ($rag_status): ?>
                            <span style="color: <?php echo $rag_status['supabase_url'] === 'Set' ? 'green' : 'red'; ?>;">
                                <?php echo esc_html($rag_status['supabase_url']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color: red;">RAG system not loaded</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Supabase Key</th>
                    <td>
                        <?php if ($rag_status): ?>
                            <span style="color: <?php echo $rag_status['supabase_key'] === 'Set' ? 'green' : 'red'; ?>;">
                                <?php echo esc_html($rag_status['supabase_key']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color: red;">Not Set</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>OpenAI API Key</th>
                    <td>
                        <?php if ($rag_status): ?>
                            <span style="color: <?php echo $rag_status['openai_key'] === 'Set' ? 'green' : 'red'; ?>;">
                                <?php echo esc_html($rag_status['openai_key']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color: red;">Not Set</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Chunks in Database</th>
                    <td>
                        <strong id="hm-chunk-count"><?php echo $rag_status ? esc_html($rag_status['chunk_count']) : '0'; ?></strong>
                    </td>
                </tr>
                <tr>
                    <th>System Status</th>
                    <td>
                        <?php if ($rag_status && $rag_status['configured']): ?>
                            <span style="color: green; font-weight: bold;">✓ Ready</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">✗ Not Configured</span>
                            <p style="color: #666; margin-top: 5px;">
                                Add credentials to <code>wp-config.php</code>:<br>
                                <code>define('HM_SUPABASE_URL', 'your-url');</code><br>
                                <code>define('HM_SUPABASE_KEY', 'your-key');</code><br>
                                <code>define('HM_OPENAI_API_KEY', 'your-key');</code>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <h3>Lore Management</h3>
            <p>
                <button type="button" class="button button-primary" onclick="hmUploadLore()" id="hm-upload-btn">
                    Upload Lore Files
                </button>
                <button type="button" class="button" onclick="hmClearLore()" id="hm-clear-btn">
                    Clear All Lore
                </button>
            </p>
            <div id="hm-upload-status" style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa; display: none;">
                <p id="hm-upload-message"></p>
                <div id="hm-upload-progress" style="margin-top: 10px;"></div>
            </div>

            <h3>Test RAG Search</h3>
            <p>Enter a query to test the RAG system:</p>
            <p>
                <input type="text" id="hm-test-query" class="regular-text" placeholder="e.g., Tell me about the Doberman" />
                <button type="button" class="button" onclick="hmTestRag()">Test Search</button>
            </p>
            <div id="hm-test-results" style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa; display: none;"></div>

            <script>
                function hmUploadLore() {
                    if (!confirm('This will scan all lore directories and upload files to Supabase. This may take 5-10 minutes. Continue?')) {
                        return;
                    }

                    const btn = document.getElementById('hm-upload-btn');
                    const status = document.getElementById('hm-upload-status');
                    const message = document.getElementById('hm-upload-message');

                    btn.disabled = true;
                    btn.textContent = 'Uploading...';
                    status.style.display = 'block';
                    message.innerHTML = '<strong>Scanning directories...</strong>';

                    jQuery.post(ajaxurl, {
                        action: 'hm_upload_lore',
                        _wpnonce: '<?php echo wp_create_nonce("hm_upload_lore"); ?>'
                    }, function(response) {
                        btn.disabled = false;
                        btn.textContent = 'Upload Lore Files';

                        if (response.success) {
                            message.innerHTML = '<strong style="color: green;">✓ Upload Complete!</strong><br>' +
                                'Files processed: ' + response.data.files_processed + '<br>' +
                                'Chunks created: ' + response.data.chunks_created + '<br>' +
                                'Chunks uploaded: ' + response.data.chunks_uploaded;

                            if (response.data.errors && response.data.errors.length > 0) {
                                message.innerHTML += '<br><br><strong>Errors:</strong><br>' + response.data.errors.join('<br>');
                            }

                            // Update chunk count
                            document.getElementById('hm-chunk-count').textContent = response.data.chunks_uploaded;
                        } else {
                            message.innerHTML = '<strong style="color: red;">✗ Upload Failed</strong><br>' + response.data.message;
                        }
                    }).fail(function() {
                        btn.disabled = false;
                        btn.textContent = 'Upload Lore Files';
                        message.innerHTML = '<strong style="color: red;">✗ Upload Failed</strong><br>Network error';
                    });
                }

                function hmClearLore() {
                    if (!confirm('This will delete ALL lore chunks from Supabase. Are you sure?')) {
                        return;
                    }

                    const btn = document.getElementById('hm-clear-btn');
                    btn.disabled = true;
                    btn.textContent = 'Clearing...';

                    jQuery.post(ajaxurl, {
                        action: 'hm_clear_lore',
                        _wpnonce: '<?php echo wp_create_nonce("hm_clear_lore"); ?>'
                    }, function(response) {
                        btn.disabled = false;
                        btn.textContent = 'Clear All Lore';

                        if (response.success) {
                            alert('All lore chunks cleared successfully.');
                            document.getElementById('hm-chunk-count').textContent = '0';
                        } else {
                            alert('Clear failed: ' + response.data.message);
                        }
                    }).fail(function() {
                        btn.disabled = false;
                        btn.textContent = 'Clear All Lore';
                        alert('Clear failed: Network error');
                    });
                }

                function hmTestRag() {
                    const query = document.getElementById('hm-test-query').value.trim();
                    const results = document.getElementById('hm-test-results');

                    if (!query) {
                        alert('Please enter a test query');
                        return;
                    }

                    results.style.display = 'block';
                    results.innerHTML = '<p><em>Searching...</em></p>';

                    jQuery.post(ajaxurl, {
                        action: 'hm_test_rag',
                        query: query,
                        _wpnonce: '<?php echo wp_create_nonce("hm_test_rag"); ?>'
                    }, function(response) {
                        if (response.success) {
                            if (response.data.chunks.length === 0) {
                                results.innerHTML = '<p><strong>No results found</strong></p>';
                            } else {
                                let html = '<p><strong>Found ' + response.data.chunks.length + ' results:</strong></p>';
                                response.data.chunks.forEach((chunk, i) => {
                                    html += '<div style="background: white; padding: 10px; margin: 10px 0; border-left: 3px solid #0073aa;">';
                                    html += '<p><strong>#' + (i+1) + '</strong>';
                                    if (chunk.topic) html += ' - ' + chunk.topic;
                                    if (chunk.similarity) html += ' (Similarity: ' + (chunk.similarity * 100).toFixed(1) + '%)';
                                    html += '</p>';
                                    html += '<p>' + chunk.content.substring(0, 300) + '...</p>';
                                    if (chunk.source) html += '<p style="color: #666; font-size: 0.9em;">Source: ' + chunk.source + '</p>';
                                    html += '</div>';
                                });
                                results.innerHTML = html;
                            }
                        } else {
                            results.innerHTML = '<p style="color: red;"><strong>Search failed:</strong> ' + response.data.message + '</p>';
                        }
                    }).fail(function() {
                        results.innerHTML = '<p style="color: red;"><strong>Search failed:</strong> Network error</p>';
                    });
                }
            </script>
        </div>

        <script>
            function hmShowTab(tab) {
                document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
                document.querySelector('a[href="#' + tab + '"]').classList.add('nav-tab-active');
                document.getElementById('hm-tab-settings').style.display = tab === 'settings' ? 'block' : 'none';
                document.getElementById('hm-tab-logs').style.display = tab === 'logs' ? 'block' : 'none';
                document.getElementById('hm-tab-rag').style.display = tab === 'rag' ? 'block' : 'none';
            }
        </script>
    </div>
    <?php
}

// ============================================================================
// RAG AJAX HANDLERS
// ============================================================================

add_action('wp_ajax_hm_upload_lore', 'hm_ajax_upload_lore');
add_action('wp_ajax_hm_clear_lore', 'hm_ajax_clear_lore');
add_action('wp_ajax_hm_test_rag', 'hm_ajax_test_rag');

/**
 * AJAX handler for uploading all lore files
 */
function hm_ajax_upload_lore() {
    error_log('HM RAG DEBUG: Upload lore AJAX handler started');

    check_ajax_referer('hm_upload_lore');

    if (!current_user_can('manage_options')) {
        error_log('HM RAG DEBUG: User not authorized');
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!function_exists('hm_scan_lore_directories') || !function_exists('hm_process_lore_file')) {
        error_log('HM RAG DEBUG: RAG chunker functions not loaded');
        wp_send_json_error(array('message' => 'RAG system not loaded'));
        return;
    }

    // Increase time limits for large uploads (can take 5-10 minutes)
    set_time_limit(600);  // 10 minutes max
    ini_set('max_execution_time', '600');
    error_log('HM RAG DEBUG: Set execution time limit to 600 seconds');

    // Scan for lore files
    error_log('HM RAG DEBUG: Scanning lore directories...');
    $lore_files = hm_scan_lore_directories();

    error_log('HM RAG DEBUG: Found ' . count($lore_files) . ' lore files');

    if (!empty($lore_files)) {
        error_log('HM RAG DEBUG: First 5 files: ' . print_r(array_slice($lore_files, 0, 5), true));
    }

    if (empty($lore_files)) {
        error_log('HM RAG DEBUG: No lore files found in any directory');
        wp_send_json_error(array('message' => 'No lore files found'));
        return;
    }

    $total_results = array(
        'files_processed' => 0,
        'chunks_created' => 0,
        'chunks_uploaded' => 0,
        'errors' => array(),
    );

    // Process each file
    foreach ($lore_files as $file) {
        error_log("HM RAG DEBUG: Processing file: {$file}");
        $results = hm_process_lore_file($file);

        $total_results['files_processed']++;
        $total_results['chunks_created'] += $results['chunks_created'];
        $total_results['chunks_uploaded'] += $results['chunks_uploaded'];

        error_log("HM RAG DEBUG: File results - chunks created: {$results['chunks_created']}, uploaded: {$results['chunks_uploaded']}");

        if (!empty($results['errors'])) {
            error_log("HM RAG DEBUG: Errors: " . print_r($results['errors'], true));
            $total_results['errors'] = array_merge($total_results['errors'], $results['errors']);
        }
    }

    wp_send_json_success($total_results);
}

/**
 * AJAX handler for clearing all lore chunks
 */
function hm_ajax_clear_lore() {
    check_ajax_referer('hm_clear_lore');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!function_exists('hm_clear_lore_chunks')) {
        wp_send_json_error(array('message' => 'RAG system not loaded'));
        return;
    }

    $result = hm_clear_lore_chunks();

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    } else {
        wp_send_json_success(array('message' => 'All chunks cleared'));
    }
}

/**
 * AJAX handler for testing RAG search
 */
function hm_ajax_test_rag() {
    check_ajax_referer('hm_test_rag');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

    if (empty($query)) {
        wp_send_json_error(array('message' => 'No query provided'));
        return;
    }

    if (!function_exists('hm_get_embedding') || !function_exists('hm_search_lore')) {
        wp_send_json_error(array('message' => 'RAG system not loaded'));
        return;
    }

    // Get embedding
    $embedding = hm_get_embedding($query);

    if (!$embedding) {
        wp_send_json_error(array('message' => 'Failed to get embedding'));
        return;
    }

    // Search lore (0.3 = lower threshold for better recall of exact matches)
    $chunks = hm_search_lore($embedding, 5, 0.3);

    wp_send_json_success(array('chunks' => $chunks));
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
