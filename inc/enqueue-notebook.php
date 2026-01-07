<?php
/**
 * Enqueue: Dossier Notebook v4
 *
 * Conditionally loads notebook assets on BuddyBoss profile edit pages.
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

/**
 * Check if we're on a profile edit page
 */
function unmask_is_notebook_page() {
    // BuddyBoss profile edit screens
    if (function_exists('bp_is_user_profile_edit') && bp_is_user_profile_edit()) {
        return true;
    }

    // BuddyBoss profile with ?edit or ?dossier parameter
    if (function_exists('bp_is_user_profile') && bp_is_user_profile()) {
        if (isset($_GET['edit']) || isset($_GET['dossier'])) {
            return true;
        }
    }

    // On own profile viewing profile tab
    if (function_exists('bp_is_my_profile') && bp_is_my_profile()) {
        if (function_exists('bp_is_user_profile') && bp_is_user_profile()) {
            return true;
        }
    }

    // Custom dossier endpoint
    if (function_exists('bp_current_component') && function_exists('bp_current_action')) {
        $current = bp_current_component();
        $action = bp_current_action();
        if ($current === 'profile' && ($action === 'edit' || $action === 'dossier')) {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue notebook assets on profile pages
 */
function unmask_enqueue_notebook_assets() {
    // Only for logged-in users
    if (!is_user_logged_in()) {
        return;
    }

    // Check if on notebook page OR if shortcode will be used
    $is_notebook_page = unmask_is_notebook_page();

    // Also enqueue on any BuddyPress member page (we'll conditionally show)
    $is_member_page = function_exists('bp_is_user') && bp_is_user();

    if (!$is_notebook_page && !$is_member_page) {
        return;
    }

    $theme_uri = get_stylesheet_directory_uri();
    $theme_dir = get_stylesheet_directory();

    // CSS
    $css_file = $theme_dir . '/assets/css/pages/dossier-notebook.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'unmask-notebook',
            $theme_uri . '/assets/css/pages/dossier-notebook.css',
            ['buddyboss-theme-css'],
            filemtime($css_file)
        );
    }

    // JS
    $js_file = $theme_dir . '/assets/js/dossier-notebook.js';
    if (file_exists($js_file)) {
        wp_enqueue_script(
            'unmask-notebook',
            $theme_uri . '/assets/js/dossier-notebook.js',
            [],
            filemtime($js_file),
            true
        );

        // Pass data to JS
        wp_localize_script('unmask-notebook', 'unmaskNotebook', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('unmask_notebook_nonce'),
            'userId' => get_current_user_id(),
            'maxCustomFields' => defined('UNMASK_MAX_CUSTOM_FIELDS') ? UNMASK_MAX_CUSTOM_FIELDS : 5,
        ]);
    }
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_notebook_assets', 20);

/**
 * Shortcode to display the notebook
 * Usage: [unmask_notebook] or [dossier_notebook]
 */
function unmask_notebook_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to edit your dossier.</p>';
    }

    ob_start();

    $template_file = get_stylesheet_directory() . '/template-parts/dossier/notebook.php';
    if (file_exists($template_file)) {
        include $template_file;
    } else {
        echo '<p>Notebook template not found.</p>';
    }

    return ob_get_clean();
}
add_shortcode('unmask_notebook', 'unmask_notebook_shortcode');
add_shortcode('dossier_notebook', 'unmask_notebook_shortcode');

/**
 * Hook into BuddyBoss profile edit to show notebook
 * This runs on the profile/edit screen
 */
function unmask_maybe_show_notebook_on_profile_edit() {
    // Only on profile edit
    if (!function_exists('bp_is_user_profile_edit') || !bp_is_user_profile_edit()) {
        return;
    }

    // Only on own profile
    if (!function_exists('bp_is_my_profile') || !bp_is_my_profile()) {
        return;
    }

    // Add notebook before the edit form
    add_action('bp_before_profile_edit_content', 'unmask_render_notebook_ui', 5);

    // Hide the default form with CSS
    add_action('wp_head', function() {
        echo '<style>
            /* Hide BuddyBoss default profile edit forms */
            #profile-edit-form,
            .bp-profile-edit,
            .profile-content form,
            .bp-profile-content .profile form,
            #buddypress form.profile-form,
            #buddypress .profile-edit,
            .bb-profile-body form.profile-form,
            .profile .editfield {
                display: none !important;
            }
        </style>';
    });
}
add_action('bp_actions', 'unmask_maybe_show_notebook_on_profile_edit', 10);

/**
 * Render the notebook UI
 */
function unmask_render_notebook_ui() {
    $template_file = get_stylesheet_directory() . '/template-parts/dossier/notebook.php';
    if (file_exists($template_file)) {
        include $template_file;
    }
}

/**
 * Add "Edit Dossier" link to profile navigation
 */
function unmask_add_dossier_nav_item() {
    if (!function_exists('bp_core_new_subnav_item') || !is_user_logged_in()) {
        return;
    }

    // Only add to own profile
    if (!bp_is_my_profile()) {
        return;
    }

    bp_core_new_subnav_item([
        'name' => 'Dossier',
        'slug' => 'dossier',
        'parent_url' => bp_loggedin_user_domain() . 'profile/',
        'parent_slug' => 'profile',
        'screen_function' => 'unmask_dossier_screen',
        'position' => 5,
        'user_has_access' => bp_is_my_profile(),
    ]);
}
add_action('bp_setup_nav', 'unmask_add_dossier_nav_item', 100);

/**
 * Screen function for dossier subnav
 */
function unmask_dossier_screen() {
    add_action('bp_template_content', 'unmask_render_notebook_ui');
    bp_core_load_template('members/single/plugins');
}
