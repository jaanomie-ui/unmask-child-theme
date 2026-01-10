<?php
/**
 * Pink Panthers Submissions Handler
 *
 * Registers CPT for submissions and handles form processing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/* ============================================================================
   CUSTOM POST TYPE: PP SUBMISSIONS
   ============================================================================ */

add_action('init', 'unmask_register_pp_submission_cpt');
function unmask_register_pp_submission_cpt() {
    $labels = array(
        'name'               => 'PP Submissions',
        'singular_name'      => 'PP Submission',
        'menu_name'          => 'Pink Panthers',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Submission',
        'edit_item'          => 'Edit Submission',
        'new_item'           => 'New Submission',
        'view_item'          => 'View Submission',
        'search_items'       => 'Search Submissions',
        'not_found'          => 'No submissions found',
        'not_found_in_trash' => 'No submissions found in trash',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 25,
        'menu_icon'           => 'dashicons-tickets-alt',
        'supports'            => array('title'),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'exclude_from_search' => true,
    );

    register_post_type('pp_submission', $args);

    // Register custom statuses
    register_post_status('approved', array(
        'label'                     => 'Approved',
        'public'                    => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Approved <span class="count">(%s)</span>', 'Approved <span class="count">(%s)</span>'),
    ));

    register_post_status('rejected', array(
        'label'                     => 'Rejected',
        'public'                    => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>'),
    ));
}

/* ============================================================================
   ADMIN COLUMNS
   ============================================================================ */

add_filter('manage_pp_submission_posts_columns', 'unmask_pp_submission_columns');
function unmask_pp_submission_columns($columns) {
    $new_columns = array(
        'cb'              => $columns['cb'],
        'title'           => 'Submission',
        'pp_type'         => 'Type',
        'pp_act_or_role'  => 'Act/Role',
        'pp_contact'      => 'Contact',
        'pp_status'       => 'Status',
        'date'            => 'Date',
    );
    return $new_columns;
}

add_action('manage_pp_submission_posts_custom_column', 'unmask_pp_submission_column_content', 10, 2);
function unmask_pp_submission_column_content($column, $post_id) {
    switch ($column) {
        case 'pp_type':
            $type = get_post_meta($post_id, '_pp_type', true);
            $type_labels = array(
                'performer' => '<span style="color: var(--primitive-red, #8a3233);">Performer</span>',
                'volunteer' => '<span style="color: var(--primitive-blue, #2C3D68);">Volunteer</span>',
            );
            echo isset($type_labels[$type]) ? $type_labels[$type] : ucfirst($type);
            break;

        case 'pp_act_or_role':
            $type = get_post_meta($post_id, '_pp_type', true);
            if ($type === 'performer') {
                echo esc_html(get_post_meta($post_id, '_pp_act_type', true));
            } else {
                echo esc_html(get_post_meta($post_id, '_pp_role_interest', true));
            }
            break;

        case 'pp_contact':
            $email = get_post_meta($post_id, '_pp_user_email', true);
            $link = get_post_meta($post_id, '_pp_link', true);
            if ($email) {
                echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
            } elseif ($link) {
                echo esc_html($link);
            } else {
                echo '<em>—</em>';
            }
            break;

        case 'pp_status':
            $status = get_post_status($post_id);
            $status_labels = array(
                'pending'  => '<span style="color: #b26200;">● Pending</span>',
                'approved' => '<span style="color: #00a32a;">● Approved</span>',
                'rejected' => '<span style="color: #d63638;">● Rejected</span>',
                'publish'  => '<span style="color: #00a32a;">● Published</span>',
            );
            echo isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);
            break;
    }
}

/* ============================================================================
   META BOX FOR SUBMISSION DETAILS
   ============================================================================ */

add_action('add_meta_boxes', 'unmask_pp_submission_meta_box');
function unmask_pp_submission_meta_box() {
    add_meta_box(
        'pp_submission_details',
        'Submission Details',
        'unmask_pp_submission_meta_box_html',
        'pp_submission',
        'normal',
        'high'
    );
}

function unmask_pp_submission_meta_box_html($post) {
    $type = get_post_meta($post->ID, '_pp_type', true);
    $name = get_post_meta($post->ID, '_pp_name', true);
    $act_type = get_post_meta($post->ID, '_pp_act_type', true);
    $role_interest = get_post_meta($post->ID, '_pp_role_interest', true);
    $description = get_post_meta($post->ID, '_pp_description', true);
    $link = get_post_meta($post->ID, '_pp_link', true);
    $user_email = get_post_meta($post->ID, '_pp_user_email', true);
    $user_id = get_post_meta($post->ID, '_pp_user_id', true);

    ?>
    <style>
        .pp-meta-grid { display: grid; grid-template-columns: 140px 1fr; gap: 12px 16px; }
        .pp-meta-label { font-weight: 600; color: #1d2327; }
        .pp-meta-value { color: #50575e; }
        .pp-meta-value--empty { color: #a7aaad; font-style: italic; }
        .pp-meta-divider { grid-column: 1 / -1; border-top: 1px solid #c3c4c7; margin: 8px 0; }
    </style>
    <div class="pp-meta-grid">
        <div class="pp-meta-label">Type</div>
        <div class="pp-meta-value"><?php echo esc_html(ucfirst($type)); ?></div>

        <div class="pp-meta-label">Name / Stage Name</div>
        <div class="pp-meta-value <?php echo empty($name) ? 'pp-meta-value--empty' : ''; ?>">
            <?php echo $name ? esc_html($name) : 'Not provided'; ?>
        </div>

        <?php if ($type === 'performer'): ?>
            <div class="pp-meta-label">Act Type</div>
            <div class="pp-meta-value"><?php echo esc_html($act_type); ?></div>

            <div class="pp-meta-label">Description</div>
            <div class="pp-meta-value <?php echo empty($description) ? 'pp-meta-value--empty' : ''; ?>">
                <?php echo $description ? nl2br(esc_html($description)) : 'Not provided'; ?>
            </div>

            <div class="pp-meta-label">Instagram / Link</div>
            <div class="pp-meta-value <?php echo empty($link) ? 'pp-meta-value--empty' : ''; ?>">
                <?php echo $link ? esc_html($link) : 'Not provided'; ?>
            </div>
        <?php else: ?>
            <div class="pp-meta-label">Role Interest</div>
            <div class="pp-meta-value"><?php echo esc_html($role_interest); ?></div>
        <?php endif; ?>

        <div class="pp-meta-divider"></div>

        <div class="pp-meta-label">Email</div>
        <div class="pp-meta-value <?php echo empty($user_email) ? 'pp-meta-value--empty' : ''; ?>">
            <?php if ($user_email): ?>
                <a href="mailto:<?php echo esc_attr($user_email); ?>"><?php echo esc_html($user_email); ?></a>
            <?php else: ?>
                Not available (guest submission)
            <?php endif; ?>
        </div>

        <?php if ($user_id): ?>
            <div class="pp-meta-label">User Profile</div>
            <div class="pp-meta-value">
                <a href="<?php echo esc_url(get_edit_user_link($user_id)); ?>">View User #<?php echo esc_html($user_id); ?></a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/* ============================================================================
   FORM HANDLER: PERFORMER SUBMISSIONS
   ============================================================================ */

add_action('admin_post_pp_performer_submit', 'unmask_handle_pp_performer');
add_action('admin_post_nopriv_pp_performer_submit', 'unmask_handle_pp_performer');

function unmask_handle_pp_performer() {
    // Verify nonce
    if (!isset($_POST['pp_performer_nonce']) || !wp_verify_nonce($_POST['pp_performer_nonce'], 'pp_performer_submit')) {
        wp_die('Security check failed. Please go back and try again.', 'Error', array('back_link' => true));
    }

    // Sanitize inputs
    $performer_name = sanitize_text_field($_POST['performer_name'] ?? '');
    $act_type = sanitize_text_field($_POST['act_type'] ?? '');
    $act_description = sanitize_textarea_field($_POST['act_description'] ?? '');
    $performer_link = sanitize_text_field($_POST['performer_link'] ?? '');

    // Validate required fields
    if (empty($act_type)) {
        wp_die('Please select an act type.', 'Missing Information', array('back_link' => true));
    }

    // Get user info if logged in
    $user_id = get_current_user_id();
    $user_email = '';
    if ($user_id) {
        $user = get_userdata($user_id);
        $user_email = $user->user_email;
        // Use display name if no performer name provided
        if (empty($performer_name)) {
            $performer_name = $user->display_name;
        }
    }

    // Create submission post
    $post_title = sprintf('Performer: %s — %s', $performer_name ?: 'Anonymous', ucfirst($act_type));

    $post_id = wp_insert_post(array(
        'post_type'   => 'pp_submission',
        'post_status' => 'pending',
        'post_title'  => $post_title,
    ));

    if (is_wp_error($post_id)) {
        wp_die('There was an error saving your submission. Please try again.', 'Error', array('back_link' => true));
    }

    // Save meta
    update_post_meta($post_id, '_pp_type', 'performer');
    update_post_meta($post_id, '_pp_name', $performer_name);
    update_post_meta($post_id, '_pp_act_type', $act_type);
    update_post_meta($post_id, '_pp_description', $act_description);
    update_post_meta($post_id, '_pp_link', $performer_link);
    update_post_meta($post_id, '_pp_user_id', $user_id);
    update_post_meta($post_id, '_pp_user_email', $user_email);

    // Optional: Send admin notification email
    unmask_pp_notify_admin($post_id, 'performer');

    // Redirect with success
    $redirect_url = add_query_arg('pp_submitted', 'performer', wp_get_referer() ?: home_url('/pink-panthers/'));
    wp_safe_redirect($redirect_url);
    exit;
}

/* ============================================================================
   FORM HANDLER: VOLUNTEER SUBMISSIONS
   ============================================================================ */

add_action('admin_post_pp_volunteer_submit', 'unmask_handle_pp_volunteer');
add_action('admin_post_nopriv_pp_volunteer_submit', 'unmask_handle_pp_volunteer');

function unmask_handle_pp_volunteer() {
    // Verify nonce
    if (!isset($_POST['pp_volunteer_nonce']) || !wp_verify_nonce($_POST['pp_volunteer_nonce'], 'pp_volunteer_submit')) {
        wp_die('Security check failed. Please go back and try again.', 'Error', array('back_link' => true));
    }

    // Sanitize inputs
    $role_interest = sanitize_text_field($_POST['role_interest'] ?? '');

    // Validate required fields
    if (empty($role_interest)) {
        wp_die('Please select a role.', 'Missing Information', array('back_link' => true));
    }

    // Get user info if logged in
    $user_id = get_current_user_id();
    $user_email = '';
    $volunteer_name = '';
    if ($user_id) {
        $user = get_userdata($user_id);
        $user_email = $user->user_email;
        $volunteer_name = $user->display_name;
    }

    // Create submission post
    $post_title = sprintf('Volunteer: %s — %s', $volunteer_name ?: 'Anonymous', ucfirst($role_interest));

    $post_id = wp_insert_post(array(
        'post_type'   => 'pp_submission',
        'post_status' => 'pending',
        'post_title'  => $post_title,
    ));

    if (is_wp_error($post_id)) {
        wp_die('There was an error saving your submission. Please try again.', 'Error', array('back_link' => true));
    }

    // Save meta
    update_post_meta($post_id, '_pp_type', 'volunteer');
    update_post_meta($post_id, '_pp_role_interest', $role_interest);
    update_post_meta($post_id, '_pp_user_id', $user_id);
    update_post_meta($post_id, '_pp_user_email', $user_email);

    // Optional: Send admin notification email
    unmask_pp_notify_admin($post_id, 'volunteer');

    // Redirect with success
    $redirect_url = add_query_arg('pp_submitted', 'volunteer', wp_get_referer() ?: home_url('/pink-panthers/'));
    wp_safe_redirect($redirect_url);
    exit;
}

/* ============================================================================
   FORM HANDLER: TICKET NOTIFY SUBMISSIONS
   ============================================================================ */

add_action('admin_post_pp_notify_submit', 'unmask_handle_pp_notify');
add_action('admin_post_nopriv_pp_notify_submit', 'unmask_handle_pp_notify');

function unmask_handle_pp_notify() {
    // Verify nonce
    if (!isset($_POST['pp_notify_nonce']) || !wp_verify_nonce($_POST['pp_notify_nonce'], 'pp_notify_submit')) {
        wp_die('Security check failed. Please go back and try again.', 'Error', array('back_link' => true));
    }

    // Sanitize email
    $email = sanitize_email($_POST['notify_email'] ?? '');

    // Validate email
    if (!is_email($email)) {
        wp_die('Please enter a valid email address.', 'Invalid Email', array('back_link' => true));
    }

    // Store in notify list option
    $notify_list = get_option('unmask_pp_notify_list', array());
    if (!in_array($email, $notify_list)) {
        $notify_list[] = $email;
        update_option('unmask_pp_notify_list', $notify_list);
    }

    // Add to Mailchimp if MC4WP is available
    if (function_exists('mc4wp_get_api_v3')) {
        try {
            $api = mc4wp_get_api_v3();
            $lists = mc4wp_get_option('general', 'lists', array());
            $list_id = !empty($lists) ? $lists[0] : '';

            if ($list_id) {
                $api->add_list_member($list_id, array(
                    'email_address' => $email,
                    'status' => 'subscribed',
                    'tags' => array('pp-ticket-interest', 'event-interest')
                ));
            }
        } catch (Exception $e) {
            // Log error but don't fail - we still saved to local option
            error_log('UNMASK PP Notify Mailchimp Error: ' . $e->getMessage());
        }
    }

    // Redirect with success
    $redirect_url = add_query_arg('pp_submitted', 'notify', wp_get_referer() ?: home_url('/pink-panthers/'));
    wp_safe_redirect($redirect_url);
    exit;
}

/* ============================================================================
   ADMIN EMAIL NOTIFICATION
   ============================================================================ */

function unmask_pp_notify_admin($post_id, $type) {
    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');

    $subject = sprintf('[%s] New Pink Panthers %s Submission', $site_name, ucfirst($type));

    $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');

    if ($type === 'performer') {
        $name = get_post_meta($post_id, '_pp_name', true);
        $act_type = get_post_meta($post_id, '_pp_act_type', true);
        $description = get_post_meta($post_id, '_pp_description', true);

        $message = sprintf(
            "New performer submission received:\n\n" .
            "Name: %s\n" .
            "Act Type: %s\n" .
            "Description: %s\n\n" .
            "Review: %s",
            $name ?: 'Not provided',
            $act_type,
            $description ?: 'Not provided',
            $edit_link
        );
    } else {
        $role = get_post_meta($post_id, '_pp_role_interest', true);
        $email = get_post_meta($post_id, '_pp_user_email', true);

        $message = sprintf(
            "New volunteer submission received:\n\n" .
            "Role Interest: %s\n" .
            "Email: %s\n\n" .
            "Review: %s",
            $role,
            $email ?: 'Not provided',
            $edit_link
        );
    }

    wp_mail($admin_email, $subject, $message);
}

/* ============================================================================
   ADD CUSTOM STATUS TO DROPDOWN
   ============================================================================ */

add_action('admin_footer-post.php', 'unmask_pp_append_post_status_list');
add_action('admin_footer-edit.php', 'unmask_pp_append_post_status_list');

function unmask_pp_append_post_status_list() {
    global $post;
    if (!$post || $post->post_type !== 'pp_submission') {
        return;
    }

    $approved_selected = ($post->post_status === 'approved') ? ' selected="selected"' : '';
    $rejected_selected = ($post->post_status === 'rejected') ? ' selected="selected"' : '';

    ?>
    <script>
    jQuery(document).ready(function($) {
        $('select#post_status').append('<option value="approved"<?php echo $approved_selected; ?>>Approved</option>');
        $('select#post_status').append('<option value="rejected"<?php echo $rejected_selected; ?>>Rejected</option>');

        <?php if ($post->post_status === 'approved'): ?>
        $('#post-status-display').text('Approved');
        <?php elseif ($post->post_status === 'rejected'): ?>
        $('#post-status-display').text('Rejected');
        <?php endif; ?>
    });
    </script>
    <?php
}
