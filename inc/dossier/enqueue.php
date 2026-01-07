<?php
/**
 * Dossier Asset Loading and AJAX Handlers
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

/**
 * Redirect member base URL to profile tab
 * /members/username/ → /members/username/profile/
 */
function unmask_redirect_to_profile_tab() {
    if (!function_exists('bp_is_user') || !bp_is_user()) {
        return;
    }

    // Only redirect if on the activity component (default landing)
    if (bp_is_current_component('activity') && bp_is_user()) {
        $profile_url = bp_displayed_user_domain() . 'profile/';
        wp_safe_redirect($profile_url);
        exit;
    }
}
add_action('template_redirect', 'unmask_redirect_to_profile_tab');

/**
 * Dequeue BuddyBoss profile-specific CSS on member pages
 * This removes the ID-selector heavy stylesheets that conflict with our dossier
 *
 * Priority 999 ensures we run after BuddyBoss enqueues its styles
 */
function unmask_dequeue_buddyboss_profile_css() {
    // Only on BuddyPress/BuddyBoss profile pages
    if (!function_exists('bp_is_user') || !bp_is_user()) {
        return;
    }

    // Dequeue BuddyBoss profile-specific styles
    // These use ID selectors (#item-header, etc.) that override our class-based styles
    wp_dequeue_style('buddyboss-theme-buddypress-css');
}
add_action('wp_enqueue_scripts', 'unmask_dequeue_buddyboss_profile_css', 999);

/**
 * Enqueue dossier assets on profile pages
 * Priority 1000 ensures we load AFTER all BuddyBoss styles
 */
function unmask_enqueue_dossier_assets() {
    // Only on BuddyPress/BuddyBoss profile pages
    if (!function_exists('bp_is_user') || !bp_is_user()) {
        return;
    }

    $theme_dir = get_stylesheet_directory_uri();
    $theme_path = get_stylesheet_directory();

    // CSS - no dependencies, we rely on high priority to load last
    $css_file = $theme_path . '/assets/css/pages/dossier.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'unmask-dossier',
            $theme_dir . '/assets/css/pages/dossier.css',
            [],
            filemtime($css_file)
        );

        // Add inline CSS for layout fix with highest priority
        $inline_css = '
            .bb-grid.site-content-grid { display: block !important; }
            .bb-grid.site-content-grid > * { width: 100% !important; max-width: 100% !important; flex: unset !important; }
            #primary.lg-grid-2-3, #primary.md-grid-2-3 { width: 100% !important; max-width: 100% !important; flex: unset !important; }
        ';
        wp_add_inline_style('unmask-dossier', $inline_css);
    }

    // JS
    $js_file = $theme_path . '/assets/js/dossier.js';
    if (file_exists($js_file)) {
        wp_enqueue_script(
            'unmask-dossier',
            $theme_dir . '/assets/js/dossier.js',
            ['jquery'],
            filemtime($js_file),
            true
        );

        wp_localize_script('unmask-dossier', 'unmaskDossier', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('unmask_dossier_nonce'),
            'isOwn' => bp_is_my_profile(),
            'userId' => bp_displayed_user_id()
        ]);
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_dossier_assets', 1000);

/**
 * AJAX: Toggle dashboard visibility
 */
function unmask_ajax_toggle_visibility() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $visibility = isset($_POST['visibility']) ? sanitize_text_field($_POST['visibility']) : '';
    if (!in_array($visibility, ['masked', 'unmasked'])) {
        wp_send_json_error(['message' => 'Invalid visibility']);
    }

    unmask_set_dashboard_visibility(get_current_user_id(), $visibility);
    wp_send_json_success(['visibility' => $visibility]);
}
add_action('wp_ajax_unmask_toggle_visibility', 'unmask_ajax_toggle_visibility');

/**
 * AJAX: Expire ISO
 */
function unmask_ajax_expire_iso() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $iso_id = isset($_POST['iso_id']) ? intval($_POST['iso_id']) : 0;
    if (!$iso_id) {
        wp_send_json_error(['message' => 'Invalid ISO ID']);
    }

    $iso = get_post($iso_id);
    if (!$iso || $iso->post_type !== 'iso') {
        wp_send_json_error(['message' => 'ISO not found']);
    }

    if ($iso->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => 'Not your ISO']);
    }

    // Set expiration to yesterday using ACF if available
    $yesterday = date('Ymd', strtotime('-1 day'));

    if (function_exists('update_field')) {
        update_field('iso_expiration', $yesterday, $iso_id);
    } else {
        update_post_meta($iso_id, 'iso_expiration', $yesterday);
    }

    wp_send_json_success(['message' => 'ISO expired']);
}
add_action('wp_ajax_unmask_expire_iso', 'unmask_ajax_expire_iso');

/**
 * AJAX: Mark record as read
 */
function unmask_ajax_mark_record_read() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
    if (!$record_id) {
        wp_send_json_error(['message' => 'Invalid record ID']);
    }

    $post = get_post($record_id);
    if (!$post) {
        wp_send_json_error(['message' => 'Record not found']);
    }

    unmask_mark_record_read(get_current_user_id(), $record_id);

    wp_send_json_success([
        'message' => 'Marked as read',
        'count' => unmask_get_records_read_count(get_current_user_id())
    ]);
}
add_action('wp_ajax_unmask_mark_record_read', 'unmask_ajax_mark_record_read');
