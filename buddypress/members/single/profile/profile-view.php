<?php
/**
 * UNMASK Profile View — Clean Terminal Aesthetic
 *
 * Completely replaces BuddyBoss profile display with a minimal,
 * terminal-styled page using design system tokens.
 *
 * @package UNMASK
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get user data
$displayed_user_id = bp_displayed_user_id();
$viewer_id = get_current_user_id();
$is_own_profile = bp_is_my_profile();

// Fetch all profile data server-side
$basics = unmask_get_profile_basics($displayed_user_id);
$avatar_url = unmask_get_avatar_url($displayed_user_id, 160);
$member_type = unmask_get_member_type($displayed_user_id);
$designation = unmask_get_designation($displayed_user_id);
$functions = unmask_get_user_functions($displayed_user_id);
$status = unmask_get_user_status($displayed_user_id);
$practice = unmask_get_creative_practice($displayed_user_id);
$kink = unmask_get_kink_profile($displayed_user_id);

// Visibility checks
$can_view_bio = unmask_can_view_section($displayed_user_id, $viewer_id, 'bio');
$can_view_location = unmask_can_view_section($displayed_user_id, $viewer_id, 'location');
$can_view_practice = unmask_can_view_section($displayed_user_id, $viewer_id, 'practice');
$can_view_kink = unmask_can_view_section($displayed_user_id, $viewer_id, 'kink');

// Connection status
$is_connected = $viewer_id > 0 && unmask_are_users_connected($displayed_user_id, $viewer_id);
$mutual_count = ($viewer_id > 0 && !$is_own_profile) ? unmask_get_mutual_connections($displayed_user_id, $viewer_id) : 0;

// Dashboard data (own profile only)
$dashboard = $is_own_profile ? unmask_get_dashboard_data($displayed_user_id) : null;
?>

<article class="dossier-view">
    <!-- ================================================================
         HEADER: Avatar + Identity
         ================================================================ -->
    <header class="dossier-view__header">
        <div class="dossier-view__avatar">
            <img src="<?php echo esc_url($avatar_url); ?>"
                 alt="<?php echo esc_attr($basics['display_name']); ?>"
                 width="160"
                 height="160"
                 loading="eager">
        </div>

        <div class="dossier-view__identity">
            <h1 class="dossier-view__name"><?php echo esc_html($basics['display_name']); ?></h1>

            <div class="dossier-view__meta">
                <?php if (!empty($basics['pronouns'])) : ?>
                    <span class="dossier-view__pronouns"><?php echo esc_html($basics['pronouns']); ?></span>
                <?php endif; ?>

                <?php if ($can_view_location && !empty($basics['city'])) : ?>
                    <span class="dossier-view__location"><?php echo esc_html($basics['city']); ?></span>
                <?php endif; ?>
            </div>

            <?php if ($designation) : ?>
                <span class="dossier-view__badge"><?php echo esc_html($designation); ?></span>
            <?php endif; ?>
        </div>

        <?php if ($is_own_profile) : ?>
            <a href="<?php echo esc_url(bp_displayed_user_domain() . 'profile/edit/'); ?>"
               class="dossier-view__edit-link">
                edit dossier
            </a>
        <?php elseif ($viewer_id > 0 && !$is_connected) : ?>
            <?php if (function_exists('bp_get_add_friend_button')) : ?>
                <div class="dossier-view__action">
                    <?php bp_add_friend_button($displayed_user_id); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </header>

    <!-- ================================================================
         MAIN CONTENT: Profile Sections
         ================================================================ -->
    <main class="dossier-view__body">

        <!-- BIO SECTION -->
        <?php if ($can_view_bio && !empty($basics['bio'])) : ?>
        <section class="dossier-view__section" aria-labelledby="section-bio">
            <h2 class="dossier-view__section-label" id="section-bio">bio</h2>
            <div class="dossier-view__prose">
                <?php echo wp_kses_post(wpautop($basics['bio'])); ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- FUNCTIONS (Creative Roles) -->
        <?php if (!empty($functions)) : ?>
        <section class="dossier-view__section" aria-labelledby="section-functions">
            <h2 class="dossier-view__section-label" id="section-functions">functions</h2>
            <ul class="dossier-view__tags">
                <?php foreach ($functions as $fn) : ?>
                    <li class="dossier-view__tag"><?php echo esc_html(trim($fn)); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <!-- AVAILABILITY STATUS -->
        <?php if ($status['bookable'] || $status['available_photographed'] || $status['available_photograph']) : ?>
        <section class="dossier-view__section" aria-labelledby="section-status">
            <h2 class="dossier-view__section-label" id="section-status">availability</h2>
            <ul class="dossier-view__status-list">
                <?php if ($status['bookable']) : ?>
                    <li class="dossier-view__status dossier-view__status--active">open to bookings</li>
                <?php endif; ?>
                <?php if ($status['available_photographed']) : ?>
                    <li class="dossier-view__status dossier-view__status--active">available to be photographed</li>
                <?php endif; ?>
                <?php if ($status['available_photograph']) : ?>
                    <li class="dossier-view__status dossier-view__status--active">available to photograph</li>
                <?php endif; ?>
            </ul>
        </section>
        <?php endif; ?>

        <!-- CREATIVE PRACTICE -->
        <?php if ($can_view_practice && $practice) : ?>
        <section class="dossier-view__section" aria-labelledby="section-practice">
            <h2 class="dossier-view__section-label" id="section-practice">creative practice</h2>

            <?php if (!empty($practice['practices'])) : ?>
            <div class="dossier-view__row">
                <span class="dossier-view__key">disciplines</span>
                <span class="dossier-view__value">
                    <?php echo esc_html(implode(', ', $practice['practices'])); ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($practice['portfolio'])) :
                $portfolio_url = strip_tags($practice['portfolio']);
            ?>
            <div class="dossier-view__row">
                <span class="dossier-view__key">portfolio</span>
                <a href="<?php echo esc_url($portfolio_url); ?>"
                   class="dossier-view__value dossier-view__link"
                   target="_blank"
                   rel="noopener">
                    <?php echo esc_html(parse_url($portfolio_url, PHP_URL_HOST)); ?>
                </a>
            </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- KINK PROFILE (Connection-gated) -->
        <?php if ($can_view_kink && $kink) : ?>
        <section class="dossier-view__section dossier-view__section--kink" aria-labelledby="section-kink">
            <h2 class="dossier-view__section-label" id="section-kink">kink profile</h2>

            <?php if (!empty($kink['orientation'])) : ?>
            <div class="dossier-view__row">
                <span class="dossier-view__key">orientation</span>
                <span class="dossier-view__value"><?php echo esc_html($kink['orientation']); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($kink['interests'])) : ?>
            <div class="dossier-view__row">
                <span class="dossier-view__key">interests</span>
                <span class="dossier-view__value">
                    <?php echo esc_html(implode(', ', $kink['interests'])); ?>
                </span>
            </div>
            <?php endif; ?>
        </section>
        <?php elseif (unmask_has_kink_profile($displayed_user_id) && !$is_own_profile) : ?>
        <section class="dossier-view__section dossier-view__section--locked" aria-labelledby="section-kink-locked">
            <h2 class="dossier-view__section-label" id="section-kink-locked">kink profile</h2>
            <p class="dossier-view__locked-message">
                <?php if ($viewer_id <= 0) : ?>
                    log in to view
                <?php else : ?>
                    connect to view
                <?php endif; ?>
            </p>
        </section>
        <?php endif; ?>

        <!-- MUTUAL INTERLINKED (when viewing others) -->
        <?php if (!$is_own_profile && $mutual_count > 0) : ?>
        <section class="dossier-view__section" aria-labelledby="section-mutual">
            <h2 class="dossier-view__section-label" id="section-mutual">mutual interlinked</h2>
            <p class="dossier-view__mutual-count">
                <?php echo esc_html($mutual_count); ?> interlinked visitor<?php echo $mutual_count !== 1 ? 's' : ''; ?>
            </p>
        </section>
        <?php endif; ?>

    </main>

    <!-- ================================================================
         DASHBOARD (Own Profile Only)
         3x3 metrics grid + membership bar
         ================================================================ -->
    <?php if ($is_own_profile && $dashboard) : ?>
    <aside class="dossier-view__dashboard" aria-label="My Unmask Dashboard">
        <h2 class="dossier-view__section-label">my unmask</h2>

        <!-- 3x3 Metrics Grid -->
        <div class="dossier-view__metrics dossier-view__metrics--3x3">
            <a href="<?php echo esc_url(home_url('/isos/')); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value"><?php echo esc_html($dashboard['isos_active']); ?></span>
                <span class="dossier-view__metric-label">active isos</span>
            </a>

            <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value">
                    <?php echo $dashboard['factory_next'] ? esc_html(unmask_format_date($dashboard['factory_next']->booking_date)) : '—'; ?>
                </span>
                <span class="dossier-view__metric-label">factory</span>
            </a>

            <a href="<?php echo esc_url(bp_displayed_user_domain() . 'friends/'); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value"><?php echo esc_html($dashboard['connections']); ?></span>
                <span class="dossier-view__metric-label">interlinked</span>
                <?php if ($dashboard['connections_pending'] > 0) : ?>
                    <span class="dossier-view__metric-badge"><?php echo esc_html($dashboard['connections_pending']); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo esc_url(bp_displayed_user_domain() . 'messages/'); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value"><?php echo esc_html($dashboard['messages_unread']); ?></span>
                <span class="dossier-view__metric-label">messages</span>
            </a>

            <a href="<?php echo esc_url(bp_displayed_user_domain() . 'notifications/'); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value"><?php echo esc_html($dashboard['notifications_unread'] ?? 0); ?></span>
                <span class="dossier-view__metric-label">notifications</span>
            </a>

            <a href="<?php echo esc_url(bp_displayed_user_domain() . 'profile/'); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value"><?php echo esc_html($dashboard['profile_views'] ?? 0); ?></span>
                <span class="dossier-view__metric-label">visitors</span>
            </a>

            <a href="<?php echo esc_url(home_url('/the-archive/')); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value"><?php echo esc_html($dashboard['articles_published'] ?? 0); ?></span>
                <span class="dossier-view__metric-label">articles</span>
            </a>

            <a href="<?php echo esc_url(home_url('/submit/')); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value"><?php echo esc_html($dashboard['submissions_pending'] ?? 0); ?></span>
                <span class="dossier-view__metric-label">pending</span>
            </a>

            <a href="<?php echo esc_url(bp_displayed_user_domain() . 'settings/'); ?>" class="dossier-view__metric">
                <span class="dossier-view__metric-value">⚙</span>
                <span class="dossier-view__metric-label">settings</span>
            </a>
        </div>

        <!-- Membership Bar -->
        <div class="dossier-view__membership">
            <span class="dossier-view__membership-type">
                <?php echo esc_html($member_type ?: 'visitor'); ?>
            </span>
            <a href="<?php echo esc_url(home_url('/membership/')); ?>" class="dossier-view__membership-link">
                manage
            </a>
        </div>
    </aside>
    <?php endif; ?>

</article>
