<?php
/**
 * Single Record Template
 *
 * 3-column Swiss grid layout for individual records.
 * Uses design system tokens and existing shortcodes.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    // Get post data
    $post_id = get_the_ID();
    $title = get_the_title();
    $date = get_the_date('Y.m.d');
    $word_count = str_word_count(strip_tags(get_the_content()));
    $featured_image = get_the_post_thumbnail_url($post_id, 'full');

    // Get custom meta
    $file_id = get_post_meta($post_id, 'unmask_file_id', true) ?: sprintf('REC-%s-%03d', date('Y'), $post_id);
    $subject = get_post_meta($post_id, 'unmask_subject', true) ?: '';
    $location = get_post_meta($post_id, 'unmask_location', true) ?: 'Chicago';
    $photographer = get_post_meta($post_id, 'unmask_photographer', true) ?: '';
    $writer = get_post_meta($post_id, 'unmask_writer', true) ?: '';
    $issue = get_post_meta($post_id, 'unmask_issue', true) ?: '';

    // Get gallery images for counter (uses dynamic extraction from content)
    $gallery_images = unmask_get_gallery_images('', $post_id);
    $image_count = count($gallery_images);
?>

<div class="unmask-single-record">

    <!-- ═══════════════════════════════════════════════════════════════
         HERO SECTION - Viewport Height
    ═══════════════════════════════════════════════════════════════ -->
    <section class="record-hero">

        <!-- System Bar -->
        <div class="record-system-bar">
            <a href="<?php echo esc_url(home_url('/archive/')); ?>" class="record-system-bar__back">← back to archive</a>
            <span class="record-system-bar__center">UNMASK / the archive</span>
            <span class="record-system-bar__id"><?php echo esc_html($file_id); ?></span>
        </div>

        <!-- 3-Column Grid -->
        <div class="record-hero__grid">

            <!-- Column 1: Gallery Lead Image -->
            <div class="record-hero__image-col">
                <?php echo do_shortcode('[unmask_gallery_lead height="100%"]'); ?>
            </div>

            <!-- Columns 2-3: Content Area (3x3 sub-grid) -->
            <div class="record-hero__content-area">

                <!-- Row 1: Status + Label (col 2 only) -->
                <div class="record-hero__row-1">
                    <div class="record-hero__status">
                        <span class="record-hero__status-dot"></span>
                        <span>archived / public</span>
                    </div>
                    <span class="record-hero__label">interview:</span>
                </div>

                <!-- Row 2: Title (spans both columns) -->
                <div class="record-hero__row-2">
                    <h1 class="record-hero__title"><?php echo esc_html($title); ?></h1>
                </div>

                <!-- Row 3: Tags (left) + Metadata (right) -->
                <div class="record-hero__row-3">
                    <div class="record-hero__tags">
                        <?php echo do_shortcode('[unmask_tags]'); ?>
                    </div>

                    <div class="record-hero__meta-col">
                        <div class="record-hero__meta-grid">
                            <div class="record-hero__meta-item">
                                <div class="record-hero__meta-label">location</div>
                                <div class="record-hero__meta-value record-hero__meta-value--sm"><?php echo esc_html($location); ?></div>
                            </div>

                            <div class="record-hero__meta-item">
                                <div class="record-hero__meta-label">filed</div>
                                <div class="record-hero__meta-value record-hero__meta-value--sm"><?php echo esc_html($date); ?></div>
                            </div>

                            <div class="record-hero__meta-item">
                                <div class="record-hero__meta-label">words</div>
                                <div class="record-hero__meta-value record-hero__meta-value--sm"><?php echo number_format($word_count); ?></div>
                            </div>

                            <?php if ($photographer) : ?>
                            <div class="record-hero__meta-item">
                                <div class="record-hero__meta-label">photographer</div>
                                <div class="record-hero__meta-value record-hero__meta-value--sm"><?php echo esc_html($photographer); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if ($writer) : ?>
                            <div class="record-hero__meta-item">
                                <div class="record-hero__meta-label">writer</div>
                                <div class="record-hero__meta-value record-hero__meta-value--sm"><?php echo esc_html($writer); ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if ($issue) : ?>
                            <div class="record-hero__meta-item">
                                <div class="record-hero__meta-label">issue</div>
                                <div class="record-hero__meta-value record-hero__meta-value--sm"><?php echo esc_html($issue); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Gallery Strip -->
        <div class="record-gallery-strip-wrapper">
            <?php echo do_shortcode('[unmask_gallery_strip]'); ?>
            <div class="record-gallery-strip__hint">
                click thumbnail to view • <?php echo esc_html($image_count); ?> images in this record
            </div>
        </div>

    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         ARTICLE BODY - Tiempos / 65ch Optimal Width
    ═══════════════════════════════════════════════════════════════ -->
    <article class="record-body">
        <div class="record-body__container">
            <div class="record-body__content">
                <?php the_content(); ?>
            </div>
        </div>
    </article>

    <!-- ═══════════════════════════════════════════════════════════════
         POST NAVIGATION - Prev/Next Loop (excludes d001 Training Logs)
    ═══════════════════════════════════════════════════════════════ -->
    <?php
    // Exclude category 112 (d001-training-logs) from navigation
    $excluded_cat = 112;

    // Get adjacent posts
    $prev_post = get_previous_post(false, $excluded_cat);
    $next_post = get_next_post(false, $excluded_cat);

    // If no previous, loop to newest post
    if (!$prev_post) {
        $newest = get_posts([
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'category__not_in' => [$excluded_cat],
            'exclude' => [$post_id],
        ]);
        if ($newest) $prev_post = $newest[0];
    }

    // If no next, loop to oldest post
    if (!$next_post) {
        $oldest = get_posts([
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'ASC',
            'category__not_in' => [$excluded_cat],
            'exclude' => [$post_id],
        ]);
        if ($oldest) $next_post = $oldest[0];
    }
    ?>
    <nav class="record-nav">
        <?php if ($prev_post) : ?>
            <a href="<?php echo get_permalink($prev_post); ?>" class="record-nav__link record-nav__link--prev">
                <span class="record-nav__label">previous record</span>
                <span class="record-nav__title"><?php echo get_the_title($prev_post); ?></span>
            </a>
        <?php endif; ?>

        <?php if ($next_post) : ?>
            <a href="<?php echo get_permalink($next_post); ?>" class="record-nav__link record-nav__link--next">
                <span class="record-nav__label">next record</span>
                <span class="record-nav__title"><?php echo get_the_title($next_post); ?></span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- ═══════════════════════════════════════════════════════════════
         COMMENTS SECTION - WordPress Native
    ═══════════════════════════════════════════════════════════════ -->
    <?php if (comments_open() || get_comments_number()) : ?>
    <section class="record-comments">
        <div class="record-comments__container">
            <header class="record-comments__header">
                <span class="record-comments__label">discussion</span>
                <span class="record-comments__count"><?php echo get_comments_number(); ?> responses</span>
            </header>
            <?php comments_template(); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         RELATED RECORDS - By Tag, Fallback to Category
    ═══════════════════════════════════════════════════════════════ -->
    <?php
    // Get current post's tags
    $current_tags = wp_get_post_tags($post_id, ['fields' => 'ids']);
    $current_categories = wp_get_post_categories($post_id);

    // Try to find related by tags first
    $related_args = [
        'post__not_in'   => [$post_id],
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'meta_query'     => [
            [
                'key'     => '_thumbnail_id',
                'compare' => 'EXISTS',
            ],
        ],
    ];

    if (!empty($current_tags)) {
        $related_args['tag__in'] = $current_tags;
    } elseif (!empty($current_categories)) {
        $related_args['category__in'] = $current_categories;
    }

    $related_query = new WP_Query($related_args);

    if ($related_query->have_posts()) :
    ?>
    <section class="record-related">
        <header class="record-related__header">
            <span class="record-related__label">related records</span>
            <span class="record-related__count"><?php echo $related_query->found_posts; ?> in archive</span>
        </header>

        <div class="record-related__grid">
            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                <?php
                $related_file_id = get_post_meta(get_the_ID(), 'unmask_file_id', true) ?: sprintf('UM-%03d', get_the_ID());
                $related_type = get_post_meta(get_the_ID(), 'unmask_type', true) ?: 'record';
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

                        <span class="unmask-record-card__file-id"><?php echo esc_html($related_file_id); ?></span>

                        <div class="unmask-record-card__overlay">
                            <h3 class="unmask-record-card__subject"><?php the_title(); ?></h3>
                            <span class="unmask-record-card__type"><?php echo esc_html($related_type); ?></span>
                            <p class="unmask-record-card__excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        <?php wp_reset_postdata(); ?>
    </section>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════
         STICKY SUBMIT CTA (Mobile)
    ═══════════════════════════════════════════════════════════════ -->
    <div class="record-submit-cta" id="record-submit-cta">
        <div class="record-submit-cta__text">
            <span class="record-submit-cta__label">open call</span>
            <span class="record-submit-cta__title">tell your story</span>
        </div>
        <div class="record-submit-cta__actions">
            <button type="button" class="record-submit-cta__btn record-submit-cta__btn--share" id="share-btn" aria-label="Share this record">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13"/>
                </svg>
            </button>
            <a href="<?php echo esc_url(home_url('/submit/')); ?>" class="record-submit-cta__btn record-submit-cta__btn--primary">submit</a>
        </div>
    </div>

    <!-- Share Toast -->
    <div class="record-share-toast" id="share-toast"></div>

</div>

<!-- Submit CTA & Share JavaScript -->
<script>
(function() {
    'use strict';

    var cta = document.getElementById('record-submit-cta');
    var shareBtn = document.getElementById('share-btn');
    var shareToast = document.getElementById('share-toast');
    var recordBody = document.querySelector('.record-body');
    var recordComments = document.querySelector('.record-comments');
    var recordRelated = document.querySelector('.record-related');
    var isVisible = false;

    // Only on mobile
    function isMobile() {
        return window.innerWidth <= 768;
    }

    // Show CTA after scrolling past hero
    function checkScroll() {
        if (!isMobile() || !cta) return;

        var scrollY = window.scrollY || window.pageYOffset;
        var heroHeight = window.innerHeight;
        var documentHeight = document.documentElement.scrollHeight;
        var viewportHeight = window.innerHeight;

        // Calculate footer area (comments + related)
        var footerTop = documentHeight;
        if (recordComments) {
            footerTop = Math.min(footerTop, recordComments.offsetTop);
        }
        if (recordRelated) {
            footerTop = Math.min(footerTop, recordRelated.offsetTop);
        }

        // Show after scrolling past hero
        var shouldShow = scrollY > heroHeight * 0.7;

        // Hide near footer
        var nearFooter = scrollY + viewportHeight > footerTop - 100;

        if (shouldShow && !nearFooter) {
            if (!isVisible) {
                cta.classList.add('visible');
                cta.classList.remove('near-footer');
                isVisible = true;
            }
        } else if (nearFooter) {
            cta.classList.add('near-footer');
            isVisible = false;
        } else {
            cta.classList.remove('visible');
            isVisible = false;
        }
    }

    // Share functionality
    function shareRecord() {
        var title = <?php echo json_encode(get_the_title()); ?>;
        var url = <?php echo json_encode(get_permalink()); ?>;

        // Try native share API first (mobile)
        if (navigator.share) {
            navigator.share({
                title: title,
                text: 'Check out this record on UNMASK',
                url: url
            }).catch(function() {
                // Fallback to clipboard
                copyToClipboard(url);
            });
        } else {
            // Desktop fallback
            copyToClipboard(url);
        }
    }

    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showToast('link copied', true);
            }).catch(function() {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showToast('link copied', true);
        } catch (err) {
            showToast('copy failed', false);
        }
        document.body.removeChild(textarea);
    }

    function showToast(message, success) {
        if (!shareToast) return;
        shareToast.textContent = message;
        shareToast.classList.toggle('success', success);
        shareToast.classList.add('visible');

        setTimeout(function() {
            shareToast.classList.remove('visible');
        }, 2000);
    }

    // Event listeners
    if (shareBtn) {
        shareBtn.addEventListener('click', shareRecord);
    }

    // Throttled scroll handler
    var ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                checkScroll();
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });

    // Handle resize
    window.addEventListener('resize', function() {
        if (!isMobile() && cta) {
            cta.classList.remove('visible', 'near-footer');
            isVisible = false;
        }
    });

    // Initial check
    checkScroll();
})();
</script>

<?php
endwhile;

get_footer();
