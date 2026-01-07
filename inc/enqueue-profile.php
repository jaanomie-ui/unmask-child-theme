<?php
/**
 * Enqueue Profile Page Assets
 *
 * @package unmask-child-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue profile page styles
 */
function unmask_enqueue_profile_assets() {
    if ( ! function_exists( 'bp_is_user' ) || ! bp_is_user() ) {
        return;
    }

    wp_enqueue_style(
        'unmask-profile',
        get_stylesheet_directory_uri() . '/assets/css/pages/profile.css',
        array( 'buddyboss-theme-css' ),
        filemtime( get_stylesheet_directory() . '/assets/css/pages/profile.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'unmask_enqueue_profile_assets', 20 );

/**
 * Force-expand all profile sections via inline JS
 */
function unmask_force_expand_profile_sections() {
    if ( ! function_exists( 'bp_is_user' ) || ! bp_is_user() ) {
        return;
    }
    ?>
    <script>
    (function() {
        function forceExpand() {
            document.querySelectorAll('.group-separator-block').forEach(function(el) {
                el.classList.remove('is-collapsed', 'collapsed', 'closed', 'hidden');
                el.classList.add('is-expanded', 'expanded', 'open');
                el.style.cssText = 'display:block!important;visibility:visible!important;opacity:1!important;max-height:9999px!important;height:auto!important;overflow:visible!important;';
            });
            document.querySelectorAll('.bp-widget').forEach(function(el) {
                el.classList.remove('is-collapsed', 'collapsed', 'closed', 'hidden');
                el.style.cssText = 'display:block!important;visibility:visible!important;opacity:1!important;max-height:9999px!important;height:auto!important;overflow:visible!important;';
            });
            document.querySelectorAll('.profile-fields').forEach(function(el) {
                el.style.cssText = 'display:table!important;visibility:visible!important;opacity:1!important;';
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', forceExpand);
        } else {
            forceExpand();
        }
        setTimeout(forceExpand, 100);
        setTimeout(forceExpand, 500);
        setTimeout(forceExpand, 1000);
        setTimeout(forceExpand, 2000);
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'unmask_force_expand_profile_sections', 999 );
