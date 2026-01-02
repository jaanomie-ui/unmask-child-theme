<?php
/**
 * UNMASK Records Section
 *
 * 3-column grid of record cards with portrait images.
 * Displayed in the "below fold" area on desktop.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get records (skip the hero post)
$records_query = new WP_Query([
    'posts_per_page' => 3,
    'post_status'    => 'publish',
    'offset'         => 1, // Skip hero post
    'category__not_in' => [get_cat_ID('d001')],
    'meta_query'     => [
        [
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS',
        ],
    ],
]);

// Count total records
$total_count = wp_count_posts()->publish;
?>

<section class="unmask-records">
    <header class="unmask-section-header">
        <span class="unmask-section-header__title">the archive</span>
        <span class="unmask-section-header__meta"><?php echo esc_html($total_count); ?> records</span>
    </header>

    <?php if ($records_query->have_posts()) : ?>
        <div class="unmask-records__grid">
            <?php while ($records_query->have_posts()) : $records_query->the_post(); ?>
                <?php
                $file_id = get_post_meta(get_the_ID(), 'unmask_file_id', true) ?: sprintf('UM-%03d', get_the_ID());
                $record_type = get_post_meta(get_the_ID(), 'unmask_type', true) ?: 'record';
                ?>
                <article class="unmask-record-card">
                    <a href="<?php the_permalink(); ?>" class="unmask-record-card__link">
                        <div class="unmask-record-card__image-wrap">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large', [
                                    'class'   => 'unmask-record-card__image',
                                    'loading' => 'lazy',
                                ]); ?>
                            <?php endif; ?>
                            <div class="unmask-record-card__gradient"></div>
                        </div>

                        <span class="unmask-record-card__file-id"><?php echo esc_html($file_id); ?></span>

                        <div class="unmask-record-card__overlay">
                            <h3 class="unmask-record-card__subject"><?php the_title(); ?></h3>
                            <span class="unmask-record-card__type"><?php echo esc_html($record_type); ?></span>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <p class="unmask-records__empty">No records found.</p>
    <?php endif; ?>
</section>
