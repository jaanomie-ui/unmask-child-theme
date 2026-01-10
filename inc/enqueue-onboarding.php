<?php
/**
 * UNMASK Onboarding Enqueue & Functions
 *
 * Handles asset loading for the streamlined 2-step onboarding flow:
 * 1. Register (email + password) - MemberPress
 * 2. Welcome + Guided Profile Setup - page-welcome.php
 *
 * @package BuddyBoss_Child
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue onboarding styles and scripts on welcome page
 */
function unmask_enqueue_onboarding_assets() {
    // Only load on welcome page
    if (!is_page_template('page-templates/page-welcome.php')) {
        return;
    }

    // Base onboarding styles
    wp_enqueue_style(
        'unmask-onboarding',
        get_stylesheet_directory_uri() . '/assets/css/onboarding.css',
        array('unmask-00-design-system'),
        filemtime(get_stylesheet_directory() . '/assets/css/onboarding.css')
    );

    // Bottom sheet component
    wp_enqueue_style(
        'unmask-bottom-sheet',
        get_stylesheet_directory_uri() . '/assets/css/components/bottom-sheet.css',
        array('unmask-00-design-system'),
        filemtime(get_stylesheet_directory() . '/assets/css/components/bottom-sheet.css')
    );

    // Profile setup sheet styles
    wp_enqueue_style(
        'unmask-profile-sheet',
        get_stylesheet_directory_uri() . '/assets/css/components/profile-sheet.css',
        array('unmask-bottom-sheet'),
        filemtime(get_stylesheet_directory() . '/assets/css/components/profile-sheet.css')
    );

    // Profile setup JavaScript
    wp_enqueue_script(
        'unmask-profile-setup',
        get_stylesheet_directory_uri() . '/assets/js/profile-setup.js',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/js/profile-setup.js'),
        true
    );

    // Localize script with config
    wp_localize_script('unmask-profile-setup', 'unmaskProfileSetup', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('unmask_profile_setup'),
        'redirectUrl' => home_url('/'),
        'practiceOptions' => unmask_get_practice_options(),
    ));
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_onboarding_assets');

/**
 * Get profile completion percentage for onboarding
 *
 * @param int $user_id User ID
 * @return int Completion percentage (0-100)
 */
function unmask_get_onboarding_completion($user_id) {
    if (!$user_id) {
        return 0;
    }

    // Use the existing comprehensive profile completion function from dossier
    if (function_exists('unmask_get_profile_completion')) {
        $result = unmask_get_profile_completion($user_id);
        if (is_array($result) && isset($result['percentage'])) {
            return intval($result['percentage']);
        }
        if (is_numeric($result)) {
            return intval($result);
        }
    }

    // Fallback: simple check for scene name
    if (function_exists('xprofile_get_field_data')) {
        $scene_name = xprofile_get_field_data('Scene Name', $user_id);
        if (!empty($scene_name)) {
            return 50;
        }
    }

    return 0;
}

/**
 * Redirect after MemberPress registration to welcome page
 */
function unmask_memberpress_registration_redirect($txn) {
    if ($txn && $txn->user_id) {
        // Clear any existing onboarding state for fresh start
        delete_user_meta($txn->user_id, 'unmask_onboarding_complete');
        delete_user_meta($txn->user_id, 'unmask_onboarding_step');

        // Redirect to welcome
        wp_redirect(home_url('/welcome/'));
        exit;
    }
}
add_action('mepr-signup', 'unmask_memberpress_registration_redirect', 10);

/**
 * Set initial onboarding state on new user registration
 */
function unmask_onboarding_user_register($user_id) {
    if ($user_id) {
        // Clear any existing onboarding state
        delete_user_meta($user_id, 'unmask_onboarding_complete');
        delete_user_meta($user_id, 'unmask_onboarding_step');
    }
}
add_action('user_register', 'unmask_onboarding_user_register', 10);
