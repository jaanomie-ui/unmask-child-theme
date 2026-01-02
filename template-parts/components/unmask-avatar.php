<?php
/**
 * UNMASK Avatar Component
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type int    $user_id WordPress user ID
 *     @type string $size    xs|sm|md|lg|xl
 *     @type bool   $online  Show online status indicator
 *     @type bool   $square  Square avatar (default: round)
 *     @type string $href    Link URL (optional, auto-generates profile link if true)
 *     @type string $class   Additional CSS classes
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args passed from shortcode or direct call
$args = wp_parse_args($args ?? array(), array(
    'user_id' => get_current_user_id(),
    'size'    => 'md',
    'online'  => false,
    'square'  => false,
    'href'    => '',
    'class'   => '',
));

// Size mapping for avatar dimensions
$size_map = array(
    'xs' => 24,
    'sm' => 32,
    'md' => 48,
    'lg' => 64,
    'xl' => 96,
);

$avatar_size = isset($size_map[$args['size']]) ? $size_map[$args['size']] : 48;

// Build CSS classes
$classes = array('unmask-avatar');
$classes[] = 'unmask-avatar--' . esc_attr($args['size']);

if ($args['online']) {
    $classes[] = 'unmask-avatar--online';
}

if ($args['square']) {
    $classes[] = 'unmask-avatar--square';
}

if (!empty($args['class'])) {
    $classes[] = esc_attr($args['class']);
}

$class_string = implode(' ', $classes);

// Get avatar HTML - use BuddyBoss if available, fallback to WordPress
$img_class = 'unmask-avatar__img';
if ($args['square']) {
    $img_class .= ' unmask-avatar__img--square';
}

if (function_exists('bp_core_fetch_avatar')) {
    $avatar_html = bp_core_fetch_avatar(array(
        'item_id' => $args['user_id'],
        'type'    => 'full',
        'width'   => $avatar_size,
        'height'  => $avatar_size,
        'html'    => true,
        'class'   => $img_class,
    ));
} else {
    $avatar_html = get_avatar($args['user_id'], $avatar_size, '', '', array(
        'class' => $img_class,
    ));
}

// Determine link URL
$link_url = '';
if (!empty($args['href'])) {
    $link_url = $args['href'];
} elseif ($args['href'] !== false) {
    // Auto-generate profile link if href is not explicitly false
    if (function_exists('bp_core_get_user_domain')) {
        $link_url = bp_core_get_user_domain($args['user_id']);
    }
}
?>

<div class="<?php echo $class_string; ?>">
    <?php if (!empty($link_url)) : ?>
        <a href="<?php echo esc_url($link_url); ?>" class="unmask-avatar__link">
            <?php echo $avatar_html; ?>
        </a>
    <?php else : ?>
        <?php echo $avatar_html; ?>
    <?php endif; ?>

    <?php if ($args['online']) : ?>
        <span class="unmask-avatar__status unmask-avatar__status--online"></span>
    <?php endif; ?>
</div>
