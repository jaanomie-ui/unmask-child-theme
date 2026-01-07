<?php
/**
 * Dossier Helper Functions
 *
 * IMPORTANT: Using field IDs instead of names because xprofile_get_field_data
 * has issues with special characters in field names (like ampersand).
 *
 * xProfile field IDs (verified 2025-01-05):
 *   Visitor Profile:
 *     - Scene Name: 3
 *     - Pronouns: 85
 *     - City: 87
 *     - Bio: 88
 *   Creative Practice:
 *     - What do you practice?: 21
 *     - Portfolio: 103
 *     - Available to be photographed: 104
 *     - Available to photograph: 108
 *     - Open to bookings: 111
 *   Kink Profile:
 *     - Dominance & Submission Orientation: 57
 *     - Kink interests: 71
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

// Note: unmask_get_member_type() and unmask_get_designation() are defined in main functions.php

/**
 * Get user's creative practices from xProfile
 */
function unmask_get_user_functions($user_id) {
    // Field ID 21 = "What do you practice?"
    if (function_exists('xprofile_get_field_data')) {
        $functions = xprofile_get_field_data(21, $user_id);
        if (!empty($functions)) {
            return is_array($functions) ? $functions : explode(',', $functions);
        }
    }

    // Fall back to user meta
    $functions = get_user_meta($user_id, 'unmask_functions', true);
    if (!empty($functions)) {
        return is_array($functions) ? $functions : explode(',', $functions);
    }

    return [];
}

/**
 * Get user's status indicators
 */
function unmask_get_user_status($user_id) {
    $bookable = false;
    $available_photographed = false;
    $available_photograph = false;

    if (function_exists('xprofile_get_field_data')) {
        // Field IDs: 111 = Open to bookings, 104 = Available to be photographed, 108 = Available to photograph
        $bookable_val = xprofile_get_field_data(111, $user_id);
        $photographed_val = xprofile_get_field_data(104, $user_id);
        $photograph_val = xprofile_get_field_data(108, $user_id);

        // Checkbox fields return array with value or empty
        $bookable = !empty($bookable_val);
        $available_photographed = !empty($photographed_val);
        $available_photograph = !empty($photograph_val);
    }

    return [
        'bookable' => $bookable,
        'available_photographed' => $available_photographed,
        'available_photograph' => $available_photograph
    ];
}

/**
 * Check if user has created a kink profile
 */
function unmask_has_kink_profile($user_id) {
    if (!function_exists('xprofile_get_field_data')) {
        return false;
    }

    // Field ID 57 = "Dominance & Submission Orientation"
    $orientation = xprofile_get_field_data(57, $user_id);
    return !empty($orientation);
}

/**
 * Get kink profile data
 */
function unmask_get_kink_profile($user_id) {
    if (!function_exists('xprofile_get_field_data')) {
        return null;
    }

    // Field IDs: 57 = D/s Orientation, 71 = Kink interests
    $orientation = xprofile_get_field_data(57, $user_id);
    if (empty($orientation)) {
        return null;
    }

    $interests = xprofile_get_field_data(71, $user_id);

    return [
        'orientation' => $orientation,
        'interests' => is_array($interests) ? $interests : ($interests ? explode(',', $interests) : [])
    ];
}

/**
 * Get creative practice data
 */
function unmask_get_creative_practice($user_id) {
    if (!function_exists('xprofile_get_field_data')) {
        return null;
    }

    // Field IDs: 21 = What do you practice?, 103 = Portfolio
    $practices = xprofile_get_field_data(21, $user_id);
    $portfolio = xprofile_get_field_data(103, $user_id);

    // If nothing filled out, return null
    if (empty($practices) && empty($portfolio)) {
        return null;
    }

    return [
        'practices' => is_array($practices) ? $practices : ($practices ? explode(',', $practices) : []),
        'portfolio' => $portfolio ?: ''
    ];
}

/**
 * Get dashboard visibility preference
 */
function unmask_get_dashboard_visibility($user_id) {
    $visibility = get_user_meta($user_id, 'unmask_dashboard_visibility', true);
    return $visibility ?: 'masked';
}

/**
 * Set dashboard visibility preference
 */
function unmask_set_dashboard_visibility($user_id, $visibility) {
    return update_user_meta($user_id, 'unmask_dashboard_visibility', $visibility);
}

/**
 * Get user's basic profile data
 */
function unmask_get_profile_basics($user_id) {
    $user = get_userdata($user_id);

    $data = [
        'display_name' => $user ? $user->display_name : '',
        'bio' => '',
        'city' => '',
        'pronouns' => ''
    ];

    if (function_exists('xprofile_get_field_data')) {
        // Field IDs: 3 = Scene Name, 88 = Bio, 87 = City, 85 = Pronouns
        $scene_name = xprofile_get_field_data(3, $user_id);
        if (!empty($scene_name)) {
            $data['display_name'] = $scene_name;
        }

        $data['bio'] = xprofile_get_field_data(88, $user_id) ?: '';
        $data['city'] = xprofile_get_field_data(87, $user_id) ?: '';
        $data['pronouns'] = xprofile_get_field_data(85, $user_id) ?: '';
    }

    return $data;
}

/**
 * Get user avatar URL
 */
function unmask_get_avatar_url($user_id, $size = 100) {
    if (function_exists('bp_core_fetch_avatar')) {
        return bp_core_fetch_avatar([
            'item_id' => $user_id,
            'type' => 'full',
            'width' => $size,
            'height' => $size,
            'html' => false
        ]);
    }

    return get_avatar_url($user_id, ['size' => $size]);
}

/**
 * Format date for display
 */
function unmask_format_date($date_string, $format = 'M j') {
    if (empty($date_string)) {
        return '';
    }

    $timestamp = strtotime($date_string);
    if ($timestamp === false) {
        return '';
    }

    return date($format, $timestamp);
}

/**
 * Calculate profile completion percentage
 * Returns array with percentage and missing fields
 */
function unmask_get_profile_completion($user_id) {
    if (!function_exists('xprofile_get_field_data')) {
        return ['percentage' => 0, 'completed' => [], 'missing' => []];
    }

    // Define fields with weights and labels
    $fields = [
        // Visitor Profile (core identity)
        ['id' => 3, 'label' => 'Scene Name', 'weight' => 15, 'group' => 1],
        ['id' => 85, 'label' => 'Pronouns', 'weight' => 10, 'group' => 1],
        ['id' => 87, 'label' => 'City', 'weight' => 15, 'group' => 1],
        ['id' => 88, 'label' => 'Bio', 'weight' => 15, 'group' => 1],
        // Creative Practice
        ['id' => 21, 'label' => 'Creative practices', 'weight' => 15, 'group' => 2],
        ['id' => 103, 'label' => 'Portfolio link', 'weight' => 10, 'group' => 2],
        // Status (any one counts)
        ['id' => 'status', 'label' => 'Availability status', 'weight' => 10, 'group' => 2],
        // Avatar
        ['id' => 'avatar', 'label' => 'Profile photo', 'weight' => 10, 'group' => 1],
    ];

    $completed = [];
    $missing = [];
    $total_weight = 0;
    $earned_weight = 0;

    foreach ($fields as $field) {
        $total_weight += $field['weight'];
        $has_value = false;

        if ($field['id'] === 'status') {
            // Check if any status field is filled
            $status = unmask_get_user_status($user_id);
            $has_value = $status['bookable'] || $status['available_photographed'] || $status['available_photograph'];
        } elseif ($field['id'] === 'avatar') {
            // Check if user has custom avatar
            if (function_exists('bp_get_user_has_avatar')) {
                $has_value = bp_get_user_has_avatar($user_id);
            }
        } else {
            $value = xprofile_get_field_data($field['id'], $user_id);
            // Handle portfolio HTML issue
            if ($field['id'] === 103 && !empty($value)) {
                $value = strip_tags($value);
                $value = trim($value);
                $has_value = !empty($value) && filter_var($value, FILTER_VALIDATE_URL);
            } else {
                $has_value = !empty($value);
            }
        }

        if ($has_value) {
            $earned_weight += $field['weight'];
            $completed[] = $field;
        } else {
            $missing[] = $field;
        }
    }

    $percentage = $total_weight > 0 ? round(($earned_weight / $total_weight) * 100) : 0;

    return [
        'percentage' => $percentage,
        'completed' => $completed,
        'missing' => $missing
    ];
}

/**
 * Check if two users are connected (friends)
 */
function unmask_are_users_connected($user_id_1, $user_id_2) {
    if (!function_exists('friends_check_friendship')) {
        return false;
    }
    return friends_check_friendship($user_id_1, $user_id_2);
}

/**
 * Get mutual connections count between two users
 */
function unmask_get_mutual_connections($user_id_1, $user_id_2) {
    if (!function_exists('friends_get_friend_user_ids')) {
        return 0;
    }

    $friends_1 = friends_get_friend_user_ids($user_id_1);
    $friends_2 = friends_get_friend_user_ids($user_id_2);

    if (empty($friends_1) || empty($friends_2)) {
        return 0;
    }

    return count(array_intersect($friends_1, $friends_2));
}

/**
 * Get shared functions/practices between two users
 */
function unmask_get_shared_functions($user_id_1, $user_id_2) {
    $functions_1 = unmask_get_user_functions($user_id_1);
    $functions_2 = unmask_get_user_functions($user_id_2);

    if (empty($functions_1) || empty($functions_2)) {
        return [];
    }

    // Normalize to lowercase for comparison
    $functions_1 = array_map('strtolower', array_map('trim', $functions_1));
    $functions_2 = array_map('strtolower', array_map('trim', $functions_2));

    return array_intersect($functions_1, $functions_2);
}

/**
 * Check if two users are in the same city
 */
function unmask_same_city($user_id_1, $user_id_2) {
    $basics_1 = unmask_get_profile_basics($user_id_1);
    $basics_2 = unmask_get_profile_basics($user_id_2);

    if (empty($basics_1['city']) || empty($basics_2['city'])) {
        return false;
    }

    return strtolower(trim($basics_1['city'])) === strtolower(trim($basics_2['city']));
}

/**
 * Get recent/active visitors for the dossier grid
 *
 * @param int $count Number of visitors to return
 * @param int $exclude_user_id User ID to exclude (the profile being viewed)
 * @return array Array of user objects
 */
function unmask_get_recent_visitors($count = 9, $exclude_user_id = 0) {
    // Try BuddyBoss active users first
    if (function_exists('bp_core_get_users')) {
        $bp_users = bp_core_get_users([
            'type' => 'active',
            'per_page' => $count + 1, // Get one extra in case we need to exclude
            'exclude' => $exclude_user_id ? [$exclude_user_id] : [],
        ]);

        if (!empty($bp_users['users'])) {
            return array_slice($bp_users['users'], 0, $count);
        }
    }

    // Fallback to WP_User_Query
    $args = [
        'number' => $count,
        'fields' => ['ID', 'display_name'],
        'orderby' => 'registered',
        'order' => 'DESC',
    ];

    if ($exclude_user_id) {
        $args['exclude'] = [$exclude_user_id];
    }

    $query = new WP_User_Query($args);
    return $query->get_results();
}

/**
 * ============================================================================
 * VISIBILITY SETTINGS
 *
 * Per-section visibility levels:
 *   - user: Public (everyone including logged-out)
 *   - visitor: Members only (logged-in users)
 *   - interlinked: Connections only (friends)
 * ============================================================================
 */

/**
 * Get visibility setting for a section
 *
 * @param int    $user_id   User ID
 * @param string $section   Section identifier (bio, practice, kink, etc.)
 * @return string Visibility level: user|visitor|interlinked
 */
function unmask_get_section_visibility($user_id, $section) {
    $meta_key = 'unmask_visibility_' . sanitize_key($section);
    $visibility = get_user_meta($user_id, $meta_key, true);

    // Default visibility per section
    $defaults = [
        'location' => 'visitor',
        'bio' => 'visitor',
        'functions' => 'user',
        'status' => 'visitor',
        'practice' => 'user',
        'portfolio' => 'user',
        'kink' => 'interlinked', // Always default to most restrictive
    ];

    if (empty($visibility)) {
        return isset($defaults[$section]) ? $defaults[$section] : 'visitor';
    }

    return $visibility;
}

/**
 * Set visibility setting for a section
 *
 * @param int    $user_id    User ID
 * @param string $section    Section identifier
 * @param string $visibility Visibility level: user|visitor|interlinked
 * @return bool Success
 */
function unmask_set_section_visibility($user_id, $section, $visibility) {
    // Validate visibility level
    $valid_levels = ['user', 'visitor', 'interlinked'];
    if (!in_array($visibility, $valid_levels)) {
        return false;
    }

    // Kink section cannot be set to 'user' (always restricted)
    if ($section === 'kink' && $visibility === 'user') {
        $visibility = 'visitor';
    }

    $meta_key = 'unmask_visibility_' . sanitize_key($section);
    return update_user_meta($user_id, $meta_key, $visibility);
}

/**
 * Check if viewer can see a section based on visibility settings
 *
 * @param int    $owner_id   Profile owner user ID
 * @param int    $viewer_id  Viewer user ID (0 for logged-out)
 * @param string $section    Section identifier
 * @return bool Whether viewer can see the section
 */
function unmask_can_view_section($owner_id, $viewer_id, $section) {
    // Owner can always see their own content
    if ($owner_id === $viewer_id && $viewer_id > 0) {
        return true;
    }

    $visibility = unmask_get_section_visibility($owner_id, $section);

    switch ($visibility) {
        case 'user':
            // Everyone can see
            return true;

        case 'visitor':
            // Must be logged in
            return $viewer_id > 0;

        case 'interlinked':
            // Must be connected (friends)
            if ($viewer_id <= 0) {
                return false;
            }
            return unmask_are_users_connected($owner_id, $viewer_id);

        default:
            return false;
    }
}

/**
 * Get all visibility settings for a user
 */
function unmask_get_all_visibility_settings($user_id) {
    $sections = ['location', 'bio', 'functions', 'status', 'practice', 'portfolio', 'kink'];
    $settings = [];

    foreach ($sections as $section) {
        $settings[$section] = unmask_get_section_visibility($user_id, $section);
    }

    return $settings;
}

/**
 * ============================================================================
 * AJAX HANDLERS
 * ============================================================================
 */

/**
 * AJAX: Save visibility setting
 */
function unmask_ajax_save_visibility() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'unmask_dossier_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    // Must be logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $section = sanitize_key($_POST['section'] ?? '');
    $level = sanitize_key($_POST['level'] ?? '');

    if (empty($section) || empty($level)) {
        wp_send_json_error(['message' => 'Missing parameters']);
    }

    $result = unmask_set_section_visibility($user_id, $section, $level);

    if ($result !== false) {
        wp_send_json_success(['message' => 'Saved', 'section' => $section, 'level' => $level]);
    } else {
        wp_send_json_error(['message' => 'Failed to save']);
    }
}
add_action('wp_ajax_unmask_save_visibility', 'unmask_ajax_save_visibility');

/**
 * AJAX: Save profile field (inline editing)
 */
function unmask_ajax_save_profile_field() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'unmask_dossier_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    // Must be logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $field = sanitize_key($_POST['field'] ?? '');
    $value = wp_kses_post($_POST['value'] ?? '');

    // Map field names to xProfile field IDs
    $field_map = [
        'scene_name' => 3,
        'pronouns' => 85,
        'city' => 87,
        'bio' => 88,
        'practices' => 21,
        'portfolio' => 103,
    ];

    if (!isset($field_map[$field])) {
        wp_send_json_error(['message' => 'Unknown field']);
    }

    $field_id = $field_map[$field];

    // Update xProfile field
    if (function_exists('xprofile_set_field_data')) {
        $result = xprofile_set_field_data($field_id, $user_id, $value);

        if ($result) {
            wp_send_json_success(['message' => 'Saved', 'field' => $field, 'value' => $value]);
        } else {
            wp_send_json_error(['message' => 'Failed to save']);
        }
    } else {
        wp_send_json_error(['message' => 'xProfile not available']);
    }
}
add_action('wp_ajax_unmask_save_profile_field', 'unmask_ajax_save_profile_field');

/**
 * Enqueue dossier inline edit script with nonce
 */
function unmask_enqueue_dossier_script() {
    // Only on profile pages when viewing own profile
    if (!function_exists('bp_is_my_profile') || !bp_is_my_profile()) {
        return;
    }

    wp_enqueue_script(
        'unmask-dossier-inline-edit',
        get_stylesheet_directory_uri() . '/assets/js/dossier-inline-edit.js',
        [],
        filemtime(get_stylesheet_directory() . '/assets/js/dossier-inline-edit.js'),
        true
    );

    wp_localize_script('unmask-dossier-inline-edit', 'unmaskDossier', [
        'nonce' => wp_create_nonce('unmask_dossier_nonce'),
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_dossier_script');
