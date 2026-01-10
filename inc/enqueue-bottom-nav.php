<?php
/**
 * Enqueue: Mobile Bottom Navigation
 *
 * Loads bottom nav assets and renders the navigation component.
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue bottom nav CSS
 */
function unmask_enqueue_bottom_nav_assets() {
    $theme_uri = get_stylesheet_directory_uri();
    $theme_dir = get_stylesheet_directory();

    $css_file = $theme_dir . '/assets/css/components/bottom-nav.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'unmask-bottom-nav',
            $theme_uri . '/assets/css/components/bottom-nav.css',
            ['buddyboss-theme-css'],
            filemtime($css_file)
        );
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_bottom_nav_assets', 25);

/**
 * Add body class for bottom nav padding
 */
function unmask_bottom_nav_body_class($classes) {
    // Only add on mobile-relevant pages (always add, CSS handles responsive)
    $classes[] = 'has-bottom-nav';
    return $classes;
}
add_filter('body_class', 'unmask_bottom_nav_body_class');

/**
 * Render bottom nav in footer
 */
function unmask_render_bottom_nav() {
    // Don't render in admin
    if (is_admin()) {
        return;
    }

    // Don't render on login/register pages
    if (in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'])) {
        return;
    }

    // Don't render on onboarding/registration pages
    if (is_page_template('page-templates/page-register-visitor.php') ||
        is_page_template('page-templates/page-welcome.php') ||
        is_page_template('page-templates/page-welcome-orientation.php') ||
        is_page_template('page-templates/page-welcome-start.php') ||
        is_page_template('page-templates/page-welcome-complete.php')) {
        return;
    }

    get_template_part('template-parts/global/bottom-nav');
}
add_action('wp_footer', 'unmask_render_bottom_nav', 100);

/**
 * Optional: Hide-on-scroll JavaScript
 * Uncomment if you want the nav to hide when scrolling down
 */
/*
function unmask_bottom_nav_scroll_script() {
    ?>
    <script>
    (function() {
        let lastScrollY = window.scrollY;
        let ticking = false;
        const nav = document.querySelector('.unmask-bottom-nav');

        if (!nav) return;

        function updateNav() {
            const currentScrollY = window.scrollY;
            const scrollingDown = currentScrollY > lastScrollY;
            const scrolledPast = currentScrollY > 100;

            if (scrollingDown && scrolledPast) {
                nav.classList.add('is-hidden');
            } else {
                nav.classList.remove('is-hidden');
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateNav);
                ticking = true;
            }
        }, { passive: true });
    })();
    </script>
    <?php
}
add_action('wp_footer', 'unmask_bottom_nav_scroll_script', 101);
*/
