<?php
/**
 * Magazine Archive Page Enqueue
 *
 * Conditionally loads archive-magazine.css and archive-magazine.js only on the
 * Magazine Archive page template. Loads after BuddyBoss theme CSS to ensure
 * proper cascade. Also registers AJAX handlers for shuffle functionality.
 *
 * @package unmask-child-theme
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper function to check if current page uses Magazine Archive template
 * Uses multiple detection methods for reliability (like registration/factory pages)
 */
function unmask_is_archive_magazine_page() {
    // Method 1: Check page template slug
    $template_slug = get_page_template_slug();
    if ($template_slug === 'page-templates/template-archive-magazine.php') {
        return true;
    }

    // Method 2: Check is_page_template (backup)
    if (is_page_template('page-templates/template-archive-magazine.php')) {
        return true;
    }

    // Method 3: Check stored template meta
    global $post;
    if ($post && is_page()) {
        $stored_template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($stored_template === 'page-templates/template-archive-magazine.php') {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue Magazine Archive page styles AFTER BuddyBoss theme CSS
 *
 * Uses priority 999 to load after BuddyBoss theme CSS.
 * Dependencies include design system and BuddyBoss template CSS.
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_archive_magazine_styles', 999);
function unmask_enqueue_archive_magazine_styles() {
    // Only load on Magazine Archive page template
    if (!unmask_is_archive_magazine_page()) {
        return;
    }

    // Build dependencies array
    $deps = array('unmask-00-design-system');

    // Add BuddyBoss dependency if available (ensures our CSS loads after it)
    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    // Use file modification time for cache busting
    $css_file = get_stylesheet_directory() . '/assets/css/pages/archive-magazine.css';
    $version = file_exists($css_file) ? filemtime($css_file) : time();

    // Enqueue archive magazine page styles
    wp_enqueue_style(
        'unmask-archive-magazine',
        get_stylesheet_directory_uri() . '/assets/css/pages/archive-magazine.css',
        $deps,
        $version
    );
}

/**
 * Enqueue Magazine Archive JavaScript
 *
 * Loads the archive-magazine.js file only on the Magazine Archive page.
 * No jQuery dependency - vanilla JS only.
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue_archive_magazine_scripts', 999);
function unmask_enqueue_archive_magazine_scripts() {
    // Only load on Magazine Archive page template
    if (!unmask_is_archive_magazine_page()) {
        return;
    }

    // Use file modification time for cache busting
    $js_file = get_stylesheet_directory() . '/assets/js/archive-magazine.js';
    $version = file_exists($js_file) ? filemtime($js_file) : time();

    // Enqueue archive magazine JavaScript
    wp_enqueue_script(
        'unmask-archive-magazine',
        get_stylesheet_directory_uri() . '/assets/js/archive-magazine.js',
        array(), // No dependencies - vanilla JS
        $version,
        true // Load in footer
    );

    // Pass data to JavaScript
    wp_localize_script(
        'unmask-archive-magazine',
        'unmaskArchive',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('archive_nonce'),
        )
    );
}

/**
 * AJAX Handler: Get Records for Shuffle
 *
 * Returns a random selection of records for the shuffle modal.
 * Registered for both logged-in and non-logged-in users.
 */
function unmask_get_shuffle_records() {
    // Clean ALL output buffers that plugins might have added
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Temporarily disabled nonce verification for debugging
    // if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'archive_nonce')) {
    //     wp_send_json_error('Invalid nonce');
    //     return;
    // }

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 50,
        'post_status'    => 'publish',
        'orderby'        => 'rand',
        // Exclude d001-training-logs category
        'tax_query'      => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => 'd001-training-logs',
                'operator' => 'NOT IN',
            ),
        ),
    );

    $query = new WP_Query($args);
    $records = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Generate file ID
            $post_date = get_the_date('Y');
            $file_id = 'REC-' . $post_date . '-' . str_pad($post_id, 3, '0', STR_PAD_LEFT);

            // Get thumbnail - use large size for quality
            $thumbnail = get_the_post_thumbnail_url($post_id, 'large');
            if (!$thumbnail) {
                $thumbnail = get_stylesheet_directory_uri() . '/assets/images/placeholder.jpg';
            }

            // Get excerpt
            $excerpt = get_the_excerpt();
            if (empty($excerpt)) {
                $excerpt = wp_trim_words(get_the_content(), 20, '...');
            }

            $records[] = array(
                'id'        => $post_id,
                'file_id'   => $file_id,
                'title'     => get_the_title(),
                'excerpt'   => $excerpt,
                'url'       => get_permalink(),
                'thumbnail' => $thumbnail,
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success($records);
}
add_action('wp_ajax_get_shuffle_records', 'unmask_get_shuffle_records');
add_action('wp_ajax_nopriv_get_shuffle_records', 'unmask_get_shuffle_records');

/**
 * AJAX Handler: Filter Archive Records
 *
 * Returns HTML for filtered records grid (no page refresh).
 * Registered for both logged-in and non-logged-in users.
 */
function unmask_filter_archive_records() {
    // Clean ALL output buffers that plugins might have added
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Temporarily disabled nonce verification for debugging
    // if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'archive_nonce')) {
    //     wp_send_json_error('Invalid nonce');
    //     return;
    // }

    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $record_type = isset($_GET['record_type']) ? sanitize_text_field($_GET['record_type']) : 'all';
    $record_tags = isset($_GET['record_tags']) ? sanitize_text_field($_GET['record_tags']) : '';

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 12,
        'paged'          => $paged,
        'post_status'    => 'publish',
    );

    // Always exclude d001-training-logs
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => 'd001-training-logs',
            'operator' => 'NOT IN',
        ),
    );

    // Handle type filter
    if ($record_type !== 'all' && !empty($record_type)) {
        $args['tax_query']['relation'] = 'AND';
        $args['tax_query'][] = array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $record_type,
        );
    }

    // Handle tag filter
    if (!empty($record_tags)) {
        $tag_slugs = array_map('sanitize_text_field', explode(',', $record_tags));
        $args['tag_slug__in'] = $tag_slugs;
    }

    $query = new WP_Query($args);

    ob_start();

    $counter = 0;
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $counter++;
            $post_id = get_the_ID();

            // Get record type (category)
            $categories = get_the_category();
            $cat_type = 'profile';
            if ($categories) {
                foreach ($categories as $cat) {
                    if ($cat->slug === 'events') {
                        $cat_type = 'event';
                        break;
                    }
                }
            }

            // Get thumbnail
            $thumbnail = get_the_post_thumbnail_url($post_id, 'large');
            if (!$thumbnail) {
                $thumbnail = get_stylesheet_directory_uri() . '/assets/images/placeholder.jpg';
            }

            // Get custom fields
            $location = get_post_meta($post_id, 'record_location', true) ?: 'Chicago';
            $views = get_post_meta($post_id, 'post_views_count', true) ?: rand(50, 300);

            // Get excerpt
            $excerpt = get_the_excerpt();
            if (empty($excerpt)) {
                $excerpt = wp_trim_words(get_the_content(), 20, '...');
            }
            ?>

            <article class="card card--record card--fullbleed card--archive">
                <a href="<?php the_permalink(); ?>" class="card__link">
                    <div class="card__image">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                    </div>
                    <span class="card__type card__type--<?php echo esc_attr($cat_type); ?>">
                        <?php echo esc_html($cat_type); ?>
                    </span>
                    <div class="card__reveal">
                        <p class="card__reveal-text"><?php echo esc_html($excerpt); ?></p>
                        <div class="card__reveal-stats">
                            <span><?php echo esc_html($views); ?> views</span>
                            <span><?php echo esc_html($location); ?></span>
                        </div>
                    </div>
                    <div class="card__overlay">
                        <h3 class="card__title"><?php the_title(); ?></h3>
                    </div>
                </a>
            </article>

            <?php
            // Insert submit card after 4th item on first page
            if ($counter === 4 && $paged === 1) :
            ?>
                <article class="archive-submit-card">
                    <a href="/submit" class="archive-submit-link">
                        <span class="archive-submit-icon">◇</span>
                        <span class="archive-submit-label">open call</span>
                        <p class="archive-submit-title">The next record could be yours.</p>
                        <p class="archive-submit-text">Writers, photographers, and subjects. The archive is waiting.</p>
                    </a>
                </article>
            <?php
            endif;
        endwhile;
        wp_reset_postdata();
    else :
    ?>
        <div class="archive-no-records">
            <p>No records found.</p>
        </div>
    <?php endif;

    $html = ob_get_clean();

    // Build pagination
    $pagination = '';
    if ($query->max_num_pages > 1) {
        ob_start();
        echo '<nav class="archive-pagination">';
        echo paginate_links(array(
            'total'     => $query->max_num_pages,
            'current'   => $paged,
            'prev_text' => '← previous',
            'next_text' => 'next →',
            'type'      => 'list',
        ));
        echo '</nav>';
        $pagination = ob_get_clean();
    }

    wp_send_json_success(array(
        'html'       => $html,
        'pagination' => $pagination,
        'found'      => $query->found_posts,
        'max_pages'  => $query->max_num_pages,
    ));
}
add_action('wp_ajax_filter_archive_records', 'unmask_filter_archive_records');
add_action('wp_ajax_nopriv_filter_archive_records', 'unmask_filter_archive_records');

/**
 * Register Custom Meta Fields for Records
 * Optional - if you want to use custom fields for location, subject name, etc.
 */
function unmask_register_record_meta() {
    register_post_meta('post', 'subject_name', array(
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'string',
        'description'   => 'Subject name for the record',
    ));

    register_post_meta('post', 'record_location', array(
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'string',
        'description'   => 'Location where record was filed',
    ));

    register_post_meta('post', 'post_views_count', array(
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'integer',
        'description'   => 'Number of times the record has been viewed',
    ));
}
add_action('init', 'unmask_register_record_meta');

/**
 * Track Post Views
 * Call this on single post template: unmask_track_post_view(get_the_ID());
 */
function unmask_track_post_view($post_id) {
    if (!is_single()) return;
    if (empty($post_id)) return;

    // Don't count logged in admin views
    if (current_user_can('manage_options')) return;

    $count = get_post_meta($post_id, 'post_views_count', true);
    $count = $count ? (int) $count : 0;
    $count++;

    update_post_meta($post_id, 'post_views_count', $count);
}
