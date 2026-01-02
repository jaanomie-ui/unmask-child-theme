<?php
/**
 * UNMASK Badge Component
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type string $text  Badge text
 *     @type string $type  published|draft|expired|active|drone|visitor|schedule|available
 *     @type string $size  sm|md|lg
 *     @type bool   $dot   Show status dot
 *     @type string $class Additional CSS classes
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args passed from shortcode or direct call
$args = wp_parse_args($args ?? array(), array(
    'text'  => '',
    'type'  => 'default',
    'size'  => 'md',
    'dot'   => false,
    'class' => '',
));

// Don't render if no text
if (empty($args['text'])) {
    return;
}

// Build CSS classes
$classes = array('unmask-badge');
$classes[] = 'unmask-badge--' . esc_attr($args['type']);
$classes[] = 'unmask-badge--' . esc_attr($args['size']);

if ($args['dot']) {
    $classes[] = 'unmask-badge--has-dot';
}

if (!empty($args['class'])) {
    $classes[] = esc_attr($args['class']);
}

$class_string = implode(' ', $classes);
?>

<span class="<?php echo $class_string; ?>">
    <?php if ($args['dot']) : ?>
        <span class="unmask-badge__dot"></span>
    <?php endif; ?>
    <span class="unmask-badge__text"><?php echo esc_html($args['text']); ?></span>
</span>
