<?php
/**
 * ISO Board Enqueue
 *
 * Conditionally loads CSS and registers AJAX handlers for ISO Board.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper function to check if current page uses ISO Board template
 * Uses multiple detection methods for reliability
 */
function unmask_is_iso_board_page() {
    // Method 1: Check page template slug
    $template_slug = get_page_template_slug();
    if ($template_slug === 'page-templates/template-iso-board.php') {
        return true;
    }

    // Method 2: Check is_page_template (backup)
    if (is_page_template('page-templates/template-iso-board.php')) {
        return true;
    }

    // Method 3: Check stored template meta
    global $post;
    if ($post && is_page()) {
        $stored_template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($stored_template === 'page-templates/template-iso-board.php') {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue ISO Board styles
 */
function unmask_enqueue_iso_board_styles() {
    // Load on ISO Board template or ISO single posts
    if (unmask_is_iso_board_page() || is_singular('iso')) {
        wp_enqueue_style(
            'unmask-iso-board',
            get_stylesheet_directory_uri() . '/assets/css/pages/iso-board.css',
            array('buddyboss-theme-css'),
            filemtime(get_stylesheet_directory() . '/assets/css/pages/iso-board.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_iso_board_styles');

/**
 * AJAX Handler: Filter ISO listings
 */
function unmask_ajax_iso_filter() {
    // Verify nonce for CSRF protection
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'unmask_iso_board_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }

    $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    $factory = isset($_GET['factory']) ? sanitize_text_field($_GET['factory']) : '';

    // Build meta query
    $meta_query = [
        'relation' => 'AND',
        [
            'key' => 'iso_expiration',
            'value' => date('Ymd'),
            'compare' => '>=',
            'type' => 'DATE'
        ]
    ];

    if ($type && in_array($type, ['seeking', 'offering'])) {
        $meta_query[] = ['key' => 'iso_type', 'value' => $type];
    }

    if ($category) {
        $meta_query[] = ['key' => 'iso_category', 'value' => $category];
    }

    if ($factory === 'yes') {
        $meta_query[] = ['key' => 'iso_factory', 'value' => ['yes', 'preferred'], 'compare' => 'IN'];
    }

    $iso_query = new WP_Query([
        'post_type' => 'iso',
        'posts_per_page' => 30,
        'meta_query' => $meta_query,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $show_blur = !is_user_logged_in();
    $total_posts = $iso_query->found_posts;

    // Random position for promo card
    $promo_position = $total_posts > 2 ? rand(2, min($total_posts, 8)) : 1;

    ob_start();

    if ($iso_query->have_posts()) {
        $card_index = 0;
        while ($iso_query->have_posts()) {
            $iso_query->the_post();
            // Insert promo card at random position
            if ($card_index === $promo_position) {
                get_template_part('template-parts/components/iso-factory-promo-card');
            }

            get_template_part('template-parts/components/iso-listing-card', null, [
                'post_id' => get_the_ID(),
                'is_blurred' => $show_blur
            ]);
            $card_index++;
        }

        // If promo position is after all cards, add it at the end
        if ($promo_position >= $card_index) {
            get_template_part('template-parts/components/iso-factory-promo-card');
        }
    } else {
        get_template_part('template-parts/components/iso-factory-promo-card');
        echo '<div class="iso-empty">No listings match your filters.</div>';
    }

    wp_reset_postdata();

    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'count' => $iso_query->found_posts
    ]);
}
add_action('wp_ajax_unmask_iso_filter', 'unmask_ajax_iso_filter');
add_action('wp_ajax_nopriv_unmask_iso_filter', 'unmask_ajax_iso_filter');

/**
 * AJAX Handler: Get ISO detail for modal
 */
function unmask_ajax_iso_detail() {
    // Verify nonce for CSRF protection
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'unmask_iso_board_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
        return;
    }

    $iso_id = isset($_GET['iso_id']) ? intval($_GET['iso_id']) : 0;

    if (!$iso_id) {
        wp_send_json_error(['message' => 'Invalid ISO ID']);
        return;
    }

    $post = get_post($iso_id);
    if (!$post || $post->post_type !== 'iso') {
        wp_send_json_error(['message' => 'ISO not found']);
        return;
    }

    // Get ACF fields
    $iso_type = get_field('iso_type', $iso_id);
    $iso_category = get_field('iso_category', $iso_id);
    $iso_factory = get_field('iso_factory', $iso_id);
    $iso_location = get_field('iso_location', $iso_id);
    $iso_expiration = get_field('iso_expiration', $iso_id);

    // Author info
    $author_id = $post->post_author;
    $designation = function_exists('unmask_get_user_designation')
        ? unmask_get_user_designation($author_id)
        : 'V-' . str_pad($author_id, 3, '0', STR_PAD_LEFT);
    $is_drone = function_exists('unmask_user_is_drone')
        ? unmask_user_is_drone($author_id)
        : false;

    // Avatar
    $avatar_url = '';
    if (function_exists('bp_core_fetch_avatar')) {
        $avatar_url = bp_core_fetch_avatar([
            'item_id' => $author_id,
            'type' => 'thumb',
            'html' => false
        ]);
    } else {
        $avatar_url = get_avatar_url($author_id, ['size' => 96]);
    }

    // Days left
    $days_left = 0;
    if ($iso_expiration) {
        $expiration_date = DateTime::createFromFormat('Ymd', $iso_expiration);
        if (!$expiration_date) {
            $expiration_date = DateTime::createFromFormat('Y-m-d', $iso_expiration);
        }
        if ($expiration_date) {
            $now = new DateTime();
            $diff = $now->diff($expiration_date);
            $days_left = $diff->invert ? 0 : $diff->days;
        }
    }

    // Time ago
    $time_ago = human_time_diff(get_the_time('U', $iso_id), current_time('timestamp'));

    // Content
    $content = apply_filters('the_content', $post->post_content);

    wp_send_json_success([
        'id' => $iso_id,
        'title' => $post->post_title,
        'content' => $content,
        'type' => $iso_type,
        'category' => $iso_category,
        'factory' => $iso_factory,
        'location' => $iso_location,
        'days_left' => $days_left,
        'author_id' => $author_id,
        'designation' => $designation,
        'is_drone' => $is_drone,
        'avatar' => $avatar_url,
        'time_ago' => $time_ago
    ]);
}
add_action('wp_ajax_unmask_iso_detail', 'unmask_ajax_iso_detail');
add_action('wp_ajax_nopriv_unmask_iso_detail', 'unmask_ajax_iso_detail');
