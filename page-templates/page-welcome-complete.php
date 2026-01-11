<?php
/**
 * Template Name: Welcome Complete
 * Template Post Type: page
 *
 * UNMASK Onboarding Screen 4: Complete
 * Shows unlocked actions after profile completion.
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

// Check profile completion - if not complete, redirect back to Screen 3
$profile_completion = unmask_get_onboarding_completion($user_id);
if ($profile_completion < 100) {
    // Still show the page but with a notice that dossier is incomplete
    $incomplete = true;
} else {
    $incomplete = false;
}

// Mark onboarding as complete
update_user_meta($user_id, 'unmask_onboarding_step', 4);
update_user_meta($user_id, 'unmask_onboarding_complete', true);

// Generate visitor designation
$designation = 'V-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);

// Get next Pink Panthers event date (placeholder - would pull from events CPT)
$next_event = 'TBD';

// Unlocked actions
$unlocked_actions = array(
    array(
        'name' => 'THE FACTORY',
        'desc' => 'Book studio time. $40/hour.',
        'url' => home_url('/the-factory/'),
        'cta' => '[book]',
    ),
    array(
        'name' => 'ISO BOARD',
        'desc' => 'Post what you are seeking.',
        'url' => home_url('/iso-board/'),
        'cta' => '[post]',
    ),
    array(
        'name' => 'SUBMIT',
        'desc' => 'Send work for the archive.',
        'url' => home_url('/submit/'),
        'cta' => '[submit]',
    ),
    array(
        'name' => 'PINK PANTHERS',
        'desc' => 'Next event: ' . $next_event,
        'url' => home_url('/pink-panthers/'),
        'cta' => '[tickets]',
    ),
);

get_header('fullbleed');
?>

<div class="unmask-onboarding unmask-onboarding--screen-4">

    <!-- Ambient Background -->
    <div class="unmask-ambient">
        <div class="unmask-ambient__blob unmask-ambient__blob--red"></div>
        <div class="unmask-ambient__blob unmask-ambient__blob--green"></div>
    </div>

    <!-- Header -->
    <header class="unmask-onboarding__header">
        <span class="unmask-onboarding__step">STEP 4 OF 4 — UNLOCKED</span>
        <span class="unmask-onboarding__designation"><?php echo esc_html($designation); ?></span>
    </header>

    <!-- Main Content -->
    <main class="unmask-onboarding__body unmask-onboarding__body--centered">

        <div class="unmask-onboarding__unlocked">

            <div class="unmask-onboarding__unlocked-header">
                <?php if ($incomplete) : ?>
                    <p class="unmask-onboarding__unlocked-check" style="color: var(--primitive-amber);">◐ DOSSIER INCOMPLETE</p>
                    <h1 class="unmask-onboarding__unlocked-headline">COMPLETE YOUR DOSSIER TO UNLOCK ALL FEATURES</h1>
                <?php else : ?>
                    <p class="unmask-onboarding__unlocked-check">✓ DOSSIER COMPLETE</p>
                    <h1 class="unmask-onboarding__unlocked-headline">YOU ARE NOW VISIBLE</h1>
                <?php endif; ?>
            </div>

            <!-- Unlocked Actions List -->
            <ul class="unmask-unlocked-list">
                <?php foreach ($unlocked_actions as $action) : ?>
                    <li class="unmask-unlocked-list__item">
                        <div class="unmask-unlocked-list__info">
                            <div class="unmask-unlocked-list__name"><?php echo esc_html($action['name']); ?></div>
                            <div class="unmask-unlocked-list__desc"><?php echo esc_html($action['desc']); ?></div>
                        </div>
                        <a href="<?php echo esc_url($action['url']); ?>" class="unmask-unlocked-list__action">
                            <?php echo esc_html($action['cta']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>

    </main>

    <!-- Footer -->
    <footer class="unmask-onboarding__footer">
        <?php if ($incomplete) : ?>
            <?php
            $profile_edit_url = function_exists('bp_loggedin_user_domain')
                ? bp_loggedin_user_domain() . 'profile/edit/'
                : home_url('/members/' . $current_user->user_nicename . '/profile/edit/');
            ?>
            <a href="<?php echo esc_url($profile_edit_url); ?>" class="unmask-onboarding__cta unmask-onboarding__cta--primary">
                [complete dossier]
            </a>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="unmask-onboarding__skip">enter portal anyway</a>
        <?php else : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="unmask-onboarding__cta unmask-onboarding__cta--primary">
                [enter portal]
            </a>
        <?php endif; ?>
    </footer>

</div>

<script>
(function() {
    'use strict';

    // Clear onboarding cookie - flow complete
    document.cookie = 'unmask_onboarding_step=4; path=/; max-age=' + (60*60*24*30);
    document.cookie = 'unmask_onboarding_complete=1; path=/; max-age=' + (60*60*24*365);
})();
</script>

<?php get_footer('fullbleed'); ?>
