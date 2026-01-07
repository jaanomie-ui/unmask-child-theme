<?php
/**
 * Factory Page CSS Enqueue
 *
 * Conditionally loads factory.css only on The Factory page template.
 * Loads after BuddyBoss theme CSS to ensure proper cascade.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper function to check if current page uses Factory template
 * Uses multiple detection methods for reliability (like registration page)
 */
function unmask_is_factory_page() {
    // Method 1: Check page template slug
    $template_slug = get_page_template_slug();
    if ($template_slug === 'page-the-factory.php') {
        return true;
    }

    // Method 2: Check is_page_template (backup)
    if (is_page_template('page-the-factory.php')) {
        return true;
    }

    // Method 3: Check stored template meta
    global $post;
    if ($post && is_page()) {
        $stored_template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($stored_template === 'page-the-factory.php') {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue Factory page styles AFTER BuddyBoss theme CSS
 *
 * Uses priority 999 to load after BuddyBoss theme CSS.
 * Dependencies include design system and BuddyBoss template CSS.
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_factory_styles', 999);
function unmask_enqueue_factory_styles() {
    // Only load on The Factory page template
    if (!unmask_is_factory_page()) {
        return;
    }

    // Build dependencies array
    $deps = array('unmask-00-design-system');

    // Add BuddyBoss dependency if available (ensures our CSS loads after it)
    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    // Use file modification time for cache busting
    $css_file = get_stylesheet_directory() . '/assets/css/pages/factory.css';
    $version = file_exists($css_file) ? filemtime($css_file) : time();

    // Enqueue factory page styles
    wp_enqueue_style(
        'unmask-factory',
        get_stylesheet_directory_uri() . '/assets/css/pages/factory.css',
        $deps,
        $version
    );
}
