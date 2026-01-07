<?php
/**
 * UNMASK Below Fold Grid
 *
 * Netflix/Spotify rail pattern with inline widgets.
 * Uses homepage-grid CSS system with unmask-cards components.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// QUERIES
// =============================================================================

// Latest Records: 6 posts, exclude d001, must have thumbnail
$records_query = new WP_Query([
    'posts_per_page'   => 6,
    'post_status'      => 'publish',
    'offset'           => 1, // Skip hero post
    'category__not_in' => [get_cat_ID('d001')],
    'meta_query'       => [
        [
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS',
        ],
    ],
]);

// Active ISOs: non-expired listings
$iso_query = new WP_Query([
    'post_type'      => 'iso',
    'posts_per_page' => 6,
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'iso_expiration',
            'value'   => date('Ymd'),
            'compare' => '>=',
            'type'    => 'DATE'
        ]
    ],
    'orderby' => 'date',
    'order'   => 'DESC'
]);

// Counts for headers
$total_records = wp_count_posts()->publish;
$total_isos = $iso_query->found_posts;
?>

<div class="homepage-grid">

    <!-- =====================================================================
         RAIL: LATEST RECORDS
         ===================================================================== -->
    <section class="rail">
        <header class="rail__header">
            <span class="rail__label">Latest Records</span>
            <a href="<?php echo esc_url(home_url('/records/')); ?>" class="rail__link"><?php echo esc_html($total_records); ?> records →</a>
        </header>
        <div class="rail__track">
            <?php if ($records_query->have_posts()) : ?>
                <?php while ($records_query->have_posts()) : $records_query->the_post(); ?>
                    <article class="card card--record card--fullbleed">
                        <a href="<?php the_permalink(); ?>" class="card__link">
                            <div class="card__image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('large', ['loading' => 'lazy']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="card__overlay">
                                <h3 class="card__title"><?php the_title(); ?></h3>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p class="rail__empty">No records found.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- =====================================================================
         RAIL: ACTIVE ISOS
         ===================================================================== -->
    <section class="rail">
        <header class="rail__header">
            <span class="rail__label">Active ISOs</span>
            <a href="<?php echo esc_url(home_url('/iso-board/')); ?>" class="rail__link"><?php echo esc_html($total_isos); ?> listings →</a>
        </header>
        <div class="rail__track">
            <?php if ($iso_query->have_posts()) : ?>
                <?php while ($iso_query->have_posts()) : $iso_query->the_post(); ?>
                    <?php
                    // Get ISO fields
                    $iso_type = get_field('iso_type') ?: 'seeking';
                    $iso_category = get_field('iso_category') ?: '';
                    $iso_factory = get_field('iso_factory');
                    $iso_expiration = get_field('iso_expiration');

                    // Author info
                    $author_id = get_the_author_meta('ID');
                    $designation = function_exists('unmask_get_user_designation')
                        ? unmask_get_user_designation($author_id)
                        : 'V-' . str_pad($author_id, 3, '0', STR_PAD_LEFT);

                    // Avatar
                    $avatar_url = '';
                    if (function_exists('bp_core_fetch_avatar')) {
                        $avatar_url = bp_core_fetch_avatar([
                            'item_id' => $author_id,
                            'type'    => 'thumb',
                            'html'    => false
                        ]);
                    } else {
                        $avatar_url = get_avatar_url($author_id, ['size' => 96]);
                    }

                    // Days left
                    $days_left = 0;
                    if ($iso_expiration) {
                        $exp_date = DateTime::createFromFormat('Ymd', $iso_expiration);
                        if ($exp_date) {
                            $diff = (new DateTime())->diff($exp_date);
                            $days_left = $diff->invert ? 0 : $diff->days;
                        }
                    }

                    // Factory preferred
                    $is_factory = in_array($iso_factory, ['yes', 'preferred']);

                    // Card classes
                    $card_class = 'card card--iso';
                    if ($is_factory) {
                        $card_class .= ' card--preferred';
                    }
                    ?>
                    <article class="<?php echo esc_attr($card_class); ?>">
                        <div class="card__header">
                            <div class="card__avatar">
                                <?php if ($avatar_url) : ?>
                                    <img src="<?php echo esc_url($avatar_url); ?>" alt="">
                                <?php endif; ?>
                            </div>
                            <div class="card__author-info">
                                <div class="card__author-name"><?php echo esc_html($designation); ?></div>
                            </div>
                        </div>
                        <div class="card__body">
                            <div class="card__badges">
                                <span class="badge-intent badge-intent--<?php echo esc_attr($iso_type); ?>"><?php echo esc_html($iso_type); ?></span>
                                <?php if ($iso_category) : ?>
                                    <span class="badge-category"><?php echo esc_html($iso_category); ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="card__title"><?php the_title(); ?></h3>
                            <p class="card__text card__text--truncate"><?php echo esc_html(get_the_excerpt()); ?></p>
                        </div>
                        <div class="card__footer">
                            <span class="card__meta-item"><?php echo esc_html($days_left); ?> days left</span>
                            <?php if ($is_factory) : ?>
                                <span class="card__meta-item">@ factory</span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p class="rail__empty">No active listings.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- =====================================================================
         WIDGET-H: ACTIVITY FEED (PLACEHOLDER)
         ===================================================================== -->
    <div class="widget-h">
        <span class="widget-h__label">Activity</span>
        <p class="widget-h__placeholder">Activity feed coming soon</p>
    </div>

    <!-- =====================================================================
         RAIL: THE FACTORY
         ===================================================================== -->
    <section class="rail">
        <header class="rail__header">
            <span class="rail__label">The Factory</span>
            <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="rail__link">book a session →</a>
        </header>
        <div class="rail__track">
            <!-- Factory image cards with gradient placeholders -->
            <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="factory-card factory-card--photo-studio">
                <h3 class="factory-card__title">photo studio</h3>
                <p class="factory-card__desc">Professional lighting, backdrops, and equipment for portrait and editorial shoots.</p>
            </a>
            <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="factory-card factory-card--interview">
                <h3 class="factory-card__title">interview room</h3>
                <p class="factory-card__desc">Soundproofed space for audio recording, podcasts, and video interviews.</p>
            </a>
            <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="factory-card factory-card--meeting">
                <h3 class="factory-card__title">meeting space</h3>
                <p class="factory-card__desc">Flexible event space for workshops, screenings, and community gatherings.</p>
            </a>
            <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="factory-card factory-card--editing">
                <h3 class="factory-card__title">editing suite</h3>
                <p class="factory-card__desc">Workstations with professional software for video and audio post-production.</p>
            </a>
        </div>
    </section>

    <!-- =====================================================================
         WIDGET-SQ: PLACEHOLDERS
         ===================================================================== -->
    <div class="widget-sq">
        <span class="widget-sq__label">Widget</span>
        <div class="widget-sq__placeholder">Coming soon</div>
    </div>
    <div class="widget-sq">
        <span class="widget-sq__label">Widget</span>
        <div class="widget-sq__placeholder">Coming soon</div>
    </div>

    <!-- =====================================================================
         SECTION-STATIC: THE WEB (PLACEHOLDER)
         ===================================================================== -->
    <section class="section-static">
        <span class="section-static__label">The Web</span>
        <div class="section-static__placeholder">
            Network visualization coming soon
        </div>
    </section>

</div>
