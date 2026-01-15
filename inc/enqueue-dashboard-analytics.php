<?php
/**
 * UNMASK Dashboard Analytics - Enqueue
 *
 * Loads CSS for analytics dashboard page template
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue dashboard analytics styles
 */
function unmask_enqueue_dashboard_analytics() {
    if (!is_page_template('page-templates/template-dashboard-analytics.php')) {
        return;
    }

    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style(
        'unmask-dashboard-analytics',
        get_stylesheet_directory_uri() . '/assets/css/pages/dashboard-analytics.css',
        array('buddyboss-theme-css'),
        $theme_version
    );
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_dashboard_analytics', 15);
