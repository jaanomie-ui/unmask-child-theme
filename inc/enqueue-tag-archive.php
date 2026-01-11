<?php
/**
 * Tag Archive Enqueue
 *
 * Loads archive-magazine.css (base) + tag-archive.css on tag archive pages.
 *
 * @package unmask-child-theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Tag Archive styles
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_tag_archive_styles', 999);
function unmask_enqueue_tag_archive_styles() {
    // Only load on tag archive pages
    if (!is_tag()) {
        return;
    }

    $deps = array('unmask-00-design-system');

    // Add BuddyBoss dependency if available
    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    // Enqueue archive-magazine.css for base grid/card styles
    $archive_css = get_stylesheet_directory() . '/assets/css/pages/archive-magazine.css';
    $archive_version = file_exists($archive_css) ? filemtime($archive_css) : time();

    wp_enqueue_style(
        'unmask-archive-magazine',
        get_stylesheet_directory_uri() . '/assets/css/pages/archive-magazine.css',
        $deps,
        $archive_version
    );

    // Enqueue tag-archive.css for tag-specific styles
    $tag_css = get_stylesheet_directory() . '/assets/css/pages/tag-archive.css';
    $tag_version = file_exists($tag_css) ? filemtime($tag_css) : time();

    wp_enqueue_style(
        'unmask-tag-archive',
        get_stylesheet_directory_uri() . '/assets/css/pages/tag-archive.css',
        array('unmask-archive-magazine'),
        $tag_version
    );
}
