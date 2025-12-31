<?php
/**
 * UNMASK Child Theme Functions
 * 
 * All custom PHP snippets consolidated here
 */

// Enqueue parent theme styles
add_action('wp_enqueue_scripts', 'unmask_enqueue_styles');
function unmask_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'));
}

/* ==========================================================================
   CUSTOM SNIPPETS GO BELOW
   ========================================================================== */