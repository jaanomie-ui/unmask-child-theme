<?php
/**
 * Conditional CSS Enqueue: Transmissions Archive
 *
 * Enqueues transmissions-archive.css only on pages using the
 * Transmissions Archive template.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Transmissions Archive stylesheet
 */
function unmask_enqueue_transmissions_archive_css() {
    // Check if we're on a page using the Transmissions Archive template
    if (is_page_template('page-templates/template-transmissions-archive.php')) {
        wp_enqueue_style(
            'unmask-transmissions-archive',
            get_stylesheet_directory_uri() . '/assets/css/pages/transmissions-archive.css',
            array('buddyboss-theme-css'), // Depend on BuddyBoss for proper cascade
            filemtime(get_stylesheet_directory() . '/assets/css/pages/transmissions-archive.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_transmissions_archive_css', 20);
