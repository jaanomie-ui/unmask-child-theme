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

    <?php if (is_user_logged_in()) : ?>
    <!-- =====================================================================
         RAIL: ACTIVE ISOS (Members only)
         ===================================================================== -->
    <section class="rail">
        <header class="rail__header">
            <span class="rail__label">Active ISOs</span>
            <a href="<?php echo esc_url(home_url('/iso-board/')); ?>" class="rail__link"><?php echo esc_html($total_isos); ?> listings →</a>
        </header>
        <div class="rail__track">
            <?php if ($iso_query->have_posts()) : ?>
                <?php while ($iso_query->have_posts()) : $iso_query->the_post(); ?>
                    <div class="rail__card rail__card--iso">
                        <?php get_template_part('template-parts/components/iso-card-unified', null, [
                            'post_id'   => get_the_ID(),
                            'context'   => 'rail',
                            'clickable' => false,
                        ]); ?>
                    </div>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p class="rail__empty">No active listings.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- =====================================================================
         WIDGET-H: ACTIVITY FEED (Members only)
         ===================================================================== -->
    <div class="widget-h">
        <span class="widget-h__label">Activity</span>
        <p class="widget-h__placeholder">Activity feed coming soon</p>
    </div>
    <?php endif; ?>

    <?php if (is_user_logged_in()) : ?>
    <!-- =====================================================================
         RAIL: THE FACTORY (Members only)
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
    <?php endif; ?>

    <!-- =====================================================================
         WIDGET-SQ: CTAs (flex row, matches factory card sizing)
         ===================================================================== -->
    <div class="widget-row<?php echo !is_user_logged_in() ? ' widget-row--three-col' : ''; ?>">
        <?php if (!is_user_logged_in()) : ?>
        <!-- Dig Deeper CTA (Strangers only) -->
        <div class="widget-sq widget-sq--cta widget-sq--amber">
            <a href="<?php echo esc_url(home_url('/register/')); ?>" class="widget-sq__link">
                <h3 class="widget-sq__title">dig deeper</h3>
                <p class="widget-sq__desc">log in to see more.</p>
            </a>
        </div>
        <?php endif; ?>
        <div class="widget-sq widget-sq--cta widget-sq--submit">
            <a href="<?php echo esc_url(home_url('/submit/')); ?>" class="widget-sq__link">
                <h3 class="widget-sq__title">Tell Your Story</h3>
                <p class="widget-sq__desc">Submit your record</p>
            </a>
        </div>
        <div class="widget-sq widget-sq--cta widget-sq--panthers">
            <a href="<?php echo esc_url(home_url('/pink-panthers/')); ?>" class="widget-sq__link">
                <h3 class="widget-sq__title">ALL DREAMS COME TRUE AT THE PINK PANTHERS NIGHT CLUB</h3>
                <p class="widget-sq__desc">Pink Panthers</p>
            </a>
        </div>
    </div>

</div>
