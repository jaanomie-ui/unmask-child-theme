<?php
/**
 * Enqueue Single Post Styles
 *
 * Loads typography and styling for single post/article pages
 */

if (!defined('ABSPATH')) {
    exit;
}

function unmask_enqueue_single_post_styles() {
    if (is_single()) {
        wp_enqueue_style(
            'unmask-single-post',
            get_stylesheet_directory_uri() . '/assets/css/pages/single-post.css',
            array('parent-style'),
            filemtime(get_stylesheet_directory() . '/assets/css/pages/single-post.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_single_post_styles');
