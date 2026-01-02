<?php
/**
 * UNMASK Child Theme Functions
 * 
 * All custom PHP snippets consolidated here
 */

// Enqueue parent theme styles and CSS partials
add_action('wp_enqueue_scripts', 'unmask_enqueue_styles');
function unmask_enqueue_styles() {
    $theme_version = wp_get_theme()->get('Version');
    $css_dir = get_stylesheet_directory_uri() . '/assets/css/';

    // Parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Child theme main stylesheet (for WordPress theme detection)
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'), $theme_version);

    // CSS Partials - loaded in cascade order
    $css_partials = array(
        '00-design-system' => 'Design system tokens',
        '01-base'          => 'Base styles and resets',
        '02-components'    => 'UI components',
        '03-buddyboss'     => 'BuddyBoss overrides',
        '04-memberpress'   => 'MemberPress overrides',
        '05-plugins'       => 'Plugin overrides',
        '06-pages'         => 'Page-specific styles',
        '07-dashboard'     => 'Dashboard styles',
        '08-utilities'     => 'Utility classes',
    );

    $prev_handle = 'child-style';

    foreach ($css_partials as $file => $description) {
        $handle = 'unmask-' . $file;
        wp_enqueue_style(
            $handle,
            $css_dir . $file . '.css',
            array($prev_handle),
            $theme_version
        );
        $prev_handle = $handle;
    }

    // Layout system - loaded after design system, before other partials use it
    wp_enqueue_style(
        'unmask-layout-system',
        $css_dir . 'unmask-layout-system.css',
        array('unmask-00-design-system'),
        $theme_version
    );
}

/* ==========================================================================
   CUSTOM SNIPPETS GO BELOW
   ========================================================================== */