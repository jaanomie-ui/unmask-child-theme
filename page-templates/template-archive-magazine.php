<?php
/**
 * Template Name: Magazine Archive
 * Description: Archive page for UNMASK magazine records. Terminal energy. Platform presence. Erotic tension.
 *
 * @package unmask-child-theme
 *
 * ACF Fields (non-repeating):
 * - archive_page_title: Text (default: "the archive")
 * - archive_stat_records: Text (default: "RECORDS:")
 * - archive_stat_profiles: Text (default: "PROFILES:")
 * - archive_stat_events: Text (default: "EVENTS:")
 * - archive_filter_type: Text (default: "type")
 * - archive_filter_tags: Text (default: "tags")
 * - archive_shuffle_btn: Text (default: "[shuffle]")
 * - archive_default_location: Text (default: "Chicago")
 * - archive_cta_label: Text (default: "open call")
 * - archive_cta_title: Text (default: "The next record could be yours.")
 * - archive_cta_text: Text (default: "Writers, photographers, and subjects. The archive is waiting.")
 * - archive_empty_message: Text (default: "No records found.")
 * - archive_prev_text: Text (default: "← previous")
 * - archive_next_text: Text (default: "next →")
 * - archive_open_record_btn: Text (default: "[open record]")
 */

// Get ACF field values with fallbacks
$archive_page_title = unmask_get_field('archive_page_title', 'the archive');
$archive_stat_records = unmask_get_field('archive_stat_records', 'RECORDS:');
$archive_stat_profiles = unmask_get_field('archive_stat_profiles', 'PROFILES:');
$archive_stat_events = unmask_get_field('archive_stat_events', 'EVENTS:');
$archive_filter_type = unmask_get_field('archive_filter_type', 'type');
$archive_filter_tags = unmask_get_field('archive_filter_tags', 'tags');
$archive_shuffle_btn = unmask_get_field('archive_shuffle_btn', '[shuffle]');
$archive_default_location = unmask_get_field('archive_default_location', 'Chicago');
$archive_cta_label = unmask_get_field('archive_cta_label', 'open call');
$archive_cta_title = unmask_get_field('archive_cta_title', 'The next record could be yours.');
$archive_cta_text = unmask_get_field('archive_cta_text', 'Writers, photographers, and subjects. The archive is waiting.');
$archive_empty_message = unmask_get_field('archive_empty_message', 'No records found.');
$archive_prev_text = unmask_get_field('archive_prev_text', '← previous');
$archive_next_text = unmask_get_field('archive_next_text', 'next →');
$archive_open_record_btn = unmask_get_field('archive_open_record_btn', '[open record]');

get_header();

// Query args for records
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'post_status'    => 'publish',
);

// Always exclude d001 Training Logs category
$args['tax_query'] = array(
    array(
        'taxonomy' => 'category',
        'field'    => 'slug',
        'terms'    => 'd001-training-logs',
        'operator' => 'NOT IN',
    ),
);

// Handle type filter
if (isset($_GET['record_type']) && $_GET['record_type'] !== 'all') {
    $type_slug = sanitize_text_field($_GET['record_type']);
    $args['tax_query']['relation'] = 'AND';
    $args['tax_query'][] = array(
        'taxonomy' => 'category',
        'field'    => 'slug',
        'terms'    => $type_slug,
    );
}

// Handle tag filter
if (isset($_GET['record_tags']) && !empty($_GET['record_tags'])) {
    $tag_slugs = array_map('sanitize_text_field', explode(',', $_GET['record_tags']));
    $args['tag_slug__in'] = $tag_slugs;
}

// Check if any filters are active (for reset button)
$has_active_filters = (isset($_GET['record_type']) && $_GET['record_type'] !== 'all') ||
                      (isset($_GET['record_tags']) && !empty($_GET['record_tags']));

$records_query = new WP_Query($args);

// Get counts for stats (excluding d001-training-logs)
$d001_cat = get_category_by_slug('d001-training-logs');
$d001_count = $d001_cat ? $d001_cat->count : 0;
$total_records = wp_count_posts()->publish - $d001_count;
$profiles_count = get_category_by_slug('profiles') ? get_category_by_slug('profiles')->count : 0;
$events_count = get_category_by_slug('events') ? get_category_by_slug('events')->count : 0;

// Get all tags for filter
$all_tags = get_tags(array('hide_empty' => true, 'number' => 10));
?>

<div class="archive-container">
    <!-- MAIN CONTENT -->
    <main class="archive-main">

        <!-- HEADER: 3 columns (title | empty | stats) -->
        <header class="archive-header">
            <h1 class="archive-title"><?php echo esc_html($archive_page_title); ?></h1>
            <div class="archive-header-empty"></div>
            <div class="archive-header-stats">
                <span><?php echo esc_html($archive_stat_records); ?> <?php echo esc_html($total_records); ?></span>
                <span><?php echo esc_html($archive_stat_profiles); ?> <?php echo esc_html($profiles_count); ?></span>
                <span><?php echo esc_html($archive_stat_events); ?> <?php echo esc_html($events_count); ?></span>
            </div>
        </header>

        <!-- FILTER GRID: 3 columns × 3 rows -->
        <div class="archive-filters">
            <!-- Row 1: Type -->
            <div class="archive-filter-row archive-filter-row--type">
                <span class="archive-filter-label"><?php echo esc_html($archive_filter_type); ?></span>
                <div class="archive-filter-group" data-filter="type">
                    <button class="archive-filter-btn <?php echo (!isset($_GET['record_type']) || $_GET['record_type'] === 'all') ? 'active' : ''; ?>" data-value="all">all</button>
                    <button class="archive-filter-btn <?php echo (isset($_GET['record_type']) && $_GET['record_type'] === 'profiles') ? 'active' : ''; ?>" data-value="profiles">profiles</button>
                    <button class="archive-filter-btn <?php echo (isset($_GET['record_type']) && $_GET['record_type'] === 'events') ? 'active' : ''; ?>" data-value="events">events</button>
                </div>
            </div>

            <!-- Row 2: Tags (spans into column 2) -->
            <div class="archive-filter-row archive-filter-row--tags">
                <span class="archive-filter-label"><?php echo esc_html($archive_filter_tags); ?></span>
                <div class="archive-filter-group" data-filter="tags">
                    <?php
                    $active_tags = isset($_GET['record_tags']) ? explode(',', $_GET['record_tags']) : array();
                    foreach ($all_tags as $tag) :
                        $is_active = in_array($tag->slug, $active_tags);
                    ?>
                        <button class="archive-tag-btn <?php echo $is_active ? 'active' : ''; ?>" data-value="<?php echo esc_attr($tag->slug); ?>">
                            <?php echo esc_html($tag->name); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Row 3: Shuffle -->
            <div class="archive-filter-row archive-filter-row--shuffle">
                <button class="archive-btn archive-btn--shuffle" id="shuffle-btn"><?php echo esc_html($archive_shuffle_btn); ?></button>
            </div>
        </div>

        <!-- RECORD GRID -->
        <div class="archive-record-grid" id="archive-grid">
            <?php
            $counter = 0;
            if ($records_query->have_posts()) :
                while ($records_query->have_posts()) : $records_query->the_post();
                    $counter++;
                    $post_id = get_the_ID();

                    // Get record type (category)
                    $categories = get_the_category();
                    $record_type = 'profile';
                    if ($categories) {
                        foreach ($categories as $cat) {
                            if ($cat->slug === 'events') {
                                $record_type = 'event';
                                break;
                            }
                        }
                    }

                    // Get thumbnail
                    $thumbnail = get_the_post_thumbnail_url($post_id, 'large');
                    if (!$thumbnail) {
                        $thumbnail = get_stylesheet_directory_uri() . '/assets/images/placeholder.jpg';
                    }

                    // Get custom fields if available
                    $location = get_post_meta($post_id, 'record_location', true) ?: $archive_default_location;
                    $views = get_post_meta($post_id, 'post_views_count', true) ?: rand(50, 300);

                    // Get excerpt (truncated)
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
                            <span class="card__type card__type--<?php echo esc_attr($record_type); ?>">
                                <?php echo esc_html($record_type); ?>
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
                    // Insert submit card after 4th item
                    if ($counter === 4) :
                    ?>
                        <article class="archive-submit-card">
                            <a href="/submit" class="archive-submit-link">
                                <span class="archive-submit-icon">◇</span>
                                <span class="archive-submit-label"><?php echo esc_html($archive_cta_label); ?></span>
                                <p class="archive-submit-title"><?php echo esc_html($archive_cta_title); ?></p>
                                <p class="archive-submit-text"><?php echo esc_html($archive_cta_text); ?></p>
                            </a>
                        </article>
                    <?php
                    endif;

                    // Insert newsletter card after 8th item for non-logged-in users
                    if ($counter === 8 && !is_user_logged_in()) :
                    ?>
                        <article class="archive-newsletter-card">
                            <?php get_template_part('template-parts/components/newsletter-card', null, [
                                'context'  => 'archive',
                                'variant'  => 'compact',
                                'tags'     => ['archive', 'records'],
                            ]); ?>
                        </article>
                    <?php
                    endif;
                endwhile;
                wp_reset_postdata();
            else :
            ?>
                <div class="archive-no-records">
                    <p><?php echo esc_html($archive_empty_message); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- PAGINATION -->
        <?php
        $current_showing = min($paged * 12, $records_query->found_posts);
        $has_more = $paged < $records_query->max_num_pages;
        ?>
        <div class="archive-load-controls">
            <p class="archive-load-count">
                showing <strong><?php echo esc_html($current_showing); ?></strong> of <strong><?php echo esc_html($records_query->found_posts); ?></strong> records
            </p>
            <?php if ($has_more) :
                $next_page_url = get_pagenum_link($paged + 1);
            ?>
            <button type="button" class="archive-load-more" id="load-more-btn" data-page="<?php echo esc_attr($paged); ?>" data-max="<?php echo esc_attr($records_query->max_num_pages); ?>">
                [load more]
            </button>
            <?php endif; ?>
        </div>

        <?php if ($records_query->max_num_pages > 1) : ?>
        <nav class="archive-pagination">
            <?php
            echo paginate_links(array(
                'total'     => $records_query->max_num_pages,
                'current'   => $paged,
                'prev_text' => esc_html($archive_prev_text),
                'next_text' => esc_html($archive_next_text),
                'type'      => 'list',
            ));
            ?>
        </nav>
        <?php endif; ?>

    </main>
</div>

<!-- SHUFFLE MODAL -->
<div class="archive-shuffle-modal" id="shuffle-modal">
    <div class="archive-shuffle-modal-content">
        <button class="archive-shuffle-close" id="shuffle-close">×</button>
        <div class="archive-shuffle-card">
            <span class="archive-shuffle-file-id" id="shuffle-file-id">REC-2025-001</span>
            <div class="archive-shuffle-image-wrapper">
                <img src="" alt="" class="archive-shuffle-image" id="shuffle-image">
            </div>
            <h3 class="archive-shuffle-title" id="shuffle-title">Loading...</h3>
            <p class="archive-shuffle-excerpt" id="shuffle-excerpt"></p>
            <div class="archive-shuffle-actions">
                <a href="#" class="archive-btn archive-btn--primary" id="shuffle-open"><?php echo esc_html($archive_open_record_btn); ?></a>
                <button class="archive-btn" id="shuffle-again"><?php echo esc_html($archive_shuffle_btn); ?></button>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
