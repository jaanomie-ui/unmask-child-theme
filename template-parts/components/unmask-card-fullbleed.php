<?php
/**
 * UNMASK Card Fullbleed Component
 *
 * A versatile card component with fullbleed image header.
 * Can auto-fetch data from a post or use manual data.
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     Auto mode (fetches from post):
 *     @type int    $post_id    Post ID to fetch data from
 *
 *     Manual mode:
 *     @type string $image      Image URL for fullbleed header
 *     @type string $file_id    File ID (e.g., "UM-024")
 *     @type string $type_badge Type badge (interview|editorial|session|archive)
 *     @type string $subject    Subject/title
 *     @type string $desc       Description text
 *     @type string $href       Link URL
 *     @type string $class      Additional CSS classes
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args passed from shortcode or direct call
$args = wp_parse_args($args ?? array(), array(
    'post_id'    => 0,
    'image'      => '',
    'file_id'    => '',
    'type_badge' => '',
    'subject'    => '',
    'desc'       => '',
    'href'       => '',
    'class'      => '',
));

// Auto-fetch from post if post_id provided
if (!empty($args['post_id'])) {
    $post = get_post($args['post_id']);
    if ($post) {
        // Get featured image
        if (empty($args['image']) && has_post_thumbnail($args['post_id'])) {
            $args['image'] = get_the_post_thumbnail_url($args['post_id'], 'large');
        }

        // Get title as subject
        if (empty($args['subject'])) {
            $args['subject'] = get_the_title($args['post_id']);
        }

        // Get excerpt as description
        if (empty($args['desc'])) {
            $args['desc'] = get_the_excerpt($args['post_id']);
        }

        // Get permalink as href
        if (empty($args['href'])) {
            $args['href'] = get_permalink($args['post_id']);
        }

        // Try to get file_id from post meta
        if (empty($args['file_id'])) {
            $args['file_id'] = get_post_meta($args['post_id'], 'unmask_file_id', true);
        }

        // Try to get type_badge from post meta or taxonomy
        if (empty($args['type_badge'])) {
            $args['type_badge'] = get_post_meta($args['post_id'], 'unmask_type', true);
        }
    }
}

// Build CSS classes
$classes = array('unmask-card', 'unmask-card--fullbleed');

if (!empty($args['href'])) {
    $classes[] = 'unmask-card--linked';
}

if (!empty($args['image'])) {
    $classes[] = 'unmask-card--has-image';
}

if (!empty($args['class'])) {
    $classes[] = esc_attr($args['class']);
}

$class_string = implode(' ', $classes);
?>

<article class="<?php echo $class_string; ?>">
    <?php if (!empty($args['image'])) : ?>
        <div class="unmask-card__media">
            <img src="<?php echo esc_url($args['image']); ?>" alt="<?php echo esc_attr($args['subject']); ?>" class="unmask-card__image" loading="lazy">
        </div>
    <?php endif; ?>

    <div class="unmask-card__body">
        <header class="unmask-card__header">
            <?php if (!empty($args['file_id']) || !empty($args['type_badge'])) : ?>
                <div class="unmask-card__meta unmask-cluster--xs">
                    <?php if (!empty($args['file_id'])) : ?>
                        <?php get_template_part('template-parts/components/unmask-badge', null, [
                            'text' => $args['file_id'],
                            'type' => 'default',
                            'size' => 'sm',
                        ]); ?>
                    <?php endif; ?>

                    <?php if (!empty($args['type_badge'])) : ?>
                        <?php get_template_part('template-parts/components/unmask-badge', null, [
                            'text' => $args['type_badge'],
                            'type' => esc_attr($args['type_badge']),
                            'size' => 'sm',
                        ]); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($args['subject'])) : ?>
                <?php if (!empty($args['href'])) : ?>
                    <h3 class="unmask-card__title">
                        <a href="<?php echo esc_url($args['href']); ?>" class="unmask-card__link">
                            <?php echo esc_html($args['subject']); ?>
                        </a>
                    </h3>
                <?php else : ?>
                    <h3 class="unmask-card__title"><?php echo esc_html($args['subject']); ?></h3>
                <?php endif; ?>
            <?php endif; ?>
        </header>

        <?php if (!empty($args['desc'])) : ?>
            <div class="unmask-card__content">
                <p class="unmask-card__desc"><?php echo esc_html($args['desc']); ?></p>
            </div>
        <?php endif; ?>
    </div>
</article>
