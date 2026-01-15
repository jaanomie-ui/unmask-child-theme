<?php
/**
 * UNMASK Mobile Homepage Layout
 *
 * Uses NATIVE CSS scrolling (same pattern as Factory page).
 * No JavaScript library needed - just overflow-x: auto.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// HERO QUERY
// =============================================================================

// Get ACF field values with fallbacks
$hero_label = function_exists('unmask_get_field') ? unmask_get_field('hero_label', 'latest record') : 'latest record';
$hero_cta_label = function_exists('unmask_get_field') ? unmask_get_field('hero_cta_label', 'Open call:') : 'Open call:';
$hero_cta_text = function_exists('unmask_get_field') ? unmask_get_field('hero_cta_text', 'tell your story') : 'tell your story';
$hero_fallback_issue = function_exists('unmask_get_field') ? unmask_get_field('hero_fallback_issue', 'issue 001') : 'issue 001';
$hero_fallback_location = function_exists('unmask_get_field') ? unmask_get_field('hero_fallback_location', 'chicago') : 'chicago';

// Get latest post for hero
$hero_query = new WP_Query([
    'posts_per_page' => 1,
    'post_status'    => 'publish',
    'category__not_in' => [get_cat_ID('d001')],
    'meta_query'     => [
        [
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS',
        ],
    ],
]);

// Fallback data
$hero_data = [
    'image'      => '',
    'file_id'    => 'UM-001',
    'title'      => 'Latest Record',
    'type'       => 'interview',
    'desc'       => 'Documentation over news.',
    'issue'      => $hero_fallback_issue,
    'date'       => 'january 2026',
    'location'   => $hero_fallback_location,
    'permalink'  => '#',
];

if ($hero_query->have_posts()) {
    $hero_query->the_post();
    $hero_data['image']     = get_the_post_thumbnail_url(get_the_ID(), 'large');
    $hero_data['file_id']   = get_post_meta(get_the_ID(), 'unmask_file_id', true) ?: sprintf('UM-%03d', get_the_ID());
    $hero_data['title']     = get_the_title();
    $hero_data['type']      = get_post_meta(get_the_ID(), 'unmask_type', true) ?: 'record';
    $hero_data['desc']      = get_the_excerpt();
    $hero_data['date']      = get_the_date('F Y');
    $hero_data['permalink'] = get_permalink();
    wp_reset_postdata();
}

// =============================================================================
// RAIL QUERIES
// =============================================================================

// Latest Records (skip hero post)
$records_query = new WP_Query([
    'posts_per_page'   => 6,
    'post_status'      => 'publish',
    'offset'           => 1,
    'category__not_in' => [get_cat_ID('d001')],
    'meta_query'       => [
        [
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS',
        ],
    ],
]);

// Active ISOs
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

// Counts
$total_records = wp_count_posts()->publish;
$total_isos = $iso_query->found_posts;
?>

<div class="unmask-mobile">

    <!-- =====================================================================
         HERO SECTION
         ===================================================================== -->
    <section class="unmask-hero unmask-hero--mobile">
        <div class="unmask-hero__grid">

            <!-- Image -->
            <div class="unmask-hero__image-col">
                <?php if (!empty($hero_data['image'])) : ?>
                    <img
                        src="<?php echo esc_url($hero_data['image']); ?>"
                        alt="<?php echo esc_attr($hero_data['title']); ?>"
                        class="unmask-hero__image"
                        loading="eager"
                    >
                <?php else : ?>
                    <div class="unmask-hero__image-placeholder"></div>
                <?php endif; ?>
            </div>

            <!-- Content -->
            <div class="unmask-hero__content-area">
                <div class="unmask-hero__content-box">
                    <div class="unmask-hero__label"><?php echo esc_html($hero_label); ?></div>
                    <div class="unmask-hero__file-id"><?php echo esc_html($hero_data['file_id']); ?></div>

                    <h1 class="unmask-hero__title">
                        <a href="<?php echo esc_url($hero_data['permalink']); ?>" class="unmask-hero__title-link">
                            <?php echo esc_html($hero_data['title']); ?>
                        </a>
                    </h1>

                    <div class="unmask-hero__type"><?php echo esc_html($hero_data['type']); ?></div>

                    <?php if (!empty($hero_data['desc'])) : ?>
                        <p class="unmask-hero__desc"><?php echo esc_html($hero_data['desc']); ?></p>
                    <?php endif; ?>

                    <div class="unmask-hero__meta">
                        <span><?php echo esc_html($hero_data['issue']); ?></span>
                        <span><?php echo esc_html(strtolower($hero_data['date'])); ?></span>
                        <span><?php echo esc_html($hero_data['location']); ?></span>
                    </div>

                    <!-- Submit CTA -->
                    <a href="<?php echo esc_url(home_url('/submit/')); ?>" class="unmask-hero__cta">
                        <span class="unmask-hero__cta-label"><?php echo esc_html($hero_cta_label); ?></span>
                        <span class="unmask-hero__cta-text"><?php echo esc_html($hero_cta_text); ?></span>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- =====================================================================
         BELOW FOLD CONTENT
         ===================================================================== -->
    <div class="homepage-grid">

        <!-- RAIL: LATEST RECORDS -->
        <section class="homepage-rail" aria-label="Latest Records">
            <div class="homepage-rail__header">
                <span class="homepage-rail__title">Latest Records</span>
                <a href="<?php echo esc_url(home_url('/records/')); ?>" class="homepage-rail__link"><?php echo esc_html($total_records); ?> →</a>
            </div>
            <div class="homepage-rail__track">
                <?php if ($records_query->have_posts()) : ?>
                    <?php while ($records_query->have_posts()) : $records_query->the_post(); ?>
                        <article class="homepage-rail__card homepage-rail__card--record">
                            <a href="<?php the_permalink(); ?>" class="homepage-rail__card-link">
                                <div class="homepage-rail__card-image">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('large', ['loading' => 'lazy']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="homepage-rail__card-overlay">
                                    <h3 class="homepage-rail__card-title"><?php the_title(); ?></h3>
                                </div>
                            </a>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <p class="homepage-rail__empty">No records found.</p>
                <?php endif; ?>
            </div>
        </section>

        <?php if (is_user_logged_in()) : ?>
        <!-- RAIL: ACTIVE ISOS (Members only) -->
        <section class="homepage-rail" aria-label="Active ISOs">
            <div class="homepage-rail__header">
                <span class="homepage-rail__title">Active ISOs</span>
                <a href="<?php echo esc_url(home_url('/iso-board/')); ?>" class="homepage-rail__link"><?php echo esc_html($total_isos); ?> →</a>
            </div>
            <div class="homepage-rail__track">
                <?php if ($iso_query->have_posts()) : ?>
                    <?php while ($iso_query->have_posts()) : $iso_query->the_post(); ?>
                        <div class="homepage-rail__card homepage-rail__card--iso">
                            <?php get_template_part('template-parts/components/iso-card-unified', null, [
                                'post_id'   => get_the_ID(),
                                'context'   => 'rail',
                                'clickable' => false,
                            ]); ?>
                        </div>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <p class="homepage-rail__empty">No active listings.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- WIDGET: ACTIVITY (Members only) -->
        <section class="homepage-rail" aria-label="Activity">
            <div class="homepage-rail__header">
                <span class="homepage-rail__title">Activity</span>
            </div>
            <div class="homepage-rail__placeholder">Activity feed coming soon</div>
        </section>

        <!-- RAIL: THE FACTORY (Members only) -->
        <section class="homepage-rail" aria-label="The Factory">
            <div class="homepage-rail__header">
                <span class="homepage-rail__title">The Factory</span>
                <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="homepage-rail__link">book →</a>
            </div>
            <div class="homepage-rail__track">
                <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="homepage-rail__card homepage-rail__card--factory homepage-rail__card--photo-studio">
                    <h3 class="homepage-rail__factory-title">photo studio</h3>
                    <p class="homepage-rail__factory-desc">Professional lighting, backdrops, and equipment for portrait and editorial shoots.</p>
                </a>
                <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="homepage-rail__card homepage-rail__card--factory homepage-rail__card--interview">
                    <h3 class="homepage-rail__factory-title">interview room</h3>
                    <p class="homepage-rail__factory-desc">Soundproofed space for audio recording, podcasts, and video interviews.</p>
                </a>
                <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="homepage-rail__card homepage-rail__card--factory homepage-rail__card--meeting">
                    <h3 class="homepage-rail__factory-title">meeting space</h3>
                    <p class="homepage-rail__factory-desc">Flexible event space for workshops, screenings, and community gatherings.</p>
                </a>
                <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="homepage-rail__card homepage-rail__card--factory homepage-rail__card--editing">
                    <h3 class="homepage-rail__factory-title">editing suite</h3>
                    <p class="homepage-rail__factory-desc">Workstations with professional software for video and audio post-production.</p>
                </a>
            </div>
        </section>
        <?php endif; ?>

        <!-- CTA CARDS -->
        <section class="homepage-rail homepage-rail--cta" aria-label="Get Involved">
            <div class="homepage-rail__track">
                <?php if (!is_user_logged_in()) : ?>
                <!-- Dig Deeper CTA (Strangers only) -->
                <a href="<?php echo esc_url(home_url('/register/')); ?>" class="homepage-rail__card homepage-rail__card--cta homepage-rail__card--amber">
                    <div class="homepage-rail__cta-content">
                        <h3 class="homepage-rail__cta-title">dig deeper</h3>
                        <p class="homepage-rail__cta-desc">log in to see more.</p>
                    </div>
                </a>
                <?php endif; ?>
                <a href="<?php echo esc_url(home_url('/submit/')); ?>" class="homepage-rail__card homepage-rail__card--cta" style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/cta-submit.png');">
                    <div class="homepage-rail__cta-overlay">
                        <h3 class="homepage-rail__cta-title">Tell Your Story</h3>
                        <p class="homepage-rail__cta-desc">Submit your record</p>
                    </div>
                </a>
                <a href="<?php echo esc_url(home_url('/pink-panthers/')); ?>" class="homepage-rail__card homepage-rail__card--cta" style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/cta-panthers.png');">
                    <div class="homepage-rail__cta-overlay">
                        <h3 class="homepage-rail__cta-title">ALL DREAMS COME TRUE AT THE PINK PANTHERS NIGHT CLUB</h3>
                        <p class="homepage-rail__cta-desc">Pink Panthers</p>
                    </div>
                </a>
            </div>
        </section>

    </div>

    <!-- Footer -->
    <footer class="unmask-footer unmask-footer--mobile">
        <div class="unmask-footer__left">
            unmask keeps the record · chicago, illinois · est. 2024
        </div>
        <div class="unmask-footer__right">
            <a href="<?php echo esc_url(home_url('/about/')); ?>">about</a> ·
            <a href="<?php echo esc_url(home_url('/privacy/')); ?>">privacy</a> ·
            <a href="<?php echo esc_url(home_url('/terms/')); ?>">terms</a>
        </div>
    </footer>

</div>

<style>
/* ==========================================================================
   HOMEPAGE RAILS - Native CSS Scroll (same pattern as Factory page)
   No JavaScript needed. Just overflow-x: auto.
   ========================================================================== */

/* ==========================================================================
   BUDDYBOSS WRAPPER OVERRIDES
   BuddyBoss theme has wrapper elements that can interfere with scrolling.
   These overrides match what works on the Factory page.
   ========================================================================== */

/* Allow scroll within the homepage container */
.page-template-page-templatestemplate-homepage-php .bb-grid.site-content-grid,
.unmask-homepage-mobile,
.unmask-mobile {
    overflow: visible !important;
}

/* Reset touch-action for all elements in the mobile layout */
.unmask-mobile,
.unmask-mobile *,
.homepage-grid,
.homepage-grid * {
    touch-action: auto;
}

/* Rail Container */
.homepage-rail {
    margin-bottom: 0;
    border-bottom: 1px solid var(--border-default);
    /* Override any inherited touch-action restrictions */
    touch-action: auto !important;
}

/* Rail Header */
.homepage-rail__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border-default);
    background: var(--bg-card);
}

.homepage-rail__title {
    font-family: var(--font-ui);
    font-size: var(--type-xs);
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--text-muted);
}

.homepage-rail__link {
    font-family: var(--font-ui);
    font-size: var(--type-xs);
    color: var(--text-muted);
    text-decoration: none;
}

.homepage-rail__link:hover {
    color: var(--text-primary);
}

/* ==========================================================================
   THE TRACK - Native CSS scroll with explicit touch-action override
   BuddyBoss theme.css has .slick-slider { touch-action: pan-y } which blocks
   horizontal swipe. We override with pan-x pan-y to allow horizontal scroll.
   ========================================================================== */
.homepage-rail__track {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    padding: 16px;
    scrollbar-width: none;
    /* CRITICAL: Allow horizontal AND vertical panning */
    touch-action: pan-x pan-y !important;
    -ms-touch-action: pan-x pan-y !important;
    /* Prevent scroll chaining to parent */
    overscroll-behavior-x: contain;
    -ms-scroll-chaining: none;
}

.homepage-rail__track::-webkit-scrollbar {
    display: none;
}

/* Override any touch-action on all descendants */
.homepage-rail__track,
.homepage-rail__track * {
    touch-action: pan-x pan-y !important;
}

/* ==========================================================================
   CARDS
   ========================================================================== */

/* Base Card */
.homepage-rail__card {
    flex: 0 0 auto;
    scroll-snap-align: start;
    /* Allow horizontal swipe on cards */
    touch-action: pan-x pan-y !important;
}

/* Record Card */
.homepage-rail__card--record {
    width: 260px;
    height: 350px;
    position: relative;
    overflow: hidden;
    background: var(--bg-card);
    border: 1px solid var(--border-default);
}

.homepage-rail__card-link {
    display: block;
    width: 100%;
    height: 100%;
    text-decoration: none;
    color: inherit;
}

.homepage-rail__card-image {
    width: 100%;
    height: 100%;
    position: relative;
}

.homepage-rail__card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(20%) contrast(1.05);
    transition: filter 0.3s ease;
}

.homepage-rail__card--record:hover .homepage-rail__card-image img {
    filter: grayscale(0%) contrast(1);
}

.homepage-rail__card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    background: linear-gradient(transparent, rgba(24, 24, 24, 0.9));
}

.homepage-rail__card-title {
    font-family: var(--font-ui);
    font-size: var(--type-base);
    font-weight: 400;
    color: var(--text-primary);
    margin: 0;
    line-height: 1.3;
}

/* ISO Card Wrapper - contains unified iso-card component */
.homepage-rail__card--iso {
    width: 280px;
    min-height: 200px;
}

/* Unified component fills wrapper */
.homepage-rail__card--iso .iso-card {
    width: 100%;
    height: 100%;
}

/* ISO Card internal spacing adjustments */
.homepage-rail__card--iso .iso-card__title {
    margin-top: 40px !important; /* Extra space after tags row */
}

.homepage-rail__card--iso .iso-card__who {
    padding-top: 40px !important; /* Increased from 12px */
}

/* Factory Card */
.homepage-rail__card--factory {
    width: 240px;
    aspect-ratio: 3 / 4;
    padding: 16px;
    background: var(--bg-card);
    border: 1px solid var(--border-default);
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}


.homepage-rail__factory-title {
    font-family: var(--font-ui);
    font-size: var(--type-base);
    font-weight: 400;
    color: var(--text-primary);
    margin: 0 0 6px 0;
    text-transform: lowercase;
}

.homepage-rail__factory-desc {
    font-family: var(--font-ui);
    font-size: var(--type-xs);
    color: var(--text-muted);
    margin: 0;
    line-height: 1.4;
}

/* CTA Cards */
.homepage-rail__card--cta {
    width: 220px;
    height: 280px;
    position: relative;
    overflow: hidden;
    background-size: cover;
    background-position: center;
    border: 1px solid var(--border-default);
}

.homepage-rail__cta-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 40%, transparent 100%);
}

.homepage-rail__cta-title {
    font-family: var(--font-ui);
    font-size: var(--type-base);
    font-weight: 400;
    color: var(--text-primary);
    margin: 0;
}

.homepage-rail__cta-desc {
    font-family: var(--font-ui);
    font-size: var(--type-xs);
    color: var(--text-muted);
    margin: 4px 0 0 0;
}

/* Amber CTA Card - Dig Deeper (Strangers only) */
.homepage-rail__card--amber {
    background: var(--primitive-amber-glow) !important;
    border-color: var(--primitive-amber) !important;
}

.homepage-rail__card--amber .homepage-rail__cta-content {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;
    text-align: left;
    height: 100%;
    padding: 16px;
}

.homepage-rail__card--amber .homepage-rail__cta-title {
    font-family: var(--font-ui);
    font-size: var(--type-base);
    color: var(--text-primary);
    text-transform: lowercase;
    letter-spacing: 0.02em;
}

.homepage-rail__card--amber .homepage-rail__cta-desc {
    font-family: var(--font-ui);
    font-size: var(--type-xs);
    color: var(--text-muted);
    margin-top: 4px;
}

.homepage-rail__card--amber:hover {
    border-color: var(--primitive-amber-light) !important;
    background: rgba(212, 160, 25, 0.2) !important;
}

/* Placeholder */
.homepage-rail__placeholder {
    padding: 32px 16px;
    font-family: var(--font-ui);
    font-size: var(--type-sm);
    color: var(--text-muted);
    text-align: center;
}

.homepage-rail__empty {
    padding: 16px;
    font-family: var(--font-ui);
    font-size: var(--type-sm);
    color: var(--text-muted);
}

/* ==========================================================================
   GLOBAL TOUCH-ACTION RESET
   BuddyBoss theme has .slick-slider { touch-action: pan-y } which blocks
   horizontal swipe. Override at the BODY level for the entire mobile layout.
   ========================================================================== */
.page-template-page-templatestemplate-homepage-php .unmask-mobile,
.page-template-page-templatestemplate-homepage-php .unmask-mobile *,
.unmask-homepage-mobile,
.unmask-homepage-mobile * {
    touch-action: auto !important;
    -ms-touch-action: auto !important;
}

/* Specific override for the scrollable track containers */
.homepage-rail__track {
    touch-action: pan-x pan-y !important;
    -webkit-overflow-scrolling: touch !important;
    overflow-x: scroll !important;
    overflow-y: hidden !important;
    pointer-events: auto !important;
    /* Force GPU layer to reset touch handling */
    transform: translate3d(0, 0, 0);
    -webkit-transform: translate3d(0, 0, 0);
    will-change: scroll-position;
    /* Create new stacking context */
    position: relative;
    z-index: 1;
    /* Isolation */
    isolation: isolate;
}

/* Ensure parent containers don't block scroll */
.homepage-rail {
    overflow: visible !important;
    position: relative;
    /* Constrain width to viewport to force track overflow */
    max-width: 100vw;
    width: 100%;
}

.homepage-grid,
.unmask-mobile,
.unmask-homepage-mobile {
    overflow: visible !important;
    /* Prevent horizontal page scroll */
    max-width: 100vw;
    width: 100%;
}

/* Force cards to not shrink and create overflow */
.homepage-rail__card {
    flex: 0 0 auto !important;
    min-width: 240px;
    touch-action: pan-x pan-y !important;
    pointer-events: auto !important;
}

/* Links inside cards should not block touch */
.homepage-rail__card a,
.homepage-rail__card-link {
    touch-action: pan-x pan-y !important;
    pointer-events: auto !important;
}
</style>

<script>
/**
 * Homepage Rails - Native Scroll Fix
 *
 * Ensures horizontal swipe works by:
 * 1. Removing any touch event listeners that might block scrolling
 * 2. Re-enabling native scroll behavior
 * 3. Adding passive touch listeners that don't interfere
 */
(function() {
    'use strict';

    function fixTracks() {
        var tracks = document.querySelectorAll('.homepage-rail__track');

        tracks.forEach(function(track) {
            // Skip if already fixed
            if (track.dataset.scrollFixed) return;

            // Clone to remove all event listeners
            var parent = track.parentNode;
            var clone = track.cloneNode(true);

            // Force scroll styles
            clone.style.cssText = [
                'display: flex',
                'gap: 12px',
                'overflow-x: scroll',
                'overflow-y: hidden',
                '-webkit-overflow-scrolling: touch',
                'touch-action: pan-x pan-y',
                'scroll-snap-type: x mandatory',
                'scrollbar-width: none',
                'pointer-events: auto',
                'transform: translate3d(0,0,0)',
                'position: relative',
                'z-index: 1'
            ].join(' !important; ') + ' !important';

            // Mark as fixed
            clone.dataset.scrollFixed = 'true';

            // Add passive touch listeners that don't block scrolling
            clone.addEventListener('touchstart', function(e) {
                // Don't prevent default - allow native scroll
            }, { passive: true });

            clone.addEventListener('touchmove', function(e) {
                // Don't prevent default - allow native scroll
            }, { passive: true });

            // Replace
            parent.replaceChild(clone, track);

            // Debug: Log that we fixed this track
            console.log('[UNMASK] Fixed track:', clone.parentElement.getAttribute('aria-label'));
        });
    }

    // Also fix any ancestor elements that might be blocking scroll
    function fixAncestors() {
        var ancestors = document.querySelectorAll('.homepage-grid, .unmask-mobile, .unmask-homepage-mobile, .bb-grid');
        ancestors.forEach(function(el) {
            el.style.overflow = 'visible';
            el.style.touchAction = 'auto';
        });
    }

    // Run on DOM ready and after potential late-loading scripts
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            fixTracks();
            fixAncestors();
        });
    } else {
        fixTracks();
        fixAncestors();
    }

    window.addEventListener('load', function() {
        fixTracks();
        fixAncestors();
        setTimeout(function() { fixTracks(); fixAncestors(); }, 100);
        setTimeout(function() { fixTracks(); fixAncestors(); }, 500);
        setTimeout(function() { fixTracks(); fixAncestors(); }, 1000);
    });
})();
</script>
