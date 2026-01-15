<?php
/**
 * Hard Limits Checklist — Enqueue Assets & AJAX Handler
 *
 * Conditionally loads CSS/JS for the hard limits form page
 * and handles AJAX save to user profile.
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
 * Check if current page is hard limits form
 *
 * @return bool
 */
function unmask_is_hard_limits_page() {
    if (is_page('hard-limits-form')) {
        return true;
    }
    if (is_page_template('page-templates/template-hard-limits.php')) {
        return true;
    }
    return false;
}

/**
 * Enqueue hard limits assets
 */
function unmask_enqueue_hard_limits_assets() {
    if (!unmask_is_hard_limits_page()) {
        return;
    }

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    // CSS
    $css_file = $theme_dir . '/assets/css/pages/hard-limits.css';
    $css_deps = array('unmask-00-design-system');

    if (wp_style_is('buddyboss-theme-template-css', 'registered')) {
        $css_deps[] = 'buddyboss-theme-template-css';
    }

    wp_enqueue_style(
        'unmask-hard-limits',
        $theme_uri . '/assets/css/pages/hard-limits.css',
        $css_deps,
        file_exists($css_file) ? filemtime($css_file) : time()
    );

    // JS
    $js_file = $theme_dir . '/assets/js/hard-limits.js';
    wp_enqueue_script(
        'unmask-hard-limits',
        $theme_uri . '/assets/js/hard-limits.js',
        array(),
        file_exists($js_file) ? filemtime($js_file) : time(),
        true
    );

    // Get saved limits from user meta
    $saved_data = '';
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $saved_data = get_user_meta($user_id, 'unmask_hard_limits', true);
    }

    // Localize script
    wp_localize_script('unmask-hard-limits', 'unmaskLimits', array(
        'ajaxUrl'    => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('unmask_save_limits'),
        'savedData'  => $saved_data,
        'isLoggedIn' => is_user_logged_in(),
    ));
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_hard_limits_assets', 999);

/**
 * AJAX handler: Save hard limits to user profile
 */
function unmask_ajax_save_limits() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unmask_save_limits')) {
        wp_send_json_error(array('message' => 'security check failed.'));
    }

    // Must be logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'must be signed in to save limits.'));
    }

    $user_id = get_current_user_id();
    $limits_raw = isset($_POST['limits']) ? $_POST['limits'] : '{}';

    // Validate JSON
    $decoded = json_decode(stripslashes($limits_raw), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(array('message' => 'invalid data format.'));
    }

    // Sanitize: only allow valid activity slugs and level values
    $valid_slugs = unmask_get_all_activity_slugs();
    $valid_levels = array('red', 'yellow', 'green');
    $sanitized = array();

    foreach ($decoded as $slug => $level) {
        $slug = sanitize_key($slug);
        $level = sanitize_key($level);

        if (in_array($slug, $valid_slugs, true) && in_array($level, $valid_levels, true)) {
            $sanitized[$slug] = $level;
        }
    }

    // Save to user meta
    $limits_json = wp_json_encode($sanitized);
    $saved = update_user_meta($user_id, 'unmask_hard_limits', $limits_json);

    // Count limits for response
    $counts = array('green' => 0, 'yellow' => 0, 'red' => 0);
    foreach ($sanitized as $level) {
        if (isset($counts[$level])) {
            $counts[$level]++;
        }
    }

    // Also save counts for quick dashboard access
    update_user_meta($user_id, 'unmask_limits_counts', $counts);

    wp_send_json_success(array(
        'message' => 'limits saved.',
        'counts'  => $counts,
        'total'   => array_sum($counts),
    ));
}
add_action('wp_ajax_unmask_save_limits', 'unmask_ajax_save_limits');

/**
 * Get user's limits counts for dashboard display
 *
 * @param int $user_id User ID (defaults to current user)
 * @return array Counts array with green, yellow, red keys
 */
function unmask_get_user_limits_counts($user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return array('green' => 0, 'yellow' => 0, 'red' => 0);
    }

    // Try cached counts first
    $counts = get_user_meta($user_id, 'unmask_limits_counts', true);

    if (is_array($counts) && isset($counts['green'])) {
        return $counts;
    }

    // Calculate from raw data if no cached counts
    $limits_json = get_user_meta($user_id, 'unmask_hard_limits', true);

    if (empty($limits_json)) {
        return array('green' => 0, 'yellow' => 0, 'red' => 0);
    }

    $limits = json_decode($limits_json, true);

    if (!is_array($limits)) {
        return array('green' => 0, 'yellow' => 0, 'red' => 0);
    }

    $counts = array('green' => 0, 'yellow' => 0, 'red' => 0);
    foreach ($limits as $level) {
        if (isset($counts[$level])) {
            $counts[$level]++;
        }
    }

    // Cache for next time
    update_user_meta($user_id, 'unmask_limits_counts', $counts);

    return $counts;
}

/**
 * Check if user has filed any limits
 *
 * @param int $user_id User ID (defaults to current user)
 * @return bool
 */
function unmask_user_has_limits($user_id = null) {
    $counts = unmask_get_user_limits_counts($user_id);
    return ($counts['green'] + $counts['yellow'] + $counts['red']) > 0;
}
