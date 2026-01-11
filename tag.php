<?php
/**
 * Tag Archive Template
 *
 * Minimal tag archive page. Same structure as magazine archive.
 *
 * @package unmask-child-theme
 */

get_header();

$tag = get_queried_object();
$tag_name = $tag->name;
$tag_count = $tag->count;

$paged = get_query_var('paged') ? get_query_var('paged') : 1;
?>

<div class="archive-container tag-archive">
    <main class="archive-main">

        <!-- HEADER -->
        <header class="archive-header tag-archive__header">
            <div class="tag-archive__nav">
                <a href="<?php echo esc_url(home_url('/the-archive/')); ?>" class="tag-archive__back">← back to archive</a>
            </div>
            <h1 class="archive-title"><?php echo esc_html($tag_name); ?></h1>
            <div class="archive-header-stats">
                <span><?php echo esc_html($tag_count); ?> record<?php echo $tag_count !== 1 ? 's' : ''; ?></span>
            </div>
        </header>

        <!-- RECORD GRID -->
        <div class="archive-record-grid" id="archive-grid">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
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

                <?php endwhile;
            else : ?>
                <div class="archive-no-records">
                    <p>No records found with this tag.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- PAGINATION -->
        <?php
        global $wp_query;
        $total_posts = $wp_query->found_posts;
        $current_showing = min($paged * get_option('posts_per_page'), $total_posts);
        ?>
        <div class="archive-load-controls">
            <p class="archive-load-count">
                showing <strong><?php echo esc_html($current_showing); ?></strong> of <strong><?php echo esc_html($total_posts); ?></strong> records
            </p>
        </div>

        <?php if ($wp_query->max_num_pages > 1) : ?>
        <nav class="archive-pagination">
            <?php
            echo paginate_links(array(
                'total'     => $wp_query->max_num_pages,
                'current'   => $paged,
                'prev_text' => '← previous',
                'next_text' => 'next →',
                'type'      => 'list',
            ));
            ?>
        </nav>
        <?php endif; ?>

    </main>
</div>

<?php get_footer(); ?>
