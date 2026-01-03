<?php
/**
 * Template Name: Welcome Onboarding
 * Template Post Type: page
 *
 * UNMASK Welcome/Onboarding page shown after registration.
 * Displays visitor designation and onboarding options.
 *
 * @package BuddyBoss_Child
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/register/visitor/'));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Generate visitor designation (V-XXX format)
$designation = 'V-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);

// Get user's function(s) from meta
$user_functions = get_user_meta($user_id, 'unmask_function', true);
if (!is_array($user_functions)) {
    $user_functions = array();
}

// Get scene name (display name)
$scene_name = $current_user->display_name;

get_header('fullbleed');
?>

<div class="welcome-page">

    <!-- System Bar -->
    <header class="system-bar">
        <div class="system-bar-left">
            <span class="system-logo">UNMASK</span>
            <span class="system-path">/ welcome</span>
        </div>
        <div class="system-bar-right">
            <span class="system-status">
                <span class="status-dot status-dot-active"></span>
                <?php echo esc_html($designation); ?> online
            </span>
        </div>
    </header>

    <!-- Welcome Content -->
    <main class="welcome-frame">

        <div class="welcome-header">
            <p class="welcome-label">Welcome Visitor, you have been assigned the identification number</p>
            <h1 class="welcome-designation"><?php echo esc_html($designation); ?></h1>

            <?php if (!empty($user_functions)) : ?>
                <div class="welcome-functions">
                    <?php foreach ($user_functions as $function) : ?>
                        <span class="badge badge-neutral"><?php echo esc_html(strtoupper($function)); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <hr class="divider">

        <div class="welcome-prompt">
            <p class="welcome-prompt-text">Choose your next steps.</p>
        </div>

        <!-- Onboarding Cards -->
        <div class="onboarding-grid">

            <!-- Card 1: Archive -->
            <a href="<?php echo esc_url(home_url('/archive/')); ?>" class="onboarding-card">
                <div class="card-header">
                    <span class="card-header-title">the archive</span>
                    <span class="card-header-icon">→</span>
                </div>
                <div class="card-body">
                    <p class="card-text">Documenting the stories of artists, worldbuilders, and misfits.</p>
                </div>
                <div class="card-footer">
                    <span class="card-cta">[enter]</span>
                </div>
            </a>

            <!-- Card 2: Dossier -->
            <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'profile/edit/'); ?>" class="onboarding-card">
                <div class="card-header">
                    <span class="card-header-title">your dossier</span>
                    <span class="card-header-icon">→</span>
                </div>
                <div class="card-body">
                    <p class="card-text">Upload your data. What makes you different from the rest?</p>
                </div>
                <div class="card-footer">
                    <span class="card-cta">[build]</span>
                </div>
            </a>

            <!-- Card 3: Factory -->
            <a href="<?php echo esc_url(home_url('/factory/')); ?>" class="onboarding-card">
                <div class="card-header">
                    <span class="card-header-title">the Factory</span>
                    <span class="card-header-icon">→</span>
                </div>
                <div class="card-body">
                    <p class="card-text">Build inside of the Factory, where dreams are manufactured. We document your progress.</p>
                </div>
                <div class="card-footer">
                    <span class="card-cta">[explore]</span>
                </div>
            </a>

        </div>

        <!-- System Footer -->
        <div class="welcome-footer">
            <p class="welcome-footer-text">Your dossier is empty. The archive is open.</p>
            <p class="welcome-footer-text">What happens next is yours to determine.</p>
        </div>

    </main>

</div>

<?php get_footer('fullbleed'); ?>
