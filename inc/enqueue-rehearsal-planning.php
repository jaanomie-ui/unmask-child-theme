<?php
/**
 * Conditional Enqueue: Rehearsal Planning
 *
 * Enqueues CSS/JS for rehearsal planning page and handles AJAX save
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Rehearsal Planning assets
 */
function unmask_enqueue_rehearsal_planning() {
    if (is_page_template('page-templates/template-rehearsal-planning.php')) {
        // Enqueue CSS
        wp_enqueue_style(
            'unmask-rehearsal-planning',
            get_stylesheet_directory_uri() . '/assets/css/pages/rehearsal-planning.css',
            array('buddyboss-theme-css'),
            filemtime(get_stylesheet_directory() . '/assets/css/pages/rehearsal-planning.css')
        );

        // Enqueue JS
        wp_enqueue_script(
            'unmask-rehearsal-planning-js',
            get_stylesheet_directory_uri() . '/assets/js/rehearsal-planning.js',
            array('jquery'),
            filemtime(get_stylesheet_directory() . '/assets/js/rehearsal-planning.js'),
            true
        );

        // Localize script for AJAX
        wp_localize_script('unmask-rehearsal-planning-js', 'unmask_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rehearsal_planning_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_rehearsal_planning', 20);

/**
 * AJAX handler: Save rehearsal plan
 */
function unmask_save_rehearsal_plan() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rehearsal_planning_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    $user_id = get_current_user_id();

    // Get and sanitize data
    $selected_dates = isset($_POST['selected_dates']) && is_array($_POST['selected_dates'])
        ? array_map('sanitize_text_field', $_POST['selected_dates'])
        : array();

    $gear_bringing = isset($_POST['gear_bringing']) && is_array($_POST['gear_bringing'])
        ? array_map('sanitize_text_field', $_POST['gear_bringing'])
        : array();

    $notes = isset($_POST['notes'])
        ? sanitize_textarea_field($_POST['notes'])
        : '';

    // Save to user meta
    $rehearsal_data = array(
        'selected_dates' => $selected_dates,
        'gear_bringing' => $gear_bringing,
        'notes' => $notes,
        'submitted_at' => current_time('mysql')
    );

    $saved = update_user_meta($user_id, 'hm_rehearsal_planning', $rehearsal_data);

    if ($saved !== false) {
        // Update drone state to mark rehearsal_commitment loop as installed
        if (function_exists('hm_get_state')) {
            $drone_state = hm_get_state($user_id);
            if (!in_array('rehearsal_commitment', $drone_state['integration']['loops_installed'])) {
                $drone_state['integration']['loops_installed'][] = 'rehearsal_commitment';
                update_user_meta($user_id, 'hm_drone_state', $drone_state);
            }
        }

        wp_send_json_success('Rehearsal plan saved successfully');
    } else {
        wp_send_json_error('Failed to save rehearsal plan');
    }
}
add_action('wp_ajax_save_rehearsal_plan', 'unmask_save_rehearsal_plan');
