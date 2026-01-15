<?php
/**
 * UNMASK Hero Section
 *
 * 3-column Swiss grid hero layout.
 * Col 1: Fullbleed image
 * Cols 2-3: Centered content box
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * ACF Fields (non-repeating):
 * - hero_label: Text (default: "latest record")
 * - hero_cta_label: Text (default: "Open call:")
 * - hero_cta_text: Text (default: "tell your story")
 * - hero_fallback_issue: Text (default: "issue 001")
 * - hero_fallback_location: Text (default: "chicago")
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get ACF field values with fallbacks
$hero_label = unmask_get_field('hero_label', 'latest record');
$hero_cta_label = unmask_get_field('hero_cta_label', 'Open call:');
$hero_cta_text = unmask_get_field('hero_cta_text', 'tell your story');
$hero_fallback_issue = unmask_get_field('hero_fallback_issue', 'issue 001');
$hero_fallback_location = unmask_get_field('hero_fallback_location', 'chicago');

// Get latest post excluding category 'd001'
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

// Fallback data if no posts
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

    $hero_data['image']     = get_the_post_thumbnail_url(get_the_ID(), 'large'); // Use 'large' instead of 'full' for performance
    $hero_data['file_id']   = get_post_meta(get_the_ID(), 'unmask_file_id', true) ?: sprintf('UM-%03d', get_the_ID());
    $hero_data['title']     = get_the_title();
    $hero_data['type']      = get_post_meta(get_the_ID(), 'unmask_type', true) ?: 'record';
    $hero_data['desc']      = get_the_excerpt();
    $hero_data['date']      = get_the_date('F Y');
    $hero_data['permalink'] = get_permalink();

    wp_reset_postdata();
}
?>

<section class="unmask-hero">
    <div class="unmask-hero__grid">

        <!-- Column 1: Image (full bleed) -->
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

        <!-- Columns 2-3: Content area -->
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

            <!-- Center line (visual grid guide) -->
            <div class="unmask-hero__center-line"></div>

            <!-- Navigation arrows -->
            <nav class="unmask-hero__nav" aria-label="Hero navigation">
                <button class="unmask-hero__nav-btn unmask-hero__nav-btn--prev" aria-label="Previous record">
                    <span aria-hidden="true">&larr;</span>
                </button>
                <button class="unmask-hero__nav-btn unmask-hero__nav-btn--next" aria-label="Next record">
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </nav>
        </div>

    </div>
</section>
