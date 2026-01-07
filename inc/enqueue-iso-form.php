<?php
/**
 * ISO Submit Form Enqueue
 *
 * Conditionally loads CSS/JS and registers AJAX handler for ISO submission.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if current page is the ISO submit page or ISO board page (which has modal)
 */
function unmask_is_iso_submit_page() {
    // Check by slug
    if (is_page('post-iso') || is_page('iso-board')) {
        return true;
    }

    // Check if ISO board template
    if (is_page_template('page-templates/template-iso-board.php')) {
        return true;
    }

    // Fallback: check global post
    global $post;
    if ($post && is_page() && ($post->post_name === 'post-iso' || $post->post_name === 'iso-board')) {
        return true;
    }

    return false;
}

/**
 * Enqueue ISO submit form styles and scripts
 * Uses priority 999 to load after BuddyBoss theme CSS.
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_iso_form_assets', 999);
function unmask_enqueue_iso_form_assets() {
    if (!unmask_is_iso_submit_page()) {
        return;
    }

    $theme_dir = get_stylesheet_directory_uri();
    $theme_path = get_stylesheet_directory();

    // Build dependencies array
    $deps = array('unmask-00-design-system');

    // Add BuddyBoss dependency if available
    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    // CSS
    $css_file = $theme_path . '/assets/css/pages/iso-form.css';
    wp_enqueue_style(
        'unmask-iso-form',
        $theme_dir . '/assets/css/pages/iso-form.css',
        $deps,
        file_exists($css_file) ? filemtime($css_file) : time()
    );

    // JavaScript
    $js_file = $theme_path . '/assets/js/iso-submit.js';
    wp_enqueue_script(
        'unmask-iso-submit',
        $theme_dir . '/assets/js/iso-submit.js',
        array(),
        file_exists($js_file) ? filemtime($js_file) : time(),
        true
    );
}

/**
 * Register ISO submit form shortcode
 */
function unmask_iso_submit_form_shortcode() {
    ob_start();
    include get_stylesheet_directory() . '/template-parts/forms/iso-submit-form.php';
    return ob_get_clean();
}
add_shortcode('unmask_iso_submit_form', 'unmask_iso_submit_form_shortcode');
add_shortcode('iso_submit_form', 'unmask_iso_submit_form_shortcode'); // Legacy alias

/**
 * AJAX Handler: Submit new ISO listing
 */
function unmask_ajax_iso_submit() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'unmask_iso_submit')) {
        wp_send_json_error(['message' => 'security check failed']);
        return;
    }

    // Must be logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'must be signed in to post']);
        return;
    }

    // Sanitize inputs
    $type = sanitize_text_field($_POST['iso_type'] ?? '');
    $category = sanitize_text_field($_POST['iso_category'] ?? '');
    $title = sanitize_text_field($_POST['iso_title'] ?? '');
    $description = sanitize_textarea_field($_POST['iso_description'] ?? '');
    $location = sanitize_text_field($_POST['iso_location'] ?? 'chicago');
    $factory = sanitize_text_field($_POST['iso_factory'] ?? 'open');
    $expiration = sanitize_text_field($_POST['iso_expiration'] ?? '');

    // Validate required fields
    $errors = [];

    if (!in_array($type, ['seeking', 'offering'])) {
        $errors[] = 'invalid type';
    }

    if (!in_array($category, ['photographer', 'model', 'collaborator', 'performance', 'connection'])) {
        $errors[] = 'invalid category';
    }

    if (strlen($title) < 10) {
        $errors[] = 'title must be at least 10 characters';
    }

    if (strlen($title) > 100) {
        $errors[] = 'title must be less than 100 characters';
    }

    if (strlen($description) < 20) {
        $errors[] = 'description must be at least 20 characters';
    }

    if (strlen($description) > 1000) {
        $errors[] = 'description must be less than 1000 characters';
    }

    if (!in_array($location, ['chicago', 'remote', 'other'])) {
        $location = 'chicago';
    }

    if (!in_array($factory, ['yes', 'preferred', 'open', 'no'])) {
        $factory = 'open';
    }

    // Validate expiration (max 30 days from now)
    if (empty($expiration)) {
        $expiration = date('Y-m-d', strtotime('+30 days'));
    } else {
        $exp_timestamp = strtotime($expiration);
        $max_timestamp = strtotime('+30 days');
        $min_timestamp = strtotime('+1 day');

        if ($exp_timestamp > $max_timestamp) {
            $expiration = date('Y-m-d', $max_timestamp);
        } elseif ($exp_timestamp < $min_timestamp) {
            $expiration = date('Y-m-d', $min_timestamp);
        }
    }

    // Return errors if any
    if (!empty($errors)) {
        wp_send_json_error(['message' => implode(', ', $errors)]);
        return;
    }

    // Create ISO post
    $post_id = wp_insert_post([
        'post_type'    => 'iso',
        'post_title'   => $title,
        'post_content' => $description,
        'post_status'  => 'publish',
        'post_author'  => get_current_user_id()
    ]);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'failed to create listing']);
        return;
    }

    // Save ACF fields
    if (function_exists('update_field')) {
        update_field('iso_type', $type, $post_id);
        update_field('iso_category', $category, $post_id);
        update_field('iso_location', $location, $post_id);
        update_field('iso_factory', $factory, $post_id);
        // ACF date format is Ymd
        update_field('iso_expiration', date('Ymd', strtotime($expiration)), $post_id);
    } else {
        // Fallback to post meta if ACF not available
        update_post_meta($post_id, 'iso_type', $type);
        update_post_meta($post_id, 'iso_category', $category);
        update_post_meta($post_id, 'iso_location', $location);
        update_post_meta($post_id, 'iso_factory', $factory);
        update_post_meta($post_id, 'iso_expiration', date('Ymd', strtotime($expiration)));
    }

    // Return success with redirect URL
    wp_send_json_success([
        'message'  => 'listing posted',
        'post_id'  => $post_id,
        'redirect' => get_permalink($post_id) ?: home_url('/iso-board/')
    ]);
}
add_action('wp_ajax_unmask_iso_submit', 'unmask_ajax_iso_submit');
