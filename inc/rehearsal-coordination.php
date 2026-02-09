<?php
/**
 * Rehearsal Coordination System
 *
 * Sequence 4: Material World Preparation
 * Allows drones to propose rehearsal plans and handlers to confirm them.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rehearsal Planning Form Shortcode
 *
 * Usage: [hive_rehearsal_plan]
 *
 * Shows different views for drones vs handlers:
 * - Drones: Full form to propose rehearsal details
 * - Handlers: Read-only form with confirmation checkbox
 */
if (!function_exists('hm_rehearsal_form_shortcode')) {
    function hm_rehearsal_form_shortcode() {
        if (!is_user_logged_in()) {
            return '<p class="hm-access-denied">Access denied. Login required.</p>';
        }

        $user_id = get_current_user_id();
        $is_handler = ($user_id === 1 || $user_id === 15); // Admin or drone 22
        $is_drone = function_exists('hm_has_drone_access') && hm_has_drone_access($user_id);

        if (!$is_handler && !$is_drone) {
            return '<p class="hm-access-denied">Access restricted to drones and handlers only.</p>';
        }

        // Determine which user's rehearsal plan to load
        // Handlers view the drone's plan (user 15 = d001/G)
        // Drones view their own plan
        $plan_owner_id = $is_handler ? 15 : $user_id;

        // Get existing rehearsal plan from user meta
        $rehearsal = get_user_meta($plan_owner_id, 'hm_rehearsal_plan', true);
        if (!is_array($rehearsal)) {
            $rehearsal = array(
                'proposed_date' => '',
                'proposed_time' => '',
                'location' => '',
                'performance_parts' => array(
                    'encasement' => array('ready' => false, 'notes' => ''),
                    'inspection' => array('ready' => false, 'notes' => ''),
                    'dressage' => array('ready' => false, 'notes' => ''),
                    'procession' => array('ready' => false, 'notes' => '')
                ),
                'gear_checklist' => '',
                'handler_confirmed' => false,
                'drone_submitted' => false
            );
        }

        ob_start();
        include(get_stylesheet_directory() . '/template-parts/forms/rehearsal-form.php');
        return ob_get_clean();
    }
    add_shortcode('hive_rehearsal_plan', 'hm_rehearsal_form_shortcode');
}

/**
 * AJAX Handler: Save Rehearsal Plan
 *
 * Handles form submissions from both drones and handlers
 */
if (!function_exists('hm_ajax_save_rehearsal')) {
    function hm_ajax_save_rehearsal() {
        check_ajax_referer('hm_rehearsal_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Not logged in');
            return;
        }

        $user_id = get_current_user_id();
        $is_handler = ($user_id === 1 || $user_id === 15);

        // Determine which user's plan to save
        $drone_id = $is_handler ? 15 : $user_id;

        // Build rehearsal plan array
        $rehearsal = array(
            'proposed_date' => sanitize_text_field($_POST['proposed_date'] ?? ''),
            'proposed_time' => sanitize_text_field($_POST['proposed_time'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'performance_parts' => array(
                'encasement' => array(
                    'ready' => isset($_POST['encasement_ready']),
                    'notes' => sanitize_textarea_field($_POST['encasement_notes'] ?? '')
                ),
                'inspection' => array(
                    'ready' => isset($_POST['inspection_ready']),
                    'notes' => sanitize_textarea_field($_POST['inspection_notes'] ?? '')
                ),
                'dressage' => array(
                    'ready' => isset($_POST['dressage_ready']),
                    'notes' => sanitize_textarea_field($_POST['dressage_notes'] ?? '')
                ),
                'procession' => array(
                    'ready' => isset($_POST['procession_ready']),
                    'notes' => sanitize_textarea_field($_POST['procession_notes'] ?? '')
                )
            ),
            'gear_checklist' => sanitize_textarea_field($_POST['gear_checklist'] ?? ''),
            'handler_confirmed' => $is_handler ? isset($_POST['handler_confirmed']) : false,
            'drone_submitted' => !$is_handler,
            'last_updated' => current_time('Y-m-d H:i:s'),
            'updated_by' => $user_id
        );

        // Save to drone's user meta
        update_user_meta($drone_id, 'hm_rehearsal_plan', $rehearsal);

        $message = $is_handler ? 'Rehearsal plan confirmed by handler' : 'Rehearsal plan submitted';

        // If handler confirmed AND drone submitted, install Sequence 4 loops and advance
        if ($rehearsal['handler_confirmed'] && $rehearsal['drone_submitted']) {
            if (function_exists('hm_install_loop')) {
                hm_install_loop('rehearsal_commitment', $drone_id);
                hm_install_loop('material_coordination', $drone_id);
                hm_install_loop('handler_alignment', $drone_id);
            }

            if (function_exists('hm_verify_integration')) {
                hm_verify_integration('COORDINATION', array(
                    'rehearsal_planned' => true,
                    'handler_confirmed' => true,
                    'date' => $rehearsal['proposed_date'],
                    'location' => $rehearsal['location']
                ), $drone_id);
            }

            // Advance to Sequence 5 if still on Sequence 4
            if (function_exists('hm_get_state') && function_exists('hm_advance_sequence')) {
                $state = hm_get_state($drone_id);
                if ($state['current_sequence'] == 4) {
                    hm_advance_sequence($drone_id);
                    $message .= ' - SEQUENCE 4 COMPLETE: Advanced to Sequence 5';
                }
            }
        }

        wp_send_json_success(array(
            'message' => $message,
            'rehearsal' => $rehearsal,
            'sequence_advanced' => $rehearsal['handler_confirmed'] && $rehearsal['drone_submitted']
        ));
    }
    add_action('wp_ajax_hm_save_rehearsal', 'hm_ajax_save_rehearsal');
}
