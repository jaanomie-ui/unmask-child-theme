<?php
/**
 * Submit Hub Page - Conditional Enqueue
 *
 * @package UNMASK
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Submit Hub page styles
 */
function unmask_enqueue_submit_styles() {
    // Only load on Submit Hub template
    if (!is_page_template('page-templates/template-submit.php')) {
        return;
    }

    wp_enqueue_style(
        'unmask-submit',
        get_stylesheet_directory_uri() . '/assets/css/pages/submit.css',
        ['buddyboss-theme-css'],
        filemtime(get_stylesheet_directory() . '/assets/css/pages/submit.css')
    );
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_submit_styles', 20);
