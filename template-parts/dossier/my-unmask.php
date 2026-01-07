<?php
/**
 * Dossier: MY UNMASK Dashboard
 *
 * Only visible on own profile. Contains:
 * - Header with visibility toggle
 * - Dashboard grid (metrics)
 * - Activity stream (restyled BuddyBoss)
 * - Membership bar
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

// Only show on own profile
if (!bp_is_my_profile()) {
    return;
}

$user_id = get_current_user_id();
$visibility = unmask_get_dashboard_visibility($user_id);
$data = unmask_get_dashboard_data($user_id);
$member_type = unmask_get_member_type($user_id);
$activities = unmask_get_user_activity($user_id, 5);
$completion = unmask_get_profile_completion($user_id);

// Format Factory booking
$factory_display = '—';
$factory_meta = '';
if ($data['factory_next']) {
    $factory_display = unmask_format_date($data['factory_next']->booking_date ?? '', 'M j');
    if (isset($data['factory_next']->booking_time)) {
        $factory_meta = $data['factory_next']->booking_time;
    }
}
?>

<section class="dossier-section">
    <div class="my-unmask">
        <!-- Header -->
        <div class="my-unmask__head">
            <span class="my-unmask__title">my unmask</span>
            <div class="my-unmask-visibility">
                <span class="my-unmask-visibility__label">public:</span>
                <button type="button"
                        class="my-unmask-visibility__btn <?php echo $visibility === 'masked' ? 'active' : ''; ?>"
                        data-visibility="masked">mask</button>
                <button type="button"
                        class="my-unmask-visibility__btn <?php echo $visibility === 'unmasked' ? 'active' : ''; ?>"
                        data-visibility="unmasked">unmask</button>
            </div>
        </div>

        <!-- Dashboard Grid Row 1 -->
        <div class="my-unmask-grid">
            <a href="<?php echo esc_url(home_url('/iso-board/')); ?>" class="dash-cell">
                <div class="dash-cell__key">ISOs</div>
                <div class="dash-cell__val"><?php echo esc_html($data['isos_active']); ?></div>
                <div class="dash-cell__meta">active</div>
            </a>

            <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="dash-cell">
                <div class="dash-cell__key">Factory</div>
                <div class="dash-cell__val <?php echo $factory_display !== '—' ? 'dash-cell__val--green' : 'dash-cell__val--dim'; ?>">
                    <?php echo esc_html($factory_display); ?>
                </div>
                <div class="dash-cell__meta"><?php echo esc_html($factory_meta ?: 'no booking'); ?></div>
            </a>

            <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'friends/'); ?>" class="dash-cell">
                <div class="dash-cell__key">Connections</div>
                <div class="dash-cell__val"><?php echo esc_html($data['connections']); ?></div>
                <div class="dash-cell__meta">
                    <?php if ($data['connections_pending'] > 0) : ?>
                        +<?php echo esc_html($data['connections_pending']); ?> pending
                    <?php else : ?>
                        connected
                    <?php endif; ?>
                </div>
            </a>

            <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'messages/'); ?>" class="dash-cell">
                <div class="dash-cell__key">Messages</div>
                <div class="dash-cell__val <?php echo $data['messages_unread'] > 0 ? 'dash-cell__val--red' : ''; ?>">
                    <?php echo esc_html($data['messages_unread']); ?>
                </div>
                <div class="dash-cell__meta">unread</div>
            </a>
        </div>

        <!-- Dashboard Grid Row 2 -->
        <div class="my-unmask-grid">
            <div class="dash-cell">
                <div class="dash-cell__key">Pink Panthers</div>
                <div class="dash-cell__val dash-cell__val--dim">coming soon</div>
                <div class="dash-cell__meta"></div>
            </div>

            <a href="<?php echo esc_url(home_url('/magazine/')); ?>" class="dash-cell">
                <div class="dash-cell__key">Records Read</div>
                <div class="dash-cell__val"><?php echo esc_html($data['records_read']); ?></div>
                <div class="dash-cell__meta">the archive</div>
            </a>

            <div class="dash-cell">
                <div class="dash-cell__key">Credits</div>
                <div class="dash-cell__val"><?php echo esc_html($data['credits']); ?></div>
                <div class="dash-cell__meta">magazine features</div>
            </div>

            <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'settings/'); ?>" class="dash-cell">
                <div class="dash-cell__key">Settings</div>
                <div class="dash-cell__val dash-cell__val--dim">&#8594;</div>
                <div class="dash-cell__meta">account</div>
            </a>
        </div>

        <!-- Activity Stream -->
        <div class="my-unmask-activity">
            <div class="my-unmask-activity__head">recent activity</div>

            <?php if (!empty($activities)) : ?>
                <?php foreach ($activities as $activity) : ?>
                    <div class="activity-entry">
                        <span class="activity-entry__time"><?php echo esc_html($activity['date_formatted']); ?></span>
                        <span class="activity-entry__type"><?php echo esc_html(ucfirst($activity['component'])); ?></span>
                        <span class="activity-entry__desc"><?php echo esc_html($activity['action'] ?: $activity['content']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="activity-entry">
                    <span class="activity-entry__desc">no recent activity</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Membership Bar -->
        <div class="my-unmask-membership">
            <div class="my-unmask-membership__info">
                <span class="my-unmask-membership__type my-unmask-membership__type--<?php echo esc_attr($member_type); ?>">
                    <?php echo esc_html(strtoupper($member_type)); ?>
                </span>
                <span class="my-unmask-membership__detail">
                    <?php if ($member_type === 'drone') : ?>
                        membership active
                    <?php else : ?>
                        free account
                    <?php endif; ?>
                </span>
            </div>
            <a href="<?php echo esc_url(home_url('/account/')); ?>" class="dossier-action">manage</a>
        </div>

        <?php if ($completion['percentage'] < 100) : ?>
        <!-- Profile Completion -->
        <div class="my-unmask-completion">
            <div class="my-unmask-completion__head">
                <span class="my-unmask-completion__title">dossier completion</span>
                <span class="my-unmask-completion__pct"><?php echo esc_html($completion['percentage']); ?>%</span>
            </div>
            <div class="my-unmask-completion__bar">
                <div class="my-unmask-completion__fill" style="width: <?php echo esc_attr($completion['percentage']); ?>%;"></div>
            </div>
            <?php if (!empty($completion['missing'])) : ?>
                <div class="my-unmask-completion__missing">
                    <span class="my-unmask-completion__label">missing:</span>
                    <?php
                    $missing_labels = array_map(function($field) {
                        return $field['label'];
                    }, $completion['missing']);
                    echo esc_html(implode(', ', $missing_labels));
                    ?>
                    <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'profile/edit/'); ?>" class="my-unmask-completion__link">complete profile</a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
