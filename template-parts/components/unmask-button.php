<?php
/**
 * UNMASK Button Component
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type string $text     Button text
 *     @type string $url      Link URL
 *     @type string $variant  primary|secondary|ghost|danger
 *     @type string $size     sm|md|lg
 *     @type string $icon     Icon class
 *     @type bool   $disabled Whether button is disabled
 *     @type string $class    Additional CSS classes
 *     @type string $target   Link target (_blank, etc.)
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args passed from shortcode or direct call
$args = wp_parse_args($args ?? array(), array(
    'text'     => 'Button',
    'url'      => '#',
    'variant'  => 'primary',
    'size'     => 'md',
    'icon'     => '',
    'disabled' => false,
    'class'    => '',
    'target'   => '',
));

// Build CSS classes
$classes = array('unmask-btn');
$classes[] = 'unmask-btn--' . esc_attr($args['variant']);
$classes[] = 'unmask-btn--' . esc_attr($args['size']);

if ($args['disabled']) {
    $classes[] = 'unmask-btn--disabled';
}

if (!empty($args['class'])) {
    $classes[] = esc_attr($args['class']);
}

$class_string = implode(' ', $classes);

// Build attributes
$attrs = array();
if (!empty($args['target'])) {
    $attrs[] = 'target="' . esc_attr($args['target']) . '"';
    if ($args['target'] === '_blank') {
        $attrs[] = 'rel="noopener noreferrer"';
    }
}
if ($args['disabled']) {
    $attrs[] = 'aria-disabled="true"';
    $attrs[] = 'tabindex="-1"';
}

$attr_string = implode(' ', $attrs);
?>

<a href="<?php echo esc_url($args['url']); ?>" class="<?php echo $class_string; ?>"<?php echo $attr_string ? ' ' . $attr_string : ''; ?>>
    <?php if (!empty($args['icon'])) : ?>
        <span class="unmask-btn__icon"><?php echo esc_html($args['icon']); ?></span>
    <?php endif; ?>
    <span class="unmask-btn__text"><?php echo esc_html($args['text']); ?></span>
</a>
