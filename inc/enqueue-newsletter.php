<?php
/**
 * Enqueue Newsletter Assets
 *
 * Loads newsletter card CSS when needed.
 * Only loads for logged-out users or pages with newsletter cards.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', 'unmask_enqueue_newsletter_assets');

function unmask_enqueue_newsletter_assets() {
    // Load newsletter CSS globally for now
    // Can be optimized later to only load on specific pages
    wp_enqueue_style(
        'unmask-newsletter-card',
        get_stylesheet_directory_uri() . '/assets/css/components/newsletter-card.css',
        array('unmask-cards'), // Depends on card system
        filemtime(get_stylesheet_directory() . '/assets/css/components/newsletter-card.css')
    );
}
