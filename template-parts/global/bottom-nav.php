<?php
/**
 * Mobile Bottom Navigation
 *
 * Sticky 5-item navigation bar for mobile devices.
 * Shows: Home, Magazine, ISO, Submit, Dossier
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

// Determine active state
$current_url = $_SERVER['REQUEST_URI'];
$is_home = is_front_page() || is_home();
$is_magazine = is_post_type_archive('post') || is_singular('post') || strpos($current_url, '/the-archive') !== false;
$is_iso = strpos($current_url, '/iso') !== false;
$is_submit = strpos($current_url, '/submit') !== false || strpos($current_url, '/post-iso') !== false;
$is_dossier = function_exists('bp_is_user') && bp_is_user();

// URLs - adjust these to match your site structure
$home_url = home_url('/');
$magazine_url = home_url('/the-archive/');
$iso_url = home_url('/iso-board/');
$submit_url = home_url('/submit/');
$dossier_url = is_user_logged_in() && function_exists('bp_loggedin_user_domain')
    ? bp_loggedin_user_domain()
    : home_url('/register/');

// Labels
$dossier_label = is_user_logged_in() ? 'dossier' : 'join';
?>

<nav class="unmask-bottom-nav" aria-label="Primary mobile navigation">
    <a href="<?php echo esc_url($home_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_home ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <!-- Home: Sharp angular house -->
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter">
                <path d="M3 10L12 3L21 10V21H15V14H9V21H3V10Z"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label">home</span>
    </a>

    <a href="<?php echo esc_url($magazine_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_magazine ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <!-- Magazine: Sharp stacked pages -->
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter">
                <path d="M4 4H20V20H4V4Z"/>
                <path d="M4 8H20"/>
                <path d="M8 8V20"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label">magazine</span>
    </a>

    <a href="<?php echo esc_url($iso_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_iso ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <!-- ISO: Crosshair/target - seeking connection -->
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter">
                <path d="M12 3V7"/>
                <path d="M12 17V21"/>
                <path d="M3 12H7"/>
                <path d="M17 12H21"/>
                <path d="M8 8H16V16H8V8Z"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label">iso</span>
    </a>

    <a href="<?php echo esc_url($submit_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_submit ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <!-- Submit: Terminal cursor/prompt -->
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter">
                <path d="M4 4L12 12L4 20"/>
                <path d="M14 20H20"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label">submit</span>
    </a>

    <a href="<?php echo esc_url($dossier_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_dossier ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <!-- Dossier/Join: Angular eye (seeing/being seen — fits "unmask") -->
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter">
                <path d="M2 12L12 5L22 12L12 19L2 12Z"/>
                <path d="M9 12H15"/>
                <path d="M12 9V15"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label"><?php echo esc_html($dossier_label); ?></span>
    </a>
</nav>
