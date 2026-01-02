<?php
/**
 * UNMASK ISO Card Component
 *
 * Member card displaying user avatar, name, and designation (D-XXX/V-XXX).
 * Designed for the UNMASK community member identification system.
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type int    $user_id          WordPress user ID
 *     @type bool   $show_designation Whether to show D-XXX/V-XXX designation
 *     @type bool   $show_avatar      Whether to show user avatar
 *     @type bool   $show_name        Whether to show display name
 *     @type string $display_name     User display name (auto-fetched if not provided)
 *     @type string $designation      User designation (auto-fetched if not provided)
 *     @type string $variant          default|compact|expanded
 *     @type string $class            Additional CSS classes
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args passed from shortcode or direct call
$args = wp_parse_args($args ?? array(), array(
    'user_id'          => get_current_user_id(),
    'show_designation' => true,
    'show_avatar'      => true,
    'show_name'        => true,
    'display_name'     => '',
    'designation'      => '',
    'variant'          => 'default',
    'class'            => '',
));

// Fetch user data if not provided
if (empty($args['display_name']) || empty($args['designation'])) {
    $user = get_userdata($args['user_id']);
    if ($user) {
        if (empty($args['display_name'])) {
            $args['display_name'] = $user->display_name;
        }
        if (empty($args['designation']) && function_exists('unmask_get_user_designation')) {
            $args['designation'] = unmask_get_user_designation($args['user_id']);
        }
    }
}

// Don't render if no user
if (empty($args['display_name']) && empty($args['designation'])) {
    return;
}

// Build CSS classes
$classes = array('unmask-iso-card');
$classes[] = 'unmask-iso-card--' . esc_attr($args['variant']);

if (!empty($args['class'])) {
    $classes[] = esc_attr($args['class']);
}

$class_string = implode(' ', $classes);

// Get profile URL
$profile_url = '';
if (function_exists('bp_core_get_user_domain')) {
    $profile_url = bp_core_get_user_domain($args['user_id']);
} else {
    $profile_url = get_author_posts_url($args['user_id']);
}

// Avatar size based on variant
$avatar_sizes = array(
    'compact'  => 'sm',
    'default'  => 'md',
    'expanded' => 'lg',
);
$avatar_size = isset($avatar_sizes[$args['variant']]) ? $avatar_sizes[$args['variant']] : 'md';
?>

<div class="<?php echo $class_string; ?>">
    <?php if (!empty($profile_url)) : ?>
        <a href="<?php echo esc_url($profile_url); ?>" class="unmask-iso-card__link">
    <?php endif; ?>

    <div class="unmask-iso-card__inner">
        <?php if ($args['show_avatar']) : ?>
            <div class="unmask-iso-card__avatar">
                <?php
                // Use avatar template part
                get_template_part('template-parts/components/unmask-avatar', null, array(
                    'user_id' => $args['user_id'],
                    'size'    => $avatar_size,
                    'linked'  => false, // Parent handles linking
                ));
                ?>
            </div>
        <?php endif; ?>

        <div class="unmask-iso-card__info">
            <?php if ($args['show_name'] && !empty($args['display_name'])) : ?>
                <span class="unmask-iso-card__name"><?php echo esc_html($args['display_name']); ?></span>
            <?php endif; ?>

            <?php if ($args['show_designation'] && !empty($args['designation'])) : ?>
                <span class="unmask-iso-card__designation"><?php echo esc_html($args['designation']); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($profile_url)) : ?>
        </a>
    <?php endif; ?>
</div>
