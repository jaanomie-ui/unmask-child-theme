<?php
/**
 * Admin Hard Limits Viewer
 *
 * WordPress admin page to view all users' filed hard limits.
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include data structure
require_once get_stylesheet_directory() . '/inc/hard-limits-data.php';

/**
 * Add admin menu item
 */
function unmask_admin_limits_menu() {
    add_menu_page(
        'Hard Limits Viewer',           // Page title
        'Hard Limits',                  // Menu title
        'manage_options',               // Capability
        'unmask-hard-limits-viewer',    // Menu slug
        'unmask_admin_limits_page',     // Callback
        'dashicons-shield',             // Icon
        30                              // Position
    );
}
add_action('admin_menu', 'unmask_admin_limits_menu');

/**
 * Render admin page
 */
function unmask_admin_limits_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access.');
    }

    // Get all users who have filed limits
    $users_with_limits = get_users(array(
        'meta_key' => 'unmask_hard_limits',
        'meta_compare' => 'EXISTS',
        'orderby' => 'display_name',
    ));

    // Get selected user if viewing details
    $selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $selected_user = null;
    $limits_data = null;
    $limits_categories = unmask_get_limits_categories();

    if ($selected_user_id) {
        $selected_user = get_user_by('id', $selected_user_id);
        $limits_json = get_user_meta($selected_user_id, 'unmask_hard_limits', true);
        if ($limits_json) {
            $limits_data = json_decode($limits_json, true);
        }
    }

    ?>
    <div class="wrap">
        <h1>Hard Limits Viewer</h1>

        <?php if (empty($users_with_limits)): ?>
            <div class="notice notice-info">
                <p>No users have filed hard limits yet.</p>
            </div>
        <?php else: ?>

            <div style="display: flex; gap: 20px; margin-top: 20px;">

                <!-- User List Sidebar -->
                <div style="flex: 0 0 300px; background: #fff; padding: 20px; border: 1px solid #ccc;">
                    <h2 style="margin-top: 0;">Users (<?php echo count($users_with_limits); ?>)</h2>
                    <ul style="margin: 0; padding: 0; list-style: none;">
                        <?php foreach ($users_with_limits as $user): ?>
                            <?php
                            $counts = unmask_get_user_limits_counts($user->ID);
                            $total = $counts['green'] + $counts['yellow'] + $counts['red'];
                            $is_selected = $selected_user_id === $user->ID;
                            ?>
                            <li style="margin-bottom: 10px; padding: 10px; background: <?php echo $is_selected ? '#e0f0ff' : '#f9f9f9'; ?>; border-left: 3px solid <?php echo $is_selected ? '#0073aa' : 'transparent'; ?>;">
                                <a href="?page=unmask-hard-limits-viewer&user_id=<?php echo $user->ID; ?>" style="text-decoration: none; display: block;">
                                    <strong style="color: #333;"><?php echo esc_html($user->display_name); ?></strong>
                                    <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                        <?php echo esc_html($user->user_login); ?>
                                    </div>
                                    <div style="margin-top: 8px; font-size: 11px; font-family: monospace;">
                                        <span style="color: #2ecc71;">G:<?php echo $counts['green']; ?></span>
                                        <span style="color: #f39c12; margin-left: 8px;">Y:<?php echo $counts['yellow']; ?></span>
                                        <span style="color: #e74c3c; margin-left: 8px;">R:<?php echo $counts['red']; ?></span>
                                        <span style="color: #999; margin-left: 8px;">Total: <?php echo $total; ?></span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Details Panel -->
                <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccc;">
                    <?php if ($selected_user && $limits_data): ?>

                        <h2 style="margin-top: 0;">
                            <?php echo esc_html($selected_user->display_name); ?>
                            <span style="font-size: 14px; color: #666; font-weight: normal;">
                                (<?php echo esc_html($selected_user->user_login); ?>)
                            </span>
                        </h2>

                        <div style="margin-bottom: 20px; padding: 15px; background: #f0f0f0; border-radius: 4px;">
                            <strong>Summary:</strong>
                            <span style="color: #2ecc71; margin-left: 10px;">Green: <?php echo unmask_get_user_limits_counts($selected_user_id)['green']; ?></span>
                            <span style="color: #f39c12; margin-left: 10px;">Yellow: <?php echo unmask_get_user_limits_counts($selected_user_id)['yellow']; ?></span>
                            <span style="color: #e74c3c; margin-left: 10px;">Red: <?php echo unmask_get_user_limits_counts($selected_user_id)['red']; ?></span>
                        </div>

                        <?php if (empty($limits_data)): ?>
                            <div class="notice notice-warning inline">
                                <p>User clicked save but no activities were selected.</p>
                            </div>
                        <?php else: ?>

                            <!-- Organize by category -->
                            <?php foreach ($limits_categories as $cat_slug => $cat_data): ?>
                                <?php
                                // Get activities in this category that user has set
                                $cat_activities = array();
                                foreach ($cat_data['activities'] as $activity_slug => $activity_label) {
                                    if (isset($limits_data[$activity_slug])) {
                                        $cat_activities[$activity_slug] = array(
                                            'label' => $activity_label,
                                            'level' => $limits_data[$activity_slug]
                                        );
                                    }
                                }

                                // Skip empty categories
                                if (empty($cat_activities)) continue;
                                ?>

                                <div style="margin-bottom: 25px;">
                                    <h3 style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #ddd;">
                                        <?php echo esc_html($cat_data['label']); ?>
                                        <span style="font-size: 12px; color: #999; font-weight: normal;">
                                            (<?php echo count($cat_activities); ?> activities)
                                        </span>
                                    </h3>

                                    <table style="width: 100%; border-collapse: collapse;">
                                        <?php foreach ($cat_activities as $activity_slug => $activity): ?>
                                            <?php
                                            $level = $activity['level'];
                                            $bg_color = $level === 'green' ? '#d4edda' : ($level === 'yellow' ? '#fff3cd' : '#f8d7da');
                                            $text_color = $level === 'green' ? '#155724' : ($level === 'yellow' ? '#856404' : '#721c24');
                                            ?>
                                            <tr>
                                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                                    <?php echo esc_html($activity['label']); ?>
                                                </td>
                                                <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right; width: 100px;">
                                                    <span style="display: inline-block; padding: 4px 12px; background: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                                        <?php echo esc_html($level); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            <?php endforeach; ?>

                        <?php endif; ?>

                        <!-- Raw JSON for debugging -->
                        <details style="margin-top: 30px;">
                            <summary style="cursor: pointer; padding: 10px; background: #f0f0f0; border-radius: 4px;">
                                <strong>Raw JSON Data</strong> (for debugging)
                            </summary>
                            <pre style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px; overflow-x: auto; margin-top: 10px; font-size: 12px;"><?php echo esc_html(json_encode($limits_data, JSON_PRETTY_PRINT)); ?></pre>
                        </details>

                    <?php elseif ($selected_user): ?>
                        <div class="notice notice-warning inline">
                            <p>This user has filed limits but the data is empty or corrupted.</p>
                        </div>
                    <?php else: ?>
                        <div style="padding: 40px; text-align: center; color: #999;">
                            <p style="font-size: 16px;">← Select a user from the left to view their hard limits</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        <?php endif; ?>
    </div>
    <?php
}
