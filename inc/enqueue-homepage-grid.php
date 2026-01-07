<?php
/**
 * Homepage Grid CSS Enqueue
 *
 * Conditionally loads homepage grid and card CSS on UNMASK Homepage template.
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
 * Helper function to check if current page uses Homepage template
 */
function unmask_is_homepage_template() {
    // Method 1: Check page template slug
    $template_slug = get_page_template_slug();
    if ($template_slug === 'page-templates/template-homepage.php') {
        return true;
    }

    // Method 2: Check is_page_template (backup)
    if (is_page_template('page-templates/template-homepage.php')) {
        return true;
    }

    // Method 3: Check stored template meta
    global $post;
    if ($post && is_page()) {
        $stored_template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($stored_template === 'page-templates/template-homepage.php') {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue Homepage grid styles AFTER BuddyBoss theme CSS
 *
 * Uses priority 999 to load after BuddyBoss theme CSS.
 * Dependencies include design system, cards, and BuddyBoss template CSS.
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_homepage_grid_styles', 999);
function unmask_enqueue_homepage_grid_styles() {
    // Only load on UNMASK Homepage template
    if (!unmask_is_homepage_template()) {
        return;
    }

    // Build dependencies array
    $deps = array('unmask-00-design-system');

    // Add BuddyBoss dependency if available
    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    // Enqueue cards CSS first (grid depends on it)
    $cards_file = get_stylesheet_directory() . '/assets/css/unmask-cards.css';
    $cards_version = file_exists($cards_file) ? filemtime($cards_file) : time();

    wp_enqueue_style(
        'unmask-cards',
        get_stylesheet_directory_uri() . '/assets/css/unmask-cards.css',
        $deps,
        $cards_version
    );

    // Enqueue homepage grid CSS
    $grid_file = get_stylesheet_directory() . '/assets/css/unmask-homepage-grid.css';
    $grid_version = file_exists($grid_file) ? filemtime($grid_file) : time();

    wp_enqueue_style(
        'unmask-homepage-grid',
        get_stylesheet_directory_uri() . '/assets/css/unmask-homepage-grid.css',
        array('unmask-cards'),
        $grid_version
    );

    // Enqueue homepage rails JS (fixes swipe on mobile)
    $rails_js_file = get_stylesheet_directory() . '/assets/js/homepage-rails.js';
    $rails_js_version = file_exists($rails_js_file) ? filemtime($rails_js_file) : time();

    wp_enqueue_script(
        'unmask-homepage-rails',
        get_stylesheet_directory_uri() . '/assets/js/homepage-rails.js',
        array(),
        $rails_js_version,
        true // Load in footer
    );
}
