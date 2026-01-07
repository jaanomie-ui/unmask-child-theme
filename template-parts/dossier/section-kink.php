<?php
/**
 * Dossier Section: Kink Profile
 *
 * Connection-gated: Only visible to connected users (friends) or self.
 * Privacy reminder shown to profile owner.
 *
 * xProfile fields used:
 *   - Dominance & Submission Orientation (selectbox)
 *   - Kink interests (multiselectbox)
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

$displayed_user_id = bp_displayed_user_id();
$is_own_profile = bp_is_my_profile();
$current_user_id = get_current_user_id();

// Check connection status for viewing others' profiles
$is_connected = false;
if (!$is_own_profile && $current_user_id > 0) {
    $is_connected = unmask_are_users_connected($current_user_id, $displayed_user_id);
}

$has_kink = unmask_has_kink_profile($displayed_user_id);
$kink = $has_kink ? unmask_get_kink_profile($displayed_user_id) : null;

// Determine if kink profile should be visible
// Visible if: own profile OR connected user
$can_view_kink = $is_own_profile || $is_connected;
?>

<section class="dossier-section dossier-section--kink">
    <div class="dossier-section__head">
        <span class="dossier-section__title">kink profile</span>
        <?php if ($is_own_profile && $has_kink) : ?>
            <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'profile/edit/group/3/'); ?>" class="dossier-action">edit</a>
        <?php endif; ?>
    </div>

    <?php if (!$can_view_kink) : ?>
        <!-- Connection gate for non-connected users -->
        <div class="dossier-kink-gate">
            <div class="dossier-kink-gate__icon">&#128274;</div>
            <p class="dossier-kink-gate__text">kink profiles are only visible to connections</p>
            <?php if ($current_user_id > 0) : ?>
                <a href="<?php echo esc_url(bp_displayed_user_domain()); ?>" class="dossier-action dossier-action--primary">add connection</a>
            <?php else : ?>
                <a href="<?php echo esc_url(wp_login_url(bp_displayed_user_domain())); ?>" class="dossier-action dossier-action--primary">log in to connect</a>
            <?php endif; ?>
        </div>

    <?php elseif ($has_kink && $kink) : ?>
        <!-- Kink profile content -->
        <div class="dossier-data-list">
            <?php if (!empty($kink['orientation'])) : ?>
                <div class="dossier-data-row">
                    <span class="dossier-data__key">orientation</span>
                    <span class="dossier-data__val"><?php echo esc_html($kink['orientation']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($kink['interests'])) : ?>
                <div class="dossier-data-row">
                    <span class="dossier-data__key">interests</span>
                    <span class="dossier-data__val">
                        <?php echo esc_html(is_array($kink['interests']) ? implode(', ', $kink['interests']) : $kink['interests']); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($is_own_profile) : ?>
            <!-- Privacy reminder for profile owner -->
            <div class="dossier-kink-privacy">
                <span class="dossier-kink-privacy__icon">&#128274;</span>
                <span class="dossier-kink-privacy__text">only your connections can see this. you never have to share this with anyone.</span>
            </div>
        <?php endif; ?>

    <?php elseif ($is_own_profile) : ?>
        <!-- Prompt to create kink profile -->
        <div class="dossier-kink-prompt">
            <p class="dossier-kink-prompt__text">do you choose to create a kink profile?</p>
            <p class="dossier-kink-prompt__privacy">only your connections can see it. you never have to share this with anyone.</p>
            <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'profile/edit/group/3/'); ?>" class="dossier-action dossier-action--primary">create</a>
        </div>

    <?php else : ?>
        <!-- Connected but no kink profile created -->
        <div class="dossier-empty">no kink profile shared</div>
    <?php endif; ?>
</section>
