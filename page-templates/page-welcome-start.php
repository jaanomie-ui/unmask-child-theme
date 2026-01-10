<?php
/**
 * Template Name: Welcome Start
 * Template Post Type: page
 *
 * UNMASK Onboarding Screen 3: Start
 * Directs user to complete their dossier with progress indicator.
 *
 * @package BuddyBoss_Child
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/register-visitor/'));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Check if profile is already complete - if so, redirect to Screen 4
$profile_completion = unmask_get_onboarding_completion($user_id);
if ($profile_completion >= 100) {
    wp_redirect(home_url('/welcome/complete/'));
    exit;
}

// Mark Screen 2 as complete if not already
$onboarding_step = get_user_meta($user_id, 'unmask_onboarding_step', true);
if (!$onboarding_step || intval($onboarding_step) < 3) {
    update_user_meta($user_id, 'unmask_onboarding_step', 3);
}

// Generate visitor designation
$designation = 'V-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);

// Get profile edit URL (BuddyBoss)
$profile_edit_url = function_exists('bp_loggedin_user_domain')
    ? bp_loggedin_user_domain() . 'profile/edit/'
    : home_url('/members/' . $current_user->user_nicename . '/profile/edit/');

get_header('fullbleed');
?>

<div class="unmask-onboarding unmask-onboarding--screen-3">

    <!-- Ambient Background -->
    <div class="unmask-ambient">
        <div class="unmask-ambient__blob unmask-ambient__blob--red"></div>
        <div class="unmask-ambient__blob unmask-ambient__blob--green"></div>
    </div>

    <!-- Header -->
    <header class="unmask-onboarding__header">
        <span class="unmask-onboarding__step">STEP 3 OF 4 — YOUR DOSSIER</span>
        <span class="unmask-onboarding__designation"><?php echo esc_html($designation); ?></span>
    </header>

    <!-- Main Content -->
    <main class="unmask-onboarding__body unmask-onboarding__body--centered">

        <div class="unmask-onboarding__dossier">
            <h1 class="unmask-onboarding__dossier-headline">START WITH YOUR DOSSIER</h1>

            <p class="unmask-onboarding__dossier-text">
                Your dossier is how others find you. Operators seeking subjects. Collaborators. The system works when you are visible.
            </p>

            <!-- Progress Bar -->
            <div class="unmask-progress">
                <div class="unmask-progress__label">
                    <span>DOSSIER COMPLETION</span>
                    <span><?php echo esc_html($profile_completion); ?>%</span>
                </div>
                <div class="unmask-progress__bar">
                    <div class="unmask-progress__fill" style="width: <?php echo esc_attr($profile_completion); ?>%;"></div>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="unmask-onboarding__footer">
        <a href="<?php echo esc_url($profile_edit_url); ?>" class="unmask-onboarding__cta unmask-onboarding__cta--primary" data-onboarding-action="dossier">
            [complete dossier]
        </a>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="unmask-onboarding__skip">explore first</a>
    </footer>

</div>

<script>
(function() {
    'use strict';

    // Set cookie to track that user reached dossier step
    document.cookie = 'unmask_onboarding_step=3; path=/; max-age=' + (60*60*24*30);
})();
</script>

<?php get_footer('fullbleed'); ?>
