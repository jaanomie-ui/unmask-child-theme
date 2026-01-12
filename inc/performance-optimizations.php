<?php
/**
 * Performance Optimizations
 *
 * Dequeues unused scripts and styles based on page context.
 * This keeps the site looking identical while removing bloat.
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Dequeue unused plugin assets per page context
 *
 * Priority 999 ensures this runs after plugins enqueue their assets
 */
add_action('wp_enqueue_scripts', 'unmask_performance_dequeue', 999);
function unmask_performance_dequeue() {
    if (is_admin()) return;

    $post_content = get_post()->post_content ?? '';

    // =========================================================================
    // MODERN EVENTS CALENDAR
    // If you're not using MEC, deactivate the plugin entirely for best results.
    // This code is a fallback if you need MEC active but want it conditional.
    // =========================================================================
    $is_mec_page = is_singular('mec-events') ||
                   is_post_type_archive('mec-events') ||
                   has_shortcode($post_content, 'MEC') ||
                   has_shortcode($post_content, 'MEC_calendar');

    if (!$is_mec_page) {
        // MEC Scripts (including BuddyBoss integration)
        $mec_scripts = [
            'mec-frontend-script',
            'mec-general-calendar-script',
            'mec-tooltip-script',
            'mec-featherlight-script',
            'mec-lity-script',
            'mec-select2-script',
            'mec-owl-carousel-script',
            'mec-events-script',
            'mec-colorbrightness-script',
            // MEC BuddyBoss Integration handles
            'mec-bp-js-script',
            'mec-bp-js',
            'mec-bp-js-blockui-js',
            'mec-bp-blockui',
            'mec-buddyboss',
            'mec-buddyboss-js',
        ];
        foreach ($mec_scripts as $handle) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }

        // MEC Styles
        $mec_styles = [
            'mec-frontend-style',
            'mec-general-calendar-style',
            'mec-font-icons',
            'mec-lity-style',
            'mec-select2-style',
            'mec-tooltip-style',
            'mec-tooltip-shadow-style',
            'mec-owl-carousel-style',
            'mec-single-builder',
            'mec-bp-main',
        ];
        foreach ($mec_styles as $handle) {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }

    // =========================================================================
    // WOOCOMMERCE
    // Only load on shop pages, cart, checkout, account
    // =========================================================================
    $is_woo_page = false;
    if (function_exists('is_woocommerce')) {
        $is_woo_page = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
    }

    if (!$is_woo_page) {
        // WooCommerce Scripts
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('wc-add-to-cart');
        wp_dequeue_script('wc-cart-fragments');
        wp_dequeue_script('wc-checkout');
        wp_dequeue_script('wc-single-product');
        wp_dequeue_script('wc-country-select');
        wp_dequeue_script('wc-address-i18n');

        // WooCommerce Styles (keep minimal for cart icon in header)
        // wp_dequeue_style('woocommerce-general');
        // wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_style('wc-blocks-style');
        wp_dequeue_style('wc-stripe-blocks-checkout-style');
    }

    // =========================================================================
    // VIDEO.JS / MEDIA PLAYER
    // Only load on pages with video content
    // CRITICAL: VideoJS seek-buttons plugin causes JS error that breaks touch handlers
    // Error: "TypeError: undefined is not an object (evaluating 'videojs__default["default"].getComponent')"
    // This error prevents horizontal swipe from working on carousels
    // =========================================================================
    $needs_video = is_singular('post') ||
                   (function_exists('bp_is_activity_component') && bp_is_activity_component()) ||
                   (function_exists('bp_is_group') && bp_is_group());

    if (!$needs_video) {
        // All possible VideoJS handle variations
        $videojs_scripts = [
            'bp-media-videojs',
            'bp-media-videojs-seek',
            'bp-media-videojs-seek-buttons',
            'bp-media-flv',
            'bp-media-videojs-flash',
            'bp-media-videojs-flv',
            'video-js',
            'videojs',
        ];
        foreach ($videojs_scripts as $handle) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
        wp_dequeue_style('bp-media-videojs-css');
        wp_dequeue_style('video-js');
    }

    // =========================================================================
    // FEATHERLIGHT / LIGHTBOX
    // Only load where gallery/lightbox is needed
    // =========================================================================
    $needs_lightbox = is_singular('post') ||
                      is_page_template('page-templates/template-archive-magazine.php');

    if (!$needs_lightbox) {
        wp_dequeue_script('featherlight');
        wp_dequeue_style('featherlight');
    }

    // =========================================================================
    // SIMPLY SCHEDULE APPOINTMENTS
    // Only load on booking/factory pages
    // =========================================================================
    $is_booking_page = is_page_template('page-templates/template-factory.php') ||
                       is_page('factory') ||
                       is_page('book') ||
                       is_page('booking') ||
                       has_shortcode($post_content, 'ssa_booking');

    if (!$is_booking_page) {
        wp_dequeue_script('ssa-booking-app');
        wp_dequeue_script('ssa-iframe-inner');
        wp_dequeue_script('ssa-tracking');
        wp_dequeue_style('ssa-styles');
        wp_dequeue_style('ssa-upcoming-appointments-card-style');
        wp_dequeue_style('flatpickr');
        wp_dequeue_script('flatpickr');
    }

    // =========================================================================
    // TUTOR LMS (if not using)
    // =========================================================================
    if (!is_singular('courses') && !is_post_type_archive('courses')) {
        wp_dequeue_script('bb-tutorlms-admin');
        wp_dequeue_style('bb-tutorlms-admin');
    }

    // =========================================================================
    // DROPZONE (file upload library)
    // Only load on profile/upload pages
    // =========================================================================
    $needs_dropzone = function_exists('bp_is_user') && bp_is_user();

    if (!$needs_dropzone) {
        wp_dequeue_script('bp-dropzone');
    }
}

/**
 * Aggressive VideoJS removal for homepage
 *
 * BuddyBoss Platform enqueues VideoJS in footer scripts which can bypass
 * the main wp_enqueue_scripts hook. This catches late-loaded scripts.
 *
 * The VideoJS seek-buttons plugin has a loading order bug that causes:
 * "TypeError: undefined is not an object (evaluating 'videojs__default["default"].getComponent')"
 * This JS error breaks ALL subsequent JavaScript including touch handlers.
 */
add_action('wp_print_footer_scripts', 'unmask_remove_videojs_footer', 1);
function unmask_remove_videojs_footer() {
    // Only on homepage where we don't need video
    if (!is_page_template('page-templates/template-homepage.php')) {
        return;
    }

    $videojs_scripts = [
        'bp-media-videojs',
        'bp-media-videojs-seek',
        'bp-media-videojs-seek-buttons',
        'bp-media-flv',
        'bp-media-videojs-flash',
        'bp-media-videojs-flv',
        'video-js',
        'videojs',
    ];
    foreach ($videojs_scripts as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}

/**
 * Defer non-critical JavaScript
 *
 * Adds defer attribute to scripts that don't need to block rendering
 * NOTE: Disabled - was causing BuddyBoss sidebar issues
 */
/*
add_filter('script_loader_tag', 'unmask_defer_scripts', 10, 3);
function unmask_defer_scripts($tag, $handle, $src) {
    // Scripts to defer (non-critical)
    $defer_scripts = [
        'twemoji',
        'bb-emoji-loader',
        'widget-members',
        'widget-groups',
        'woocommerce',
        'wc-add-to-cart',
    ];

    if (in_array($handle, $defer_scripts)) {
        // Don't double-add defer
        if (strpos($tag, 'defer') === false) {
            $tag = str_replace(' src', ' defer src', $tag);
        }
    }

    return $tag;
}
*/

/**
 * Preload critical fonts
 *
 * Add font preloading for faster text rendering
 */
add_action('wp_head', 'unmask_preload_fonts', 1);
function unmask_preload_fonts() {
    // Preload the main body font if you have custom fonts
    // Uncomment and adjust path if using custom fonts:
    /*
    $font_dir = get_stylesheet_directory_uri() . '/assets/fonts/';
    ?>
    <link rel="preload" href="<?php echo $font_dir; ?>your-font.woff2" as="font" type="font/woff2" crossorigin>
    <?php
    */
}

/**
 * Add resource hints for external domains
 * Preconnect is faster than dns-prefetch (includes TCP + TLS handshake)
 */
add_action('wp_head', 'unmask_resource_hints', 2);
function unmask_resource_hints() {
    // Preconnect for resources we'll definitely use
    $preconnect = [
        'https://fonts.googleapis.com',
        'https://fonts.gstatic.com',
    ];

    foreach ($preconnect as $origin) {
        echo '<link rel="preconnect" href="' . esc_url($origin) . '" crossorigin>' . "\n";
    }

    // DNS prefetch for resources we might use
    $dns_prefetch = [
        '//cloud.umami.is',  // Analytics
        '//stats.wp.com',    // Jetpack stats
    ];

    foreach ($dns_prefetch as $domain) {
        echo '<link rel="dns-prefetch" href="' . esc_url($domain) . '">' . "\n";
    }
}

/**
 * Remove query strings from static resources
 * Improves caching by CDNs and browsers
 */
add_filter('script_loader_src', 'unmask_remove_query_strings', 15);
add_filter('style_loader_src', 'unmask_remove_query_strings', 15);
function unmask_remove_query_strings($src) {
    if (!is_admin() && strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

/**
 * Remove WordPress emoji scripts (site uses CSS emoji)
 */
add_action('init', 'unmask_disable_wp_emojis');
function unmask_disable_wp_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    // Remove TinyMCE emoji
    add_filter('tiny_mce_plugins', function($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    });

    // Remove emoji DNS prefetch
    add_filter('wp_resource_hints', function($urls, $relation_type) {
        if ($relation_type === 'dns-prefetch') {
            $urls = array_filter($urls, function($url) {
                return strpos($url, 'https://s.w.org/images/core/emoji/') === false;
            });
        }
        return $urls;
    }, 10, 2);
}

/**
 * Disable jQuery Migrate (not needed for modern jQuery usage)
 * NOTE: Disabled - BuddyBoss sidebar requires jQuery Migrate
 */
/*
add_action('wp_default_scripts', 'unmask_remove_jquery_migrate');
function unmask_remove_jquery_migrate($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {
            $script->deps = array_diff($script->deps, ['jquery-migrate']);
        }
    }
}
*/
