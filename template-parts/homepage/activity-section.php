<?php
/**
 * UNMASK Activity Section
 *
 * Activity feed showing recent user actions.
 * Uses placeholder data for MVP - integrate with BuddyBoss activity later.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Placeholder activity data
// TODO: Replace with BuddyBoss activity query
$activity_items = [
    [
        'user'   => 'USER-3201',
        'action' => 'viewed',
        'target' => 'Magnus',
        'time'   => '2m',
    ],
    [
        'user'   => 'D-012',
        'action' => 'posted ISO',
        'target' => '',
        'time'   => '8m',
    ],
    [
        'user'   => 'USER-4511',
        'action' => 'viewed',
        'target' => 'Velvet',
        'time'   => '12m',
    ],
    [
        'user'   => 'V-089',
        'action' => 'joined',
        'target' => '',
        'time'   => '15m',
    ],
];
?>

<section class="unmask-activity">
    <div class="unmask-activity__feed">
        <header class="unmask-activity__header">
            <span class="unmask-activity__title">activity</span>
            <span class="unmask-activity__status">live</span>
        </header>

        <div class="unmask-activity__body">
            <?php foreach ($activity_items as $item) : ?>
                <div class="unmask-activity__item">
                    <span class="unmask-activity__action">
                        <span class="unmask-activity__user"><?php echo esc_html($item['user']); ?></span>
                        <?php echo esc_html($item['action']); ?>
                        <?php if (!empty($item['target'])) : ?>
                            <span class="unmask-activity__target"><?php echo esc_html($item['target']); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="unmask-activity__time"><?php echo esc_html($item['time']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
