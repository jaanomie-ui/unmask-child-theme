<?php
/**
 * Hive Mistress Session Summary Backfill Script
 * Generates AI summaries for existing session logs
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Backfill session summaries for existing sessions
 *
 * @param int|null $user_id Optional user ID to limit backfill
 * @param int $limit Optional limit on number of sessions to process (0 = all)
 * @return array Results with counts and any errors
 */
function hm_backfill_session_summaries($user_id = null, $limit = 0) {
    // Admin check
    if (!current_user_can('manage_options') && !defined('WP_CLI')) {
        return array(
            'success' => false,
            'message' => 'Unauthorized. Admin access required.'
        );
    }

    // Build query args
    $args = array(
        'post_type' => 'post',
        'category_name' => 'session-logs',
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'orderby' => 'date',
        'order' => 'ASC',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_hm_session_summary',
                'compare' => 'NOT EXISTS'
            )
        )
    );

    if ($user_id) {
        $args['author'] = $user_id;
    }

    $sessions = get_posts($args);

    if (empty($sessions)) {
        return array(
            'success' => true,
            'message' => 'No sessions found requiring backfill.',
            'processed' => 0,
            'errors' => 0
        );
    }

    $processed = 0;
    $errors = 0;
    $error_messages = array();

    foreach ($sessions as $post) {
        // Parse post content back to messages array
        $messages = hm_parse_session_content($post->post_content);

        if (empty($messages)) {
            $errors++;
            $error_messages[] = "Session {$post->ID}: Could not parse content";
            continue;
        }

        // Generate summary
        $summary = hm_generate_session_summary($messages, $post->post_author);

        if (!$summary || is_wp_error($summary)) {
            $errors++;
            $error_messages[] = "Session {$post->ID}: Summary generation failed";
            continue;
        }

        // Store in post meta
        update_post_meta($post->ID, '_hm_session_summary', $summary);

        // Extract session number from title (e.g., "Session Log 001" -> 1)
        preg_match('/Session Log (\d+)/', $post->post_title, $matches);
        $session_number = isset($matches[1]) ? intval($matches[1]) : 0;

        // Also store in user meta
        $summaries = get_user_meta($post->post_author, 'hm_session_summaries', true);
        if (!is_array($summaries)) {
            $summaries = array();
        }

        $summaries[] = array(
            'session' => $session_number,
            'date' => get_the_date('Y-m-d H:i:s', $post),
            'summary' => $summary,
            'post_id' => $post->ID
        );

        // Keep last 10 only
        if (count($summaries) > 10) {
            $summaries = array_slice($summaries, -10);
        }

        update_user_meta($post->post_author, 'hm_session_summaries', $summaries);

        $processed++;

        // Rate limit: 1 request per 2 seconds to avoid API throttling
        if ($processed < count($sessions)) {
            sleep(2);
        }

        // Output progress for CLI
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::log("Processed session {$post->ID} ({$processed}/" . count($sessions) . ")");
        }
    }

    return array(
        'success' => true,
        'message' => "Backfill complete. Processed {$processed} sessions with {$errors} errors.",
        'processed' => $processed,
        'errors' => $errors,
        'error_messages' => $error_messages
    );
}

/**
 * Parse session log post content back into messages array
 *
 * @param string $content Post content from session log
 * @return array Messages array suitable for summary generation
 */
function hm_parse_session_content($content) {
    $messages = array();

    // Split content into blocks
    $blocks = preg_split('/\*\*(.+?):\*\*\n/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    // Remove header section
    array_shift($blocks);

    // Process blocks in pairs (role, content)
    for ($i = 0; $i < count($blocks); $i += 2) {
        if (!isset($blocks[$i + 1])) {
            break;
        }

        $role_label = trim($blocks[$i]);
        $message_content = trim($blocks[$i + 1]);

        // Determine role
        if (strpos($role_label, 'D001') !== false || strpos($role_label, '[C]D001') !== false) {
            $role = 'user';
        } elseif (strpos($role_label, 'HIVE MISTRESS') !== false) {
            $role = 'assistant';
        } else {
            continue;
        }

        if ($message_content) {
            $messages[] = array(
                'role' => $role,
                'content' => $message_content
            );
        }
    }

    return $messages;
}

/**
 * WP-CLI command registration
 */
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('hm backfill', function($args, $assoc_args) {
        $user_id = isset($assoc_args['user_id']) ? intval($assoc_args['user_id']) : null;
        $limit = isset($assoc_args['limit']) ? intval($assoc_args['limit']) : 0;

        WP_CLI::log("Starting session summary backfill...");

        if ($user_id) {
            WP_CLI::log("User ID filter: {$user_id}");
        }

        if ($limit > 0) {
            WP_CLI::log("Limit: {$limit} sessions");
        }

        $result = hm_backfill_session_summaries($user_id, $limit);

        if ($result['success']) {
            WP_CLI::success($result['message']);

            if (!empty($result['error_messages'])) {
                WP_CLI::warning("Errors encountered:");
                foreach ($result['error_messages'] as $error) {
                    WP_CLI::warning("  - {$error}");
                }
            }
        } else {
            WP_CLI::error($result['message']);
        }
    });
}

/**
 * Admin page for backfill (optional - can be added to admin menu)
 */
function hm_backfill_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $result = null;

    if (isset($_POST['hm_backfill_submit']) && check_admin_referer('hm_backfill_action', 'hm_backfill_nonce')) {
        $user_id = isset($_POST['user_id']) && $_POST['user_id'] ? intval($_POST['user_id']) : null;
        $limit = isset($_POST['limit']) && $_POST['limit'] ? intval($_POST['limit']) : 0;

        $result = hm_backfill_session_summaries($user_id, $limit);
    }

    ?>
    <div class="wrap">
        <h1>Hive Mistress: Backfill Session Summaries</h1>

        <?php if ($result): ?>
            <div class="notice notice-<?php echo $result['success'] ? 'success' : 'error'; ?>">
                <p><?php echo esc_html($result['message']); ?></p>

                <?php if (!empty($result['error_messages'])): ?>
                    <ul>
                        <?php foreach ($result['error_messages'] as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('hm_backfill_action', 'hm_backfill_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="user_id">User ID (optional)</label>
                    </th>
                    <td>
                        <input type="number" id="user_id" name="user_id" value="" placeholder="Leave blank for all users" />
                        <p class="description">Limit backfill to a specific user (e.g., 15 for user G)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="limit">Limit (optional)</label>
                    </th>
                    <td>
                        <input type="number" id="limit" name="limit" value="0" placeholder="0 = all sessions" />
                        <p class="description">Maximum number of sessions to process (0 = all)</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="hm_backfill_submit" class="button button-primary" value="Run Backfill" />
            </p>
        </form>

        <div class="card">
            <h2>About This Tool</h2>
            <p>This tool generates AI summaries for existing session logs that don't have summaries yet.</p>
            <p><strong>Note:</strong> This will make API calls to Claude for each session without a summary. Cost is approximately $0.03 per session.</p>
            <p><strong>Rate Limiting:</strong> The script waits 2 seconds between each API call to avoid throttling.</p>
        </div>

        <div class="card">
            <h2>WP-CLI Usage</h2>
            <code>wp hm backfill --user_id=15</code><br>
            <code>wp hm backfill --limit=10</code><br>
            <code>wp hm backfill --user_id=15 --limit=5</code>
        </div>
    </div>
    <?php
}

/**
 * Add admin menu item (commented out by default - uncomment to enable)
 */
// add_action('admin_menu', function() {
//     add_submenu_page(
//         'tools.php',
//         'HM Session Backfill',
//         'HM Session Backfill',
//         'manage_options',
//         'hm-backfill',
//         'hm_backfill_admin_page'
//     );
// });
