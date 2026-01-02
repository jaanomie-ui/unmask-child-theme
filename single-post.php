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

    // Get gallery images for counter
    $gallery_images = get_post_meta($post_id, 'unmask_gallery_images', true);
    $image_count = is_array($gallery_images) ? count($gallery_images) : 0;
    if ($image_count === 0 && $featured_image) {
        $image_count = 1;
    }
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

            <!-- Column 2: Title Area (RIGHT justified, tags at bottom LEFT) -->
            <div class="record-hero__title-col">
                <span class="record-hero__label">subject dossier:</span>

                <h1 class="record-hero__title"><?php echo esc_html($title); ?></h1>

                <div class="record-hero__meta-grid">
                    <?php if ($subject) : ?>
                    <div class="record-hero__meta-item">
                        <div class="record-hero__meta-label">subject</div>
                        <div class="record-hero__meta-value"><?php echo esc_html($subject); ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="record-hero__meta-item">
                        <div class="record-hero__meta-label">location</div>
                        <div class="record-hero__meta-value"><?php echo esc_html($location); ?></div>
                    </div>

                    <div class="record-hero__meta-item">
                        <div class="record-hero__meta-label">filed</div>
                        <div class="record-hero__meta-value"><?php echo esc_html($date); ?></div>
                    </div>

                    <div class="record-hero__meta-item">
                        <div class="record-hero__meta-label">words</div>
                        <div class="record-hero__meta-value"><?php echo number_format($word_count); ?></div>
                    </div>
                </div>

                <!-- Tags anchored to bottom, LEFT justified -->
                <div class="record-hero__tags">
                    <?php echo do_shortcode('[unmask_tags]'); ?>
                </div>
            </div>

            <!-- Column 3: Additional Meta (LEFT justified) -->
            <div class="record-hero__meta-col">
                <div class="record-hero__record-id"><?php echo esc_html($file_id); ?></div>

                <?php echo do_shortcode('[unmask_type_badge]'); ?>

                <div class="record-hero__meta-grid record-hero__meta-grid--secondary">
                    <?php if ($issue) : ?>
                    <div class="record-hero__meta-item">
                        <div class="record-hero__meta-label">issue</div>
                        <div class="record-hero__meta-value"><?php echo esc_html($issue); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($photographer) : ?>
                    <div class="record-hero__meta-item">
                        <div class="record-hero__meta-label">photographer</div>
                        <div class="record-hero__meta-value"><?php echo esc_html($photographer); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($writer) : ?>
                    <div class="record-hero__meta-item">
                        <div class="record-hero__meta-label">writer</div>
                        <div class="record-hero__meta-value"><?php echo esc_html($writer); ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="record-hero__status">
                    <span class="record-hero__status-dot"></span>
                    <span>archived / public</span>
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
         FOOTER - Navigation
    ═══════════════════════════════════════════════════════════════ -->
    <footer class="record-footer">
        <span class="record-footer__copyright">© <?php echo date('Y'); ?> UNMASK / the archive</span>
        <nav class="record-footer__nav">
            <?php
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            ?>
            <?php if ($prev_post) : ?>
                <a href="<?php echo get_permalink($prev_post); ?>" class="record-footer__link">← previous record</a>
            <?php endif; ?>
            <?php if ($next_post) : ?>
                <a href="<?php echo get_permalink($next_post); ?>" class="record-footer__link">next record →</a>
            <?php endif; ?>
        </nav>
        <span class="record-footer__share">share</span>
    </footer>

</div>

<?php
endwhile;

get_footer();
