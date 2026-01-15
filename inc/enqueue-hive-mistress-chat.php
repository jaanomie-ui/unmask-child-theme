<?php
/**
 * Hive Mistress Chat CSS Enqueue
 *
 * Conditionally loads hive-mistress-chat.css on the Hive Mistress AI page (ID 2496).
 * Loads with high priority to override WPCode inline styles.
 * Displays context header showing current sequence and objective.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Hive Mistress Chat styles
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_hive_mistress_chat_styles', 9999);
function unmask_enqueue_hive_mistress_chat_styles() {
    // Only load on Hive Mistress AI page (ID 2496)
    if (!is_page(2496)) {
        return;
    }

    // Use file modification time for cache busting
    $css_file = get_stylesheet_directory() . '/assets/css/pages/hive-mistress-chat.css';
    $version = file_exists($css_file) ? filemtime($css_file) : time();

    // Enqueue with very high priority to override inline styles
    wp_enqueue_style(
        'unmask-hive-mistress-chat',
        get_stylesheet_directory_uri() . '/assets/css/pages/hive-mistress-chat.css',
        array(), // No dependencies - load last
        $version
    );
}

/**
 * Note: Context header is now handled by the page template (template-hive-mistress-chat.php)
 * The filter below has been removed because the template provides better control over layout.
 */
