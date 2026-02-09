<?php
/**
 * Enqueue Rehearsal Form Assets
 *
 * Conditionally loads CSS when rehearsal form shortcode is present
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue rehearsal form styles when shortcode is present
 */
function unmask_enqueue_rehearsal_form_assets() {
    // Check if rehearsal form shortcode is present on page
    global $post;
    if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'hive_rehearsal_plan')) {
        return;
    }

    wp_enqueue_style(
        'unmask-rehearsal-form',
        get_stylesheet_directory_uri() . '/assets/css/components/rehearsal-form.css',
        array('unmask-00-design-system'),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_rehearsal_form_assets');
