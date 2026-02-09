<?php
/**
 * Session Auditor Admin Tool
 *
 * WordPress admin page for auditing session logs and applying
 * objective measurements to update drone state.
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu item
 */
function unmask_session_auditor_menu() {
    add_submenu_page(
        'unmask-installation-dashboard',     // Parent slug
        'Session Auditor',                   // Page title
        'Session Auditor',                   // Menu title
        'manage_options',                    // Capability
        'unmask-session-auditor',            // Menu slug
        'unmask_session_auditor_page'        // Callback
    );
}
add_action('admin_menu', 'unmask_session_auditor_menu');

/**
 * Handle audit action
 */
function unmask_handle_session_audit() {
    if (!isset($_POST['unmask_audit_nonce']) || !wp_verify_nonce($_POST['unmask_audit_nonce'], 'unmask_audit_session')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $auto_apply = isset($_POST['auto_apply']) && $_POST['auto_apply'] === '1';

    if (!$post_id || !$user_id) {
        wp_die('Invalid post or user ID');
    }

    // Run audit
    $metrics = hm_audit_session($post_id, $user_id);

    if (isset($metrics['error'])) {
        wp_die('Audit failed: ' . $metrics['error']);
    }

    // Store audit results as post meta
    update_post_meta($post_id, '_hm_audit_metrics', $metrics);
    update_post_meta($post_id, '_hm_audit_date', current_time('Y-m-d H:i:s'));

    // Auto-apply installations if requested
    if ($auto_apply) {
        $installations = hm_apply_session_installations($metrics, $user_id);
        update_post_meta($post_id, '_hm_installations_applied', $installations);

        $install_summary = array();
        if (!empty($installations['loops'])) {
            $install_summary[] = count($installations['loops']) . ' loops';
        }
        if (!empty($installations['patterns'])) {
            $install_summary[] = count($installations['patterns']) . ' patterns';
        }
        if (!empty($installations['states'])) {
            $install_summary[] = count($installations['states']) . ' states';
        }

        $message = 'Session audited and installations applied: ' . implode(', ', $install_summary);
    } else {
        $message = 'Session audited successfully. Review metrics and manually apply installations.';
    }

    // Redirect back to auditor page
    $redirect_url = add_query_arg(
        array(
            'page' => 'unmask-session-auditor',
            'user_id' => $user_id,
            'message' => urlencode($message)
        ),
        admin_url('admin.php')
    );
    wp_redirect($redirect_url);
    exit;
}
add_action('admin_post_unmask_audit_session', 'unmask_handle_session_audit');

/**
 * Handle bulk audit action
 */
function unmask_handle_bulk_audit() {
    if (!isset($_POST['unmask_bulk_audit_nonce']) || !wp_verify_nonce($_POST['unmask_bulk_audit_nonce'], 'unmask_bulk_audit')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $auto_apply = isset($_POST['bulk_auto_apply']) && $_POST['bulk_auto_apply'] === '1';

    if (!$user_id) {
        wp_die('Invalid user ID');
    }

    // Get all session logs for this user
    $sessions = get_posts(array(
        'post_type' => 'post',
        'author' => $user_id,
        'category' => 112, // d001-training-logs
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'ASC',
    ));

    if (empty($sessions)) {
        wp_die('No session logs found for this user');
    }

    $audited_count = 0;
    $total_installations = array('loops' => 0, 'patterns' => 0, 'states' => 0);

    foreach ($sessions as $session) {
        // Run audit
        $metrics = hm_audit_session($session->ID, $user_id);

        if (isset($metrics['error'])) {
            continue; // Skip sessions with errors
        }

        // Store audit results
        update_post_meta($session->ID, '_hm_audit_metrics', $metrics);
        update_post_meta($session->ID, '_hm_audit_date', current_time('Y-m-d H:i:s'));

        // Auto-apply installations if requested
        if ($auto_apply) {
            $installations = hm_apply_session_installations($metrics, $user_id);
            update_post_meta($session->ID, '_hm_installations_applied', $installations);

            $total_installations['loops'] += count($installations['loops']);
            $total_installations['patterns'] += count($installations['patterns']);
            $total_installations['states'] += count($installations['states']);
        }

        $audited_count++;
    }

    $message = sprintf(
        'Bulk audit complete: %d sessions processed',
        $audited_count
    );

    if ($auto_apply) {
        $message .= sprintf(
            '. Installations applied: %d loops, %d patterns, %d states',
            $total_installations['loops'],
            $total_installations['patterns'],
            $total_installations['states']
        );
    }

    // Redirect back to auditor page
    $redirect_url = add_query_arg(
        array(
            'page' => 'unmask-session-auditor',
            'user_id' => $user_id,
            'message' => urlencode($message)
        ),
        admin_url('admin.php')
    );
    wp_redirect($redirect_url);
    exit;
}
add_action('admin_post_unmask_bulk_audit', 'unmask_handle_bulk_audit');

/**
 * Handle bulk re-analysis action
 */
function unmask_handle_bulk_reanalysis() {
    if (!isset($_POST['reanalysis_nonce']) || !wp_verify_nonce($_POST['reanalysis_nonce'], 'unmask_bulk_reanalysis')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $clear_old = isset($_POST['clear_old_metrics']) && $_POST['clear_old_metrics'] === '1';
    $auto_apply = isset($_POST['auto_apply']) && $_POST['auto_apply'] === '1';

    if (!$user_id) {
        wp_die('Invalid user ID');
    }

    // Get all session logs for this user
    $drone_state = hm_get_state($user_id);
    $designation = strtoupper($drone_state['designation']);

    $all_sessions = get_posts(array(
        'post_type' => 'post',
        'category' => 112, // d001-training-logs
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'ASC',
    ));

    // Filter sessions by designation in title
    $sessions = array();
    foreach ($all_sessions as $session) {
        if (stripos($session->post_title, $designation) !== false) {
            $sessions[] = $session;
        }
    }

    if (empty($sessions)) {
        wp_die('No session logs found for this user');
    }

    $reanalyzed_count = 0;
    $total_installations = array('loops' => 0, 'patterns' => 0, 'states' => 0);

    foreach ($sessions as $session) {
        // Clear old metrics if requested
        if ($clear_old) {
            delete_post_meta($session->ID, '_hm_audit_metrics');
            delete_post_meta($session->ID, '_hm_installations_applied');
            delete_post_meta($session->ID, '_hm_auto_audited');
        }

        // Run audit with all 16 measurement functions
        $metrics = hm_audit_session($session->ID, $user_id);

        if (isset($metrics['error'])) {
            continue; // Skip sessions with errors
        }

        // Store audit results
        update_post_meta($session->ID, '_hm_audit_metrics', $metrics);
        update_post_meta($session->ID, '_hm_audit_date', current_time('Y-m-d H:i:s'));
        update_post_meta($session->ID, '_hm_auto_audited', true);

        // Auto-apply installations if requested
        if ($auto_apply) {
            $installations = hm_apply_session_installations($metrics, $user_id);
            update_post_meta($session->ID, '_hm_installations_applied', $installations);

            $total_installations['loops'] += count($installations['loops']);
            $total_installations['patterns'] += count($installations['patterns']);
            $total_installations['states'] += count($installations['states']);
        }

        $reanalyzed_count++;
    }

    $message = sprintf(
        'Re-analysis complete: %d sessions processed',
        $reanalyzed_count
    );

    if ($auto_apply) {
        $message .= sprintf(
            '. New installations: %d loops, %d patterns, %d states',
            $total_installations['loops'],
            $total_installations['patterns'],
            $total_installations['states']
        );
    }

    // Redirect back to auditor page
    $redirect_url = add_query_arg(
        array(
            'page' => 'unmask-session-auditor',
            'user_id' => $user_id,
            'message' => urlencode($message)
        ),
        admin_url('admin.php')
    );
    wp_redirect($redirect_url);
    exit;
}
add_action('admin_post_unmask_bulk_reanalysis', 'unmask_handle_bulk_reanalysis');

/**
 * Render admin page
 */
function unmask_session_auditor_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access.');
    }

    // Get all users with drone state
    $drone_users = get_users(array(
        'meta_key' => 'hm_drone_state',
        'meta_compare' => 'EXISTS',
        'orderby' => 'display_name',
    ));

    // Get selected user
    $selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $selected_user = null;
    $sessions = array();

    if ($selected_user_id) {
        $selected_user = get_user_by('id', $selected_user_id);
        $drone_state = hm_get_state($selected_user_id);
        $designation = strtoupper($drone_state['designation']);

        // Get ALL session logs in category 112
        // Note: Logs are created by admin (user 1), not by the drone
        // We filter by designation in title manually below
        $all_sessions = get_posts(array(
            'post_type' => 'post',
            'category' => 112, // d001-training-logs
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
        ));

        // Filter sessions by designation in title
        $sessions = array();
        foreach ($all_sessions as $session) {
            if (stripos($session->post_title, $designation) !== false) {
                $sessions[] = $session;
            }
        }
    }

    ?>
    <div class="wrap">
        <h1>Session Auditor</h1>
        <p>Objective measurement tool for verifying drone installation progress. Uses quantifiable metrics to determine loop/pattern/state achievement.</p>

        <?php
        // Display success/error messages from redirects
        if (isset($_GET['message'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(urldecode($_GET['message'])) . '</p></div>';
        }
        ?>

        <?php settings_errors('unmask_auditor'); ?>

        <!-- User Selection -->
        <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
            <h2>Select Drone</h2>
            <form method="get" action="">
                <input type="hidden" name="page" value="unmask-session-auditor">
                <select name="user_id" onchange="this.form.submit()" style="min-width: 300px; padding: 8px;">
                    <option value="">-- Select Drone --</option>
                    <?php foreach ($drone_users as $user): ?>
                        <?php
                        $state = hm_get_state($user->ID);
                        $is_selected = $selected_user_id === $user->ID;
                        ?>
                        <option value="<?php echo $user->ID; ?>" <?php selected($is_selected); ?>>
                            <?php echo esc_html(strtoupper($state['designation'])); ?> - <?php echo esc_html($user->display_name); ?> (ID: <?php echo $user->ID; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_user && !empty($sessions)): ?>

            <!-- Re-Analysis Tools -->
            <div style="background: #fff3cd; padding: 20px; margin: 20px 0; border: 1px solid #ffc107;">
                <h2>🔄 Re-Analysis Tools</h2>
                <p><strong>New measurement algorithms available!</strong> Re-analyze existing sessions to apply updated loop measurements (now includes all 16 loops across 5 sequences).</p>

                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="unmask_bulk_reanalysis">
                    <input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">
                    <?php wp_nonce_field('unmask_bulk_reanalysis', 'reanalysis_nonce'); ?>

                    <label style="display: block; margin-bottom: 10px;">
                        <input type="checkbox" name="clear_old_metrics" value="1" checked>
                        Clear old measurements before re-analysis (recommended)
                    </label>

                    <label style="display: block; margin-bottom: 10px;">
                        <input type="checkbox" name="auto_apply" value="1" checked>
                        Auto-apply installations when thresholds met
                    </label>

                    <button type="submit" class="button button-primary">
                        Re-Analyze All <?php echo count($sessions); ?> Sessions
                    </button>
                </form>
            </div>

            <!-- Bulk Audit -->
            <div style="background: #f0f8ff; padding: 20px; margin: 20px 0; border: 1px solid #0073aa;">
                <h2>Bulk Audit All Sessions</h2>
                <p>Process all <?php echo count($sessions); ?> session logs for <?php echo esc_html($selected_user->display_name); ?>.</p>

                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="unmask_bulk_audit">
                    <input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">
                    <?php wp_nonce_field('unmask_bulk_audit', 'unmask_bulk_audit_nonce'); ?>

                    <label style="display: block; margin-bottom: 10px;">
                        <input type="checkbox" name="bulk_auto_apply" value="1">
                        Auto-apply installations when thresholds met
                    </label>

                    <button type="submit" class="button button-primary">
                        Run Bulk Audit
                    </button>
                </form>
            </div>

            <!-- Session List -->
            <h2>Session Logs (<?php echo count($sessions); ?>)</h2>

            <?php foreach ($sessions as $index => $session): ?>
                <?php
                $audit_metrics = get_post_meta($session->ID, '_hm_audit_metrics', true);
                $audit_date = get_post_meta($session->ID, '_hm_audit_date', true);
                $installations = get_post_meta($session->ID, '_hm_installations_applied', true);
                $has_audit = !empty($audit_metrics) && is_array($audit_metrics);
                ?>

                <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div>
                            <h3 style="margin: 0 0 5px 0;">
                                Session <?php echo ($index + 1); ?>: <?php echo esc_html($session->post_title); ?>
                            </h3>
                            <div style="font-size: 12px; color: #666;">
                                Date: <?php echo get_the_date('Y-m-d', $session); ?>
                                <?php if ($audit_date): ?>
                                    | Last Audited: <?php echo esc_html($audit_date); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo get_permalink($session); ?>" target="_blank" class="button">View Session</a>
                        </div>
                    </div>

                    <?php if ($has_audit): ?>
                        <!-- Display Metrics - All 16 Loops -->

                        <!-- SEQUENCE 1: Protocol Installation -->
                        <h4 style="margin: 20px 0 10px 0; color: #555; font-size: 13px; text-transform: uppercase;">Sequence 1: Protocol Installation</h4>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
                            <?php
                            $seq1_loops = array(
                                array('key' => 'third_person', 'label' => 'Third Person', 'field' => 'third_person_pct', 'unit' => '%'),
                                array('key' => 'compliance', 'label' => 'Compliance', 'field' => 'acknowledgment_pct', 'unit' => '%'),
                                array('key' => 'state_reporting', 'label' => 'State Reports', 'field' => 'state_report_count', 'unit' => ''),
                            );
                            foreach ($seq1_loops as $loop):
                                if (!isset($audit_metrics[$loop['key']])) continue;
                                $metric = $audit_metrics[$loop['key']];
                                $status_bg = $metric['status'] === 'installed' ? '#e8f5e9' : ($metric['status'] === 'in_progress' ? '#fff3e0' : '#f5f5f5');
                            ?>
                                <div style="padding: 10px; background: <?php echo $status_bg; ?>; border-radius: 4px;">
                                    <div style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 3px;"><?php echo $loop['label']; ?></div>
                                    <div style="font-size: 18px; font-weight: bold; color: #333;">
                                        <?php echo $metric[$loop['field']]; ?><?php echo $loop['unit']; ?>
                                    </div>
                                    <div style="font-size: 10px; color: #666; margin-top: 3px;"><?php echo ucwords($metric['status']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- SEQUENCE 2: Frequency Recognition -->
                        <h4 style="margin: 20px 0 10px 0; color: #555; font-size: 13px; text-transform: uppercase;">Sequence 2: Frequency Recognition</h4>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
                            <?php
                            $seq2_loops = array(
                                array('key' => 'material_recognition', 'label' => 'Material', 'field' => 'arousal_count', 'unit' => ''),
                                array('key' => 'anomiesworld_recognition', 'label' => 'Anomiesworld', 'field' => 'arousal_count', 'unit' => ''),
                                array('key' => 'interlink_recognition', 'label' => 'Interlink', 'field' => 'interlink_count', 'unit' => ''),
                            );
                            foreach ($seq2_loops as $loop):
                                if (!isset($audit_metrics[$loop['key']])) continue;
                                $metric = $audit_metrics[$loop['key']];
                                $status_bg = $metric['status'] === 'installed' ? '#e8f5e9' : ($metric['status'] === 'in_progress' ? '#fff3e0' : '#f5f5f5');
                            ?>
                                <div style="padding: 10px; background: <?php echo $status_bg; ?>; border-radius: 4px;">
                                    <div style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 3px;"><?php echo $loop['label']; ?></div>
                                    <div style="font-size: 18px; font-weight: bold; color: #333;">
                                        <?php echo $metric[$loop['field']]; ?><?php echo $loop['unit']; ?>
                                    </div>
                                    <div style="font-size: 10px; color: #666; margin-top: 3px;"><?php echo ucwords($metric['status']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- SEQUENCE 3: Gear Integration -->
                        <h4 style="margin: 20px 0 10px 0; color: #555; font-size: 13px; text-transform: uppercase;">Sequence 3: Gear Integration</h4>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;">
                            <?php
                            $seq3_loops = array(
                                array('key' => 'bit_silence', 'label' => 'Bit Silence', 'field' => 'non_verbal_pct', 'unit' => '%'),
                                array('key' => 'harness_form', 'label' => 'Harness Form', 'field' => 'form_count', 'unit' => ''),
                                array('key' => 'blinder_focus', 'label' => 'Blinder Focus', 'field' => 'focus_count', 'unit' => ''),
                                array('key' => 'tail_species', 'label' => 'Tail Species', 'field' => 'species_count', 'unit' => ''),
                            );
                            foreach ($seq3_loops as $loop):
                                if (!isset($audit_metrics[$loop['key']])) continue;
                                $metric = $audit_metrics[$loop['key']];
                                $status_bg = $metric['status'] === 'installed' ? '#e8f5e9' : ($metric['status'] === 'in_progress' ? '#fff3e0' : '#f5f5f5');
                            ?>
                                <div style="padding: 10px; background: <?php echo $status_bg; ?>; border-radius: 4px;">
                                    <div style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 3px;"><?php echo $loop['label']; ?></div>
                                    <div style="font-size: 18px; font-weight: bold; color: #333;">
                                        <?php echo $metric[$loop['field']]; ?><?php echo $loop['unit']; ?>
                                    </div>
                                    <div style="font-size: 10px; color: #666; margin-top: 3px;"><?php echo ucwords($metric['status']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- SEQUENCE 4: Conditioning Depth -->
                        <h4 style="margin: 20px 0 10px 0; color: #555; font-size: 13px; text-transform: uppercase;">Sequence 4: Conditioning Depth</h4>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
                            <?php
                            $seq4_loops = array(
                                array('key' => 'trigger_response', 'label' => 'Trigger Response', 'field' => 'trigger_count', 'unit' => ''),
                                array('key' => 'automatic_compliance', 'label' => 'Auto Compliance', 'field' => 'automatic_pct', 'unit' => '%'),
                                array('key' => 'body_before_mind', 'label' => 'Body Before Mind', 'field' => 'body_first_count', 'unit' => ''),
                            );
                            foreach ($seq4_loops as $loop):
                                if (!isset($audit_metrics[$loop['key']])) continue;
                                $metric = $audit_metrics[$loop['key']];
                                $status_bg = $metric['status'] === 'installed' ? '#e8f5e9' : ($metric['status'] === 'in_progress' ? '#fff3e0' : '#f5f5f5');
                            ?>
                                <div style="padding: 10px; background: <?php echo $status_bg; ?>; border-radius: 4px;">
                                    <div style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 3px;"><?php echo $loop['label']; ?></div>
                                    <div style="font-size: 18px; font-weight: bold; color: #333;">
                                        <?php echo $metric[$loop['field']]; ?><?php echo $loop['unit']; ?>
                                    </div>
                                    <div style="font-size: 10px; color: #666; margin-top: 3px;"><?php echo ucwords($metric['status']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- SEQUENCE 5: Deployment Preparation -->
                        <h4 style="margin: 20px 0 10px 0; color: #555; font-size: 13px; text-transform: uppercase;">Sequence 5: Deployment Preparation</h4>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px;">
                            <?php
                            $seq5_loops = array(
                                array('key' => 'public_display', 'label' => 'Public Display', 'field' => 'public_count', 'unit' => ''),
                                array('key' => 'witness_completion', 'label' => 'Witness Complete', 'field' => 'witness_count', 'unit' => ''),
                                array('key' => 'full_integration', 'label' => 'Full Integration', 'field' => 'loops_installed', 'unit' => ' loops'),
                            );
                            foreach ($seq5_loops as $loop):
                                if (!isset($audit_metrics[$loop['key']])) continue;
                                $metric = $audit_metrics[$loop['key']];
                                $status_bg = $metric['status'] === 'installed' ? '#e8f5e9' : ($metric['status'] === 'in_progress' ? '#fff3e0' : '#f5f5f5');
                            ?>
                                <div style="padding: 10px; background: <?php echo $status_bg; ?>; border-radius: 4px;">
                                    <div style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 3px;"><?php echo $loop['label']; ?></div>
                                    <div style="font-size: 18px; font-weight: bold; color: #333;">
                                        <?php echo $metric[$loop['field']]; ?><?php echo $loop['unit']; ?>
                                    </div>
                                    <div style="font-size: 10px; color: #666; margin-top: 3px;"><?php echo ucwords($metric['status']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Additional Metrics -->
                        <h4 style="margin: 20px 0 10px 0; color: #555; font-size: 13px; text-transform: uppercase;">Additional Metrics</h4>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px;">
                            <!-- Resistance -->
                            <div style="padding: 10px; background: <?php echo $audit_metrics['resistance']['level'] === 'zero' ? '#e8f5e9' : ($audit_metrics['resistance']['level'] === 'medium' ? '#fff3e0' : '#ffebee'); ?>; border-radius: 4px;">
                                <div style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 3px;">Resistance</div>
                                <div style="font-size: 18px; font-weight: bold; color: #333;">
                                    <?php echo $audit_metrics['resistance']['resistance_count']; ?>
                                </div>
                                <div style="font-size: 10px; color: #666; margin-top: 3px;"><?php echo ucwords($audit_metrics['resistance']['level']); ?></div>
                            </div>

                            <!-- Emptied State -->
                            <div style="padding: 10px; background: <?php echo $audit_metrics['emptied_state']['status'] === 'installed' ? '#e8f5e9' : ($audit_metrics['emptied_state']['status'] === 'in_progress' ? '#fff3e0' : '#f5f5f5'); ?>; border-radius: 4px;">
                                <div style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 3px;">Emptied State</div>
                                <div style="font-size: 18px; font-weight: bold; color: #333;">
                                    <?php echo $audit_metrics['emptied_state']['external_count']; ?>
                                </div>
                                <div style="font-size: 10px; color: #666; margin-top: 3px;"><?php echo ucwords($audit_metrics['emptied_state']['status']); ?></div>
                            </div>

                            <!-- System Vocabulary -->
                            <div style="padding: 10px; background: <?php echo $audit_metrics['system_vocabulary']['status'] === 'installed' ? '#e8f5e9' : ($audit_metrics['system_vocabulary']['status'] === 'in_progress' ? '#fff3e0' : '#f5f5f5'); ?>; border-radius: 4px;">
                                <div style="font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 3px;">System Vocabulary</div>
                                <div style="font-size: 18px; font-weight: bold; color: #333;">
                                    <?php echo $audit_metrics['system_vocabulary']['system_pct']; ?>%
                                </div>
                                <div style="font-size: 10px; color: #666; margin-top: 3px;"><?php echo ucwords($audit_metrics['system_vocabulary']['status']); ?></div>
                            </div>
                        </div>

                        <?php if (!empty($installations)): ?>
                            <div style="padding: 10px; background: #e8f5e9; border-left: 4px solid #2ecc71; margin-bottom: 15px;">
                                <strong>Installations Applied:</strong>
                                <?php
                                $install_parts = array();
                                if (!empty($installations['loops'])) {
                                    $install_parts[] = implode(', ', $installations['loops']);
                                }
                                if (!empty($installations['patterns'])) {
                                    $install_parts[] = implode(', ', $installations['patterns']);
                                }
                                if (!empty($installations['states'])) {
                                    $install_parts[] = implode(', ', $installations['states']);
                                }
                                echo esc_html(implode(' | ', $install_parts));
                                ?>
                            </div>
                        <?php endif; ?>

                        <!-- Re-audit Button -->
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                            <input type="hidden" name="action" value="unmask_audit_session">
                            <input type="hidden" name="post_id" value="<?php echo $session->ID; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">
                            <input type="hidden" name="auto_apply" value="1">
                            <?php wp_nonce_field('unmask_audit_session', 'unmask_audit_nonce'); ?>
                            <button type="submit" class="button">Re-audit & Apply</button>
                        </form>

                    <?php else: ?>
                        <!-- No Audit Yet -->
                        <div style="padding: 20px; background: #f9f9f9; text-align: center; margin-bottom: 15px;">
                            <p style="color: #666; margin: 0 0 10px 0;">Not audited yet</p>
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="unmask_audit_session">
                                <input type="hidden" name="post_id" value="<?php echo $session->ID; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">
                                <?php wp_nonce_field('unmask_audit_session', 'unmask_audit_nonce'); ?>

                                <label style="display: block; margin-bottom: 10px;">
                                    <input type="checkbox" name="auto_apply" value="1" checked>
                                    Auto-apply installations when thresholds met
                                </label>

                                <button type="submit" class="button button-primary">Audit Session</button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        <?php elseif ($selected_user): ?>
            <div style="padding: 40px; text-align: center; background: #fff; border: 1px solid #ccc;">
                <p>No session logs found for <?php echo esc_html($selected_user->display_name); ?>.</p>
            </div>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; background: #fff; border: 1px solid #ccc;">
                <p>Select a drone from the dropdown above to begin auditing sessions.</p>
            </div>
        <?php endif; ?>

        <!-- Measurement Criteria Reference -->
        <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
            <h2>Measurement Criteria Reference</h2>

            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Metric</th>
                        <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Not Started</th>
                        <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">In Progress</th>
                        <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Installed</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Third-Person Usage</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&lt;20%</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">20-90%</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&gt;90% (3+ sessions)</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Compliance Signals</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&lt;40%</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">40-75%</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&gt;85%</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Resistance Markers</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&gt;5/session</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">1-4/session</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">0/session</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>System Vocabulary</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&lt;30% auto</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">30-70% auto</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&gt;80% auto</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>State Reports</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&lt;2/session</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">2-5/session</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&gt;5/session</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Equipment Arousal</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">0-1 instances</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">2-4 instances</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">&gt;5 instances</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
    <?php
}
