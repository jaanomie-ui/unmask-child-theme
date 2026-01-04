<?php
/**
 * Unified Card System CSS Enqueue
 *
 * Loads unmask-cards.css globally after the design system.
 * This provides the unified card architecture across all pages.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Unified Card System styles
 *
 * Uses priority 15 to load:
 * - After design system (priority 10)
 * - Before page-specific CSS (priority 999)
 *
 * This allows page-specific styles to override card defaults if needed.
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_card_styles', 15);
function unmask_enqueue_card_styles() {
    // Build dependencies array
    $deps = array('unmask-00-design-system');

    // Add BuddyBoss dependency if available
    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    // Use file modification time for cache busting
    $css_file = get_stylesheet_directory() . '/assets/css/unmask-cards.css';
    $version = file_exists($css_file) ? filemtime($css_file) : time();

    // Enqueue unified card system
    wp_enqueue_style(
        'unmask-cards',
        get_stylesheet_directory_uri() . '/assets/css/unmask-cards.css',
        $deps,
        $version
    );
}
