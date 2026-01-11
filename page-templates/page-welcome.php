<?php
/**
 * Template Name: Welcome Onboarding
 * Template Post Type: page
 *
 * UNMASK Onboarding - Streamlined 2-step flow:
 * 1. Register (email + password) - handled by MemberPress
 * 2. Welcome + Guided Profile Setup (this template)
 *
 * Uses bottom sheet UI for mobile-first profile collection.
 * Saves directly to BuddyBoss xprofile fields.
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

// Check if onboarding already complete
$onboarding_complete = get_user_meta($user_id, 'unmask_onboarding_complete', true);
if ($onboarding_complete) {
    wp_redirect(home_url('/'));
    exit;
}

// Get or generate designation
$designation = unmask_get_designation($user_id);

// Get practice options for the multiselect
$practice_options = unmask_get_practice_options();

get_header('fullbleed');
?>

<div class="unmask-onboarding unmask-onboarding--welcome">

    <!-- Ambient Background -->
    <div class="unmask-ambient">
        <div class="unmask-ambient__blob unmask-ambient__blob--red"></div>
        <div class="unmask-ambient__blob unmask-ambient__blob--green"></div>
    </div>

    <!-- Main Content -->
    <main class="unmask-onboarding__body">

        <div class="unmask-onboarding__welcome-content">
            <!-- Large Designation (spans col 1.5 to 2.5) -->
            <h1 class="unmask-onboarding__designation-large"><?php echo esc_html($designation); ?></h1>

            <!-- Welcome Title (spans col 1.5 to 2.5) -->
            <p class="unmask-onboarding__welcome-label">welcome to unmask</p>

            <!-- Body Content (confined to col 2) -->
            <div class="unmask-onboarding__welcome-body">
                <p class="unmask-onboarding__tagline">a living archive of artists, misfits, and the people who document them.</p>

                <div class="unmask-onboarding__definition">
                    <span class="unmask-onboarding__term">dossier</span>
                    <span class="unmask-onboarding__phonetic">/ˈdäsēˌā/</span>
                    <span class="unmask-onboarding__meaning">— a file containing detailed records about a person.</span>
                </div>

                <p class="unmask-onboarding__context">your dossier is empty.</p>

                <!-- Actions flow naturally after content -->
                <div class="unmask-onboarding__actions">
                    <button type="button" class="unmask-onboarding__cta unmask-onboarding__cta--primary" data-profile-setup-start>
                        set up dossier
                    </button>
                    <button type="button" class="unmask-onboarding__skip" data-profile-setup-skip>
                        skip for now
                    </button>
                </div>
            </div>
        </div>

    </main>

</div>

<!-- Profile Setup Bottom Sheet -->
<div class="profile-setup__scrim unmask-sheet__scrim"></div>
<div class="profile-setup__panel unmask-sheet__panel">

    <!-- Drag Handle -->
    <div class="unmask-sheet__handle"></div>

    <!-- Header -->
    <header class="unmask-sheet__header">
        <h2 class="unmask-sheet__title">set up your dossier</h2>
        <button type="button" class="profile-setup__close unmask-sheet__close" aria-label="Close">&times;</button>
    </header>

    <!-- Body -->
    <div class="unmask-sheet__body">

        <!-- Progress -->
        <div class="profile-setup__progress">
            <span class="profile-setup__dot profile-setup__dot--active"></span>
            <span class="profile-setup__dot"></span>
            <span class="profile-setup__dot"></span>
        </div>

        <!-- Section 1: Identity -->
        <div class="profile-setup__section is-active" data-section="0" style="display: block;">
            <div class="profile-setup__section-header">
                <span class="profile-setup__section-number">1 of 3</span>
                <h3 class="profile-setup__section-title">Who are you?</h3>
            </div>

            <div class="profile-setup__field">
                <label class="profile-setup__label profile-setup__label--required" for="scene_name">Scene Name</label>
                <input type="text" id="scene_name" name="scene_name" class="profile-setup__input" placeholder="Your scene name or alias" autocomplete="off">
                <div class="profile-setup__error"></div>
            </div>

            <div class="profile-setup__field">
                <label class="profile-setup__label" for="pronouns">Pronouns</label>
                <input type="text" id="pronouns" name="pronouns" class="profile-setup__input" placeholder="e.g., they/them, she/her" autocomplete="off">
            </div>

            <div class="profile-setup__actions">
                <button type="button" class="profile-setup__next" data-profile-next>next</button>
                <button type="button" class="profile-setup__skip" data-profile-skip-section>skip this</button>
            </div>
        </div>

        <!-- Section 2: Practice -->
        <div class="profile-setup__section" data-section="1" style="display: none;">
            <div class="profile-setup__section-header">
                <span class="profile-setup__section-number">2 of 3</span>
                <h3 class="profile-setup__section-title">What do you practice?</h3>
                <p class="profile-setup__section-hint">Select all that apply</p>
            </div>

            <div class="profile-setup__options">
                <?php foreach ($practice_options as $option) : ?>
                    <button type="button" class="profile-setup__option" data-value="<?php echo esc_attr($option); ?>">
                        <span class="profile-setup__option-check"></span>
                        <span><?php echo esc_html($option); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="profile-setup__actions">
                <button type="button" class="profile-setup__next" data-profile-next>next</button>
                <button type="button" class="profile-setup__skip" data-profile-skip-section>skip this</button>
            </div>
        </div>

        <!-- Section 3: Status -->
        <div class="profile-setup__section" data-section="2" style="display: none;">
            <div class="profile-setup__section-header">
                <span class="profile-setup__section-number">3 of 3</span>
                <h3 class="profile-setup__section-title">Your status</h3>
                <p class="profile-setup__section-hint">Let others know what you're open to</p>
            </div>

            <div class="profile-setup__toggles">
                <div class="profile-setup__toggle" data-field="available_photographed">
                    <div class="profile-setup__switch"></div>
                    <div class="profile-setup__toggle-info">
                        <span class="profile-setup__toggle-label">Available to be photographed</span>
                        <span class="profile-setup__toggle-hint">Show in photographer searches</span>
                    </div>
                </div>

                <div class="profile-setup__toggle" data-field="available_photograph">
                    <div class="profile-setup__switch"></div>
                    <div class="profile-setup__toggle-info">
                        <span class="profile-setup__toggle-label">Available to photograph</span>
                        <span class="profile-setup__toggle-hint">Looking for subjects</span>
                    </div>
                </div>

                <div class="profile-setup__toggle" data-field="open_bookings">
                    <div class="profile-setup__switch"></div>
                    <div class="profile-setup__toggle-info">
                        <span class="profile-setup__toggle-label">Open to bookings</span>
                        <span class="profile-setup__toggle-hint">Accept paid work</span>
                    </div>
                </div>
            </div>

            <div class="profile-setup__actions">
                <button type="button" class="profile-setup__next" data-profile-next>finish</button>
                <button type="button" class="profile-setup__skip" data-profile-skip-section>skip this</button>
            </div>
        </div>

        <!-- Completion -->
        <div class="profile-setup__complete" style="display: none;">
            <div class="profile-setup__complete-icon">&#10003;</div>
            <h3 class="profile-setup__complete-title">Dossier complete</h3>
            <p class="profile-setup__complete-text">Your dossier is ready. You can update it anytime from your profile.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="profile-setup__complete-cta">enter unmask</a>
        </div>

    </div>

</div>

<script>
// Inline initialization backup - ensures sheet works even if external JS fails to load
(function() {
    'use strict';

    var scrim = document.querySelector('.profile-setup__scrim');
    var panel = document.querySelector('.profile-setup__panel');
    var startBtn = document.querySelector('[data-profile-setup-start]');
    var skipBtn = document.querySelector('[data-profile-setup-skip]');
    var closeBtn = document.querySelector('.profile-setup__close');

    console.log('[UNMASK] Profile setup init:', { scrim: !!scrim, panel: !!panel, startBtn: !!startBtn });

    function openSheet(e) {
        if (e) e.preventDefault();
        console.log('[UNMASK] Opening sheet');
        if (scrim) scrim.classList.add('is-active');
        if (panel) panel.classList.add('is-active');
        document.body.style.overflow = 'hidden';
    }

    function closeSheet() {
        console.log('[UNMASK] Closing sheet');
        if (scrim) scrim.classList.remove('is-active');
        if (panel) panel.classList.remove('is-active');
        document.body.style.overflow = '';
    }

    if (startBtn) {
        startBtn.addEventListener('click', openSheet);
        console.log('[UNMASK] Click handler attached to start button');
    }

    if (skipBtn) {
        skipBtn.addEventListener('click', function() {
            window.location.href = '<?php echo esc_url(home_url('/')); ?>';
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', closeSheet);
    if (scrim) scrim.addEventListener('click', closeSheet);
})();
</script>

<?php get_footer('fullbleed'); ?>
