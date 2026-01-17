<?php
/**
 * Session Log CSS Enqueue
 *
 * Conditionally loads session-log.css for posts in Session Logs category
 * or using single-session-log.php template.
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
 * Helper function to check if current post is a session log
 * Uses multiple detection methods for reliability
 */
function unmask_is_session_log() {
    // Only on single posts
    if (!is_single()) {
        return false;
    }

    global $post;
    if (!$post) {
        return false;
    }

    // Method 1: Check if using single-session-log.php template
    $template = get_page_template_slug($post->ID);
    if ($template === 'single-session-log.php') {
        return true;
    }

    // Method 2: Check if post is in Session Logs category (ID: 130)
    if (in_category(130, $post)) {
        return true;
    }

    // Method 3: Check by category slug
    if (has_category('session-logs', $post)) {
        return true;
    }

    return false;
}

/**
 * Enqueue Session Log styles AFTER BuddyBoss theme CSS
 *
 * Uses priority 999 to load after BuddyBoss theme CSS.
 * Dependencies include design system and BuddyBoss template CSS.
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_session_log_styles', 999);
function unmask_enqueue_session_log_styles() {
    // Only load on session log posts
    if (!unmask_is_session_log()) {
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
    $css_file = get_stylesheet_directory() . '/assets/css/pages/session-log.css';
    $version = file_exists($css_file) ? filemtime($css_file) : time();

    // Enqueue session log styles
    wp_enqueue_style(
        'unmask-session-log',
        get_stylesheet_directory_uri() . '/assets/css/pages/session-log.css',
        $deps,
        $version
    );
}
