<?php
/**
 * Iso Post Page CSS Enqueue
 *
 * Conditionally loads iso-post.css only on Iso Post page template.
 * Loads after BuddyBoss theme CSS to ensure proper cascade.
 *
 * @package UNMASK
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if current page uses Iso Post template
 */
function unmask_is_iso_post_page() {
    // Template path MUST match exactly: page-templates/template-iso-post.php
    $template_path = 'page-templates/template-iso-post.php';

    // Method 1: Check page template slug
    $template_slug = get_page_template_slug();
    if ($template_slug === $template_path) {
        return true;
    }

    // Method 2: Check is_page_template
    if (is_page_template($template_path)) {
        return true;
    }

    // Method 3: Check stored template meta
    global $post;
    if ($post && is_page()) {
        $stored_template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($stored_template === $template_path) {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue Iso Post page styles
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_iso_post_styles', 999);
function unmask_enqueue_iso_post_styles() {
    if (!unmask_is_iso_post_page()) {
        return;
    }

    $deps = array('unmask-00-design-system');

    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    $css_file = get_stylesheet_directory() . '/assets/css/pages/iso-post.css';
    $version = file_exists($css_file) ? filemtime($css_file) : time();

    wp_enqueue_style(
        'unmask-iso-post',
        get_stylesheet_directory_uri() . '/assets/css/pages/iso-post.css',
        $deps,
        $version
    );
}
