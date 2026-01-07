<?php
/**
 * Mobile Bottom Navigation
 *
 * Sticky 5-item navigation bar for mobile devices.
 * Shows: Home, Magazine, ISO, Events, Dossier
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

// Determine active state
$current_url = $_SERVER['REQUEST_URI'];
$is_home = is_front_page() || is_home();
$is_magazine = is_post_type_archive('post') || is_singular('post') || strpos($current_url, '/magazine') !== false;
$is_iso = strpos($current_url, '/iso') !== false;
$is_events = strpos($current_url, '/events') !== false || strpos($current_url, '/calendar') !== false;
$is_dossier = function_exists('bp_is_user') && bp_is_user();

// URLs - adjust these to match your site structure
$home_url = home_url('/');
$magazine_url = home_url('/magazine/');
$iso_url = home_url('/iso-board/');
$events_url = home_url('/events/');
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
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label">home</span>
    </a>

    <a href="<?php echo esc_url($magazine_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_magazine ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label">magazine</span>
    </a>

    <a href="<?php echo esc_url($iso_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_iso ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label">iso</span>
    </a>

    <a href="<?php echo esc_url($events_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_events ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label">events</span>
    </a>

    <a href="<?php echo esc_url($dossier_url); ?>"
       class="unmask-bottom-nav__item <?php echo $is_dossier ? 'is-active' : ''; ?>">
        <span class="unmask-bottom-nav__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </span>
        <span class="unmask-bottom-nav__label"><?php echo esc_html($dossier_label); ?></span>
    </a>
</nav>
