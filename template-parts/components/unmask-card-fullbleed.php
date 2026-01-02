<?php
/**
 * UNMASK Card Fullbleed Component
 *
 * A versatile card component with optional fullbleed image header.
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type string $title    Card title
 *     @type string $subtitle Card subtitle
 *     @type string $image    Image URL for fullbleed header
 *     @type string $url      Link URL (makes entire card clickable)
 *     @type string $content  Card body content (HTML allowed)
 *     @type string $variant  default|elevated|outline
 *     @type string $class    Additional CSS classes
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args passed from shortcode or direct call
$args = wp_parse_args($args ?? array(), array(
    'title'    => '',
    'subtitle' => '',
    'image'    => '',
    'url'      => '',
    'content'  => '',
    'variant'  => 'default',
    'class'    => '',
));

// Build CSS classes
$classes = array('unmask-card', 'unmask-card--fullbleed');
$classes[] = 'unmask-card--' . esc_attr($args['variant']);

if (!empty($args['url'])) {
    $classes[] = 'unmask-card--linked';
}

if (!empty($args['image'])) {
    $classes[] = 'unmask-card--has-image';
}

if (!empty($args['class'])) {
    $classes[] = esc_attr($args['class']);
}

$class_string = implode(' ', $classes);

// Determine tag - article for semantic markup
$tag = 'article';
?>

<<?php echo $tag; ?> class="<?php echo $class_string; ?>">
    <?php if (!empty($args['image'])) : ?>
        <div class="unmask-card__media">
            <img src="<?php echo esc_url($args['image']); ?>" alt="<?php echo esc_attr($args['title']); ?>" class="unmask-card__image" loading="lazy">
        </div>
    <?php endif; ?>

    <div class="unmask-card__body">
        <?php if (!empty($args['title'])) : ?>
            <header class="unmask-card__header">
                <?php if (!empty($args['url'])) : ?>
                    <h3 class="unmask-card__title">
                        <a href="<?php echo esc_url($args['url']); ?>" class="unmask-card__link">
                            <?php echo esc_html($args['title']); ?>
                        </a>
                    </h3>
                <?php else : ?>
                    <h3 class="unmask-card__title"><?php echo esc_html($args['title']); ?></h3>
                <?php endif; ?>

                <?php if (!empty($args['subtitle'])) : ?>
                    <p class="unmask-card__subtitle"><?php echo esc_html($args['subtitle']); ?></p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <?php if (!empty($args['content'])) : ?>
            <div class="unmask-card__content">
                <?php echo wp_kses_post($args['content']); ?>
            </div>
        <?php endif; ?>
    </div>
</<?php echo $tag; ?>>
