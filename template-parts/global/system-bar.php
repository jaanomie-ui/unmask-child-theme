<?php
/**
 * UNMASK System Bar
 *
 * Global status bar component displayed at top of pages.
 * Fixed position on desktop, static on mobile.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user info
$current_user_id = get_current_user_id();
$user_designation = '';

if ($current_user_id && function_exists('unmask_get_user_designation')) {
    $user_designation = unmask_get_user_designation($current_user_id);
}

// Get online count (placeholder - integrate with BuddyBoss activity later)
$watching_count = 12; // Placeholder

// System status
$system_status = 'online';
?>

<div class="unmask-system-bar">
    <div class="unmask-system-bar__left">
        <div class="unmask-system-bar__status">
            <span class="unmask-system-bar__dot unmask-system-bar__dot--<?php echo esc_attr($system_status); ?>"></span>
            <span class="unmask-system-bar__label">sys.<?php echo esc_html($system_status); ?></span>
        </div>
        <div class="unmask-system-bar__location">
            <span>chi / 41.8781° n</span>
        </div>
    </div>

    <div class="unmask-system-bar__right">
        <div class="unmask-system-bar__watching">
            <span><?php echo esc_html($watching_count); ?> watching</span>
        </div>
        <?php if ($user_designation) : ?>
            <div class="unmask-system-bar__user">
                <span><?php echo esc_html($user_designation); ?></span>
            </div>
        <?php elseif (is_user_logged_in()) : ?>
            <div class="unmask-system-bar__user">
                <span>USER-<?php echo esc_html($current_user_id); ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>
