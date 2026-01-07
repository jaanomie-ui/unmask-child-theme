<?php
/**
 * Dossier View Asset Enqueue
 *
 * Clean enqueue for the new profile view template.
 * Dequeues BuddyBoss profile CSS, loads dossier-view.css.
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Redirect member base URL to profile tab
 * /members/username/ → /members/username/profile/
 */
function unmask_redirect_member_to_profile() {
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
add_action('template_redirect', 'unmask_redirect_member_to_profile');

/**
 * Dequeue conflicting CSS on member pages
 * Priority 999 ensures we run after other enqueues
 */
function unmask_dequeue_profile_conflicts() {
    if (!function_exists('bp_is_user') || !bp_is_user()) {
        return;
    }

    // Dequeue BuddyBoss profile styles
    wp_dequeue_style('buddyboss-theme-buddypress-css');

    // Dequeue old profile.css (replaced by dossier-view.css)
    wp_dequeue_style('unmask-profile');
}
add_action('wp_enqueue_scripts', 'unmask_dequeue_profile_conflicts', 999);

/**
 * Enqueue dossier view assets
 * Priority 100 - high enough to override defaults, low enough to be readable
 */
function unmask_enqueue_dossier_view() {
    if (!function_exists('bp_is_user') || !bp_is_user()) {
        return;
    }

    $theme_uri = get_stylesheet_directory_uri();
    $theme_dir = get_stylesheet_directory();

    // CSS: dossier-view.css depends on design system
    $css_file = $theme_dir . '/assets/css/pages/dossier-view.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'unmask-dossier-view',
            $theme_uri . '/assets/css/pages/dossier-view.css',
            ['unmask-00-design-system'], // depends on design tokens
            filemtime($css_file)
        );
    }

    // JS: Only on own profile for inline editing
    if (function_exists('bp_is_my_profile') && bp_is_my_profile()) {
        $js_file = $theme_dir . '/assets/js/dossier.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'unmask-dossier',
                $theme_uri . '/assets/js/dossier.js',
                [],
                filemtime($js_file),
                true
            );

            wp_localize_script('unmask-dossier', 'unmaskDossier', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('unmask_dossier_nonce'),
                'isOwn' => true,
                'userId' => bp_displayed_user_id()
            ]);
        }
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_dossier_view', 100);

/**
 * AJAX: Toggle dashboard visibility (legacy)
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

    if (function_exists('unmask_set_dashboard_visibility')) {
        unmask_set_dashboard_visibility(get_current_user_id(), $visibility);
    }

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

    if ((int) $iso->post_author !== get_current_user_id()) {
        wp_send_json_error(['message' => 'Not your ISO']);
    }

    // Set expiration to yesterday
    $yesterday = date('Ymd', strtotime('-1 day'));

    if (function_exists('update_field')) {
        update_field('iso_expiration', $yesterday, $iso_id);
    } else {
        update_post_meta($iso_id, 'iso_expiration', $yesterday);
    }

    wp_send_json_success(['message' => 'ISO expired']);
}
add_action('wp_ajax_unmask_expire_iso', 'unmask_ajax_expire_iso');
