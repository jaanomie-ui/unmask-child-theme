<?php
/**
 * Newsletter Form Handler
 *
 * Processes all newsletter signups from the newsletter card component
 * and syncs to Mailchimp via MC4WP.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle newsletter form submission
 */
add_action('admin_post_unmask_newsletter_subscribe', 'unmask_handle_newsletter_subscribe');
add_action('admin_post_nopriv_unmask_newsletter_subscribe', 'unmask_handle_newsletter_subscribe');

function unmask_handle_newsletter_subscribe() {
    // Verify nonce
    if (!isset($_POST['newsletter_nonce']) || !wp_verify_nonce($_POST['newsletter_nonce'], 'unmask_newsletter')) {
        error_log('UNMASK Newsletter: Nonce verification failed');
        unmask_newsletter_redirect('error');
        return;
    }

    // Get and validate email
    $email = isset($_POST['newsletter_email']) ? sanitize_email($_POST['newsletter_email']) : '';

    if (!is_email($email)) {
        error_log('UNMASK Newsletter: Invalid email provided');
        unmask_newsletter_redirect('error');
        return;
    }

    // Get context and tags
    $context = isset($_POST['newsletter_context']) ? sanitize_text_field($_POST['newsletter_context']) : 'general';
    $tags_json = isset($_POST['newsletter_tags']) ? wp_unslash($_POST['newsletter_tags']) : '[]';
    $tags = json_decode($tags_json, true);

    if (!is_array($tags)) {
        $tags = array($context);
    }

    // Ensure context is in tags
    if (!in_array($context, $tags)) {
        $tags[] = $context;
    }

    // Add stranger tag if not logged in
    if (!is_user_logged_in() && !in_array('stranger', $tags)) {
        $tags[] = 'stranger';
    }

    // Sanitize tags
    $tags = array_map('sanitize_text_field', $tags);

    // Add to Mailchimp
    $result = unmask_newsletter_add_subscriber($email, $tags, $context);

    // Store in local option as backup
    unmask_newsletter_store_local($email, $tags, $context);

    // Redirect with status
    unmask_newsletter_redirect($result ? 'success' : 'error');
}

/**
 * Add subscriber to Mailchimp via MC4WP
 *
 * @param string $email Subscriber email
 * @param array  $tags  Tags to apply
 * @param string $context Signup context
 * @return bool Success status
 */
function unmask_newsletter_add_subscriber($email, $tags = array(), $context = 'general') {
    // Check if MC4WP is available
    if (!function_exists('mc4wp_get_api_v3')) {
        error_log('UNMASK Newsletter: MC4WP not available');
        return false;
    }

    try {
        $api = mc4wp_get_api_v3();
        $lists = mc4wp_get_option('general', 'lists', array());
        $list_id = !empty($lists) ? $lists[0] : '';

        if (empty($list_id)) {
            error_log('UNMASK Newsletter: No Mailchimp list configured');
            return false;
        }

        // Prepare member data
        $member_data = array(
            'email_address' => $email,
            'status' => 'subscribed',
            'tags' => $tags,
        );

        // Add merge fields if user is logged in
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $merge_fields = array();

            // Try scene name first, then first name
            $scene_name = get_user_meta($user->ID, 'scene_name', true);
            if (!empty($scene_name)) {
                $merge_fields['FNAME'] = $scene_name;
            } elseif (!empty($user->first_name)) {
                $merge_fields['FNAME'] = $user->first_name;
            }

            if (!empty($merge_fields)) {
                $member_data['merge_fields'] = $merge_fields;
            }
        }

        // Add to list
        $api->add_list_member($list_id, $member_data);

        error_log('UNMASK Newsletter: Successfully subscribed ' . $email . ' with tags: ' . implode(', ', $tags));
        return true;

    } catch (Exception $e) {
        $error_message = $e->getMessage();

        // Check if already subscribed (not a real error)
        if (strpos($error_message, 'already a list member') !== false) {
            error_log('UNMASK Newsletter: ' . $email . ' already subscribed, updating tags');

            // Try to update tags for existing member
            try {
                $subscriber_hash = md5(strtolower($email));
                $api->update_list_member_tags($list_id, $subscriber_hash, array(
                    'tags' => array_map(function($tag) {
                        return array('name' => $tag, 'status' => 'active');
                    }, $tags)
                ));
                return true;
            } catch (Exception $tag_error) {
                error_log('UNMASK Newsletter: Failed to update tags - ' . $tag_error->getMessage());
            }

            return true; // Still consider it a success
        }

        error_log('UNMASK Newsletter Error: ' . $error_message);
        return false;
    }
}

/**
 * Store subscriber locally as backup
 *
 * @param string $email Subscriber email
 * @param array  $tags  Tags applied
 * @param string $context Signup context
 */
function unmask_newsletter_store_local($email, $tags, $context) {
    $subscribers = get_option('unmask_newsletter_subscribers', array());

    // Check if already exists
    $exists = false;
    foreach ($subscribers as &$sub) {
        if ($sub['email'] === $email) {
            // Update existing entry
            $sub['tags'] = array_unique(array_merge($sub['tags'], $tags));
            $sub['contexts'][] = $context;
            $sub['contexts'] = array_unique($sub['contexts']);
            $sub['updated'] = current_time('mysql');
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $subscribers[] = array(
            'email'    => $email,
            'tags'     => $tags,
            'contexts' => array($context),
            'created'  => current_time('mysql'),
            'updated'  => current_time('mysql'),
        );
    }

    update_option('unmask_newsletter_subscribers', $subscribers);
}

/**
 * Redirect after form submission
 *
 * @param string $status 'success' or 'error'
 */
function unmask_newsletter_redirect($status) {
    $referer = wp_get_referer();

    if (!$referer) {
        $referer = home_url();
    }

    // Remove any existing newsletter_status parameter
    $referer = remove_query_arg('newsletter_status', $referer);

    // Add new status
    $redirect_url = add_query_arg('newsletter_status', $status, $referer);

    wp_safe_redirect($redirect_url);
    exit;
}

/**
 * Admin page to view local subscribers (optional)
 */
add_action('admin_menu', 'unmask_newsletter_admin_menu');

function unmask_newsletter_admin_menu() {
    add_submenu_page(
        'tools.php',
        'Newsletter Subscribers',
        'Newsletter Subscribers',
        'manage_options',
        'unmask-newsletter',
        'unmask_newsletter_admin_page'
    );
}

function unmask_newsletter_admin_page() {
    $subscribers = get_option('unmask_newsletter_subscribers', array());
    ?>
    <div class="wrap">
        <h1>Newsletter Subscribers (Local Backup)</h1>
        <p>These are locally stored newsletter signups. Primary storage is Mailchimp.</p>

        <?php if (empty($subscribers)) : ?>
            <p>No subscribers yet.</p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Tags</th>
                        <th>Contexts</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $sub) : ?>
                        <tr>
                            <td><?php echo esc_html($sub['email']); ?></td>
                            <td><?php echo esc_html(implode(', ', $sub['tags'])); ?></td>
                            <td><?php echo esc_html(implode(', ', $sub['contexts'])); ?></td>
                            <td><?php echo esc_html($sub['created']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total:</strong> <?php echo count($subscribers); ?> subscribers</p>
        <?php endif; ?>
    </div>
    <?php
}
