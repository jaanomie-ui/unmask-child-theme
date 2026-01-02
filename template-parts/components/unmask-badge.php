<?php
/**
 * UNMASK Badge Component
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type string $text    Badge text
 *     @type string $variant default|success|warning|error|info
 *     @type string $size    sm|md|lg
 *     @type string $icon    Icon class
 *     @type string $class   Additional CSS classes
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args passed from shortcode or direct call
$args = wp_parse_args($args ?? array(), array(
    'text'    => '',
    'variant' => 'default',
    'size'    => 'md',
    'icon'    => '',
    'class'   => '',
));

// Don't render if no text
if (empty($args['text'])) {
    return;
}

// Build CSS classes
$classes = array('unmask-badge');
$classes[] = 'unmask-badge--' . esc_attr($args['variant']);
$classes[] = 'unmask-badge--' . esc_attr($args['size']);

if (!empty($args['class'])) {
    $classes[] = esc_attr($args['class']);
}

$class_string = implode(' ', $classes);
?>

<span class="<?php echo $class_string; ?>">
    <?php if (!empty($args['icon'])) : ?>
        <span class="unmask-badge__icon"><?php echo esc_html($args['icon']); ?></span>
    <?php endif; ?>
    <span class="unmask-badge__text"><?php echo esc_html($args['text']); ?></span>
</span>
