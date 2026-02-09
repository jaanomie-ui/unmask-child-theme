<?php
/**
 * Conditional CSS Enqueue: Training Guide
 *
 * Enqueues training-guide.css only on pages using the
 * Training Guide (Custom) template.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Training Guide stylesheet
 */
function unmask_enqueue_training_guide_css() {
    // Check if we're on a page using the Training Guide template
    if (is_page_template('page-templates/template-training-guide.php')) {
        wp_enqueue_style(
            'unmask-training-guide',
            get_stylesheet_directory_uri() . '/assets/css/pages/training-guide.css',
            array('buddyboss-theme-css'), // Depend on BuddyBoss for proper cascade
            filemtime(get_stylesheet_directory() . '/assets/css/pages/training-guide.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_training_guide_css', 20);
