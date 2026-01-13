<?php
/**
 * UNMASK Analytics - Script Enqueue
 *
 * Loads behavioral tracking script for GA4 via GTM
 * Only loads on frontend, not admin
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue UNMASK Analytics script
 */
function unmask_enqueue_analytics() {
    // Don't load in admin
    if (is_admin()) {
        return;
    }

    // Don't load for logged-in admins if debugging
    // Uncomment to exclude admins from tracking:
    // if (current_user_can('manage_options')) {
    //     return;
    // }

    $theme_version = wp_get_theme()->get('Version');

    // Use v2 analytics (lean, focused tracking)
    // Set to false to revert to original 640-line script
    $use_v2 = true;

    $script_file = $use_v2 ? '/assets/js/unmask-analytics-v2.js' : '/assets/js/unmask-analytics.js';

    wp_enqueue_script(
        'unmask-analytics',
        get_stylesheet_directory_uri() . $script_file,
        array(), // No dependencies - runs independently
        $theme_version,
        array(
            'strategy' => 'defer', // Non-blocking load
            'in_footer' => true    // Load in footer
        )
    );
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_analytics', 99);
