<?php
/**
 * UNMASK Avatar Component
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type int    $user_id     WordPress user ID
 *     @type string $size        xs|sm|md|lg|xl
 *     @type bool   $show_status Whether to show status indicator
 *     @type string $status      online|offline|away|busy
 *     @type bool   $linked      Whether to link to profile
 *     @type string $class       Additional CSS classes
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args passed from shortcode or direct call
$args = wp_parse_args($args ?? array(), array(
    'user_id'     => get_current_user_id(),
    'size'        => 'md',
    'show_status' => false,
    'status'      => 'offline',
    'linked'      => true,
    'class'       => '',
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

if ($args['show_status']) {
    $classes[] = 'unmask-avatar--has-status';
}

if (!empty($args['class'])) {
    $classes[] = esc_attr($args['class']);
}

$class_string = implode(' ', $classes);

// Get avatar HTML - use BuddyBoss if available, fallback to WordPress
if (function_exists('bp_core_fetch_avatar')) {
    $avatar_html = bp_core_fetch_avatar(array(
        'item_id' => $args['user_id'],
        'type'    => 'full',
        'width'   => $avatar_size,
        'height'  => $avatar_size,
        'html'    => true,
        'class'   => 'unmask-avatar__img',
    ));
} else {
    $avatar_html = get_avatar($args['user_id'], $avatar_size, '', '', array(
        'class' => 'unmask-avatar__img',
    ));
}

// Get profile URL for linking
$profile_url = '';
if ($args['linked'] && function_exists('bp_core_get_user_domain')) {
    $profile_url = bp_core_get_user_domain($args['user_id']);
} elseif ($args['linked']) {
    $profile_url = get_author_posts_url($args['user_id']);
}
?>

<div class="<?php echo $class_string; ?>">
    <?php if ($args['linked'] && !empty($profile_url)) : ?>
        <a href="<?php echo esc_url($profile_url); ?>" class="unmask-avatar__link">
            <?php echo $avatar_html; ?>
        </a>
    <?php else : ?>
        <?php echo $avatar_html; ?>
    <?php endif; ?>

    <?php if ($args['show_status']) : ?>
        <span class="unmask-avatar__status unmask-avatar__status--<?php echo esc_attr($args['status']); ?>"></span>
    <?php endif; ?>
</div>
