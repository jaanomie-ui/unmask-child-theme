<?php
/**
 * UNMASK Profile Edit — Enqueue Assets
 *
 * Loads CSS and JS for the profile edit split panel + bottom sheet interface.
 * Also registers the AJAX handler for saving field updates.
 *
 * @package UNMASK
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue profile edit styles and scripts
 */
function unmask_enqueue_profile_edit_assets() {
    // Only on BuddyPress profile edit pages
    if (!function_exists('bp_is_user_profile_edit') || !bp_is_user_profile_edit()) {
        return;
    }

    // Only for own profile
    if (!bp_is_my_profile()) {
        return;
    }

    $theme_uri = get_stylesheet_directory_uri();
    $theme_dir = get_stylesheet_directory();

    // Split panel CSS - depends on design system for tokens
    wp_enqueue_style(
        'unmask-edit-split',
        $theme_uri . '/assets/css/components/edit-split.css',
        array('buddyboss-theme-css', 'unmask-00-design-system'),
        filemtime($theme_dir . '/assets/css/components/edit-split.css')
    );

    // Bottom sheet CSS
    wp_enqueue_style(
        'unmask-bottom-sheet',
        $theme_uri . '/assets/css/components/bottom-sheet.css',
        array('unmask-edit-split'),
        filemtime($theme_dir . '/assets/css/components/bottom-sheet.css')
    );

    // Critical layout fix - inline CSS for highest priority
    $layout_fix = '
        .bb-grid.site-content-grid { display: block !important; }
        .bb-grid.site-content-grid > #primary { width: 100% !important; max-width: 100% !important; }
        #primary.lg-grid-2-3, #primary.md-grid-2-3, .lg-grid-2-3, .md-grid-2-3 {
            flex: unset !important;
            max-width: 100% !important;
            width: 100% !important;
        }
    ';
    wp_add_inline_style('unmask-bottom-sheet', $layout_fix);

    // Profile edit JS
    wp_enqueue_script(
        'unmask-profile-edit-sheet',
        $theme_uri . '/assets/js/profile-edit-sheet.js',
        array(),
        filemtime($theme_dir . '/assets/js/profile-edit-sheet.js'),
        true
    );

    // Localize script with AJAX data
    wp_localize_script('unmask-profile-edit-sheet', 'unmaskProfileEdit', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('unmask_profile_edit_nonce'),
        'userId'  => bp_displayed_user_id(),
    ));
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_profile_edit_assets', 1001);

/**
 * Force full-width layout on profile edit pages via wp_head
 * This prints CSS after ALL enqueued styles
 */
function unmask_force_profile_edit_layout() {
    // Check multiple conditions - any bp-user page
    $is_bp_user = function_exists('bp_is_user') && bp_is_user();
    $is_profile_edit = function_exists('bp_is_user_profile_edit') && bp_is_user_profile_edit();
    $url_has_edit = strpos($_SERVER['REQUEST_URI'], '/profile/edit') !== false;

    if (!$is_bp_user && !$is_profile_edit && !$url_has_edit) {
        return;
    }
    ?>
    <style id="unmask-profile-edit-layout-fix">
        /* Match BuddyBoss specificity with .bb-template-v2 */
        .bb-template-v2 .site-content-grid #primary.content-area,
        .bb-template-v2 #primary.lg-grid-2-3,
        .bb-template-v2 #primary.md-grid-2-3,
        .bb-template-v2 .content-area.lg-grid-2-3,
        body.bb-template-v2 #primary {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
        }
    </style>
    <?php
}
add_action('wp_head', 'unmask_force_profile_edit_layout', 9999);
add_action('wp_footer', 'unmask_force_profile_edit_layout', 9999);

/**
 * AJAX handler: Update profile field
 */
function unmask_ajax_update_profile_field() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unmask_profile_edit_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }

    // Check user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in.'));
    }

    // Get field ID and value
    $field_id = isset($_POST['field_id']) ? absint($_POST['field_id']) : 0;
    $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';

    if (!$field_id) {
        wp_send_json_error(array('message' => 'Invalid field.'));
    }

    // Verify user can edit this profile
    $user_id = get_current_user_id();

    // Check if field exists
    if (!function_exists('xprofile_get_field')) {
        wp_send_json_error(array('message' => 'BuddyPress XProfile not available.'));
    }

    $field = xprofile_get_field($field_id);
    if (!$field) {
        wp_send_json_error(array('message' => 'Field not found.'));
    }

    // Handle different field types
    $field_type = $field->type;

    // For multiselect/checkbox fields, the value might be comma-separated
    if (in_array($field_type, array('multiselectbox', 'checkbox'))) {
        $value = array_map('trim', explode(',', $value));
        $value = array_filter($value); // Remove empty values
    }

    // Update the field
    $result = xprofile_set_field_data($field_id, $user_id, $value);

    if ($result) {
        // Get the display value for the response
        $display_value = xprofile_get_field_data($field_id, $user_id);

        if (is_array($display_value)) {
            $display_value = implode(', ', $display_value);
        }

        wp_send_json_success(array(
            'message' => 'Field updated successfully.',
            'value'   => $display_value,
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to update field.'));
    }
}
add_action('wp_ajax_unmask_update_profile_field', 'unmask_ajax_update_profile_field');

/**
 * Get field options for select/multiselect fields
 *
 * @param object $field BuddyPress field object
 * @return array|null Options array or null
 */
function unmask_get_field_options($field) {
    if (!$field || !isset($field->type)) {
        return null;
    }

    $types_with_options = array('selectbox', 'multiselectbox', 'checkbox', 'radio');

    if (!in_array($field->type, $types_with_options)) {
        return null;
    }

    $children = $field->get_children();
    if (empty($children)) {
        return null;
    }

    $options = array();
    foreach ($children as $child) {
        $options[] = array(
            'value' => $child->name,
            'label' => $child->name,
        );
    }

    return $options;
}

/**
 * Get hint text for a field based on its name
 *
 * @param string $field_name Field name
 * @return string Hint text
 */
function unmask_get_field_hint($field_name) {
    $hints = array(
        'Name'           => 'Your scene name is how you appear across the platform.',
        'Scene Name'     => 'Your scene name is how you appear across the platform.',
        'Designation'    => 'Your unique identifier in the UNMASK system.',
        'Pronouns'       => 'How you prefer to be referred to.',
        'City'           => 'Your current location (city only for privacy).',
        'Location'       => 'Your current location (city only for privacy).',
        'Bio'            => 'A brief description of yourself. Keep it concise.',
        'About Me'       => 'A brief description of yourself. Keep it concise.',
        'Functions'      => 'Your roles and specialties within the community.',
        'Availability'   => 'Your current availability for connections and collaborations.',
        'Website'        => 'Your personal website or portfolio URL.',
        'Social'         => 'Social media handles or links.',
    );

    foreach ($hints as $key => $hint) {
        if (stripos($field_name, $key) !== false) {
            return $hint;
        }
    }

    return '';
}

/**
 * Map BuddyPress field type to HTML input type
 *
 * @param string $bp_type BuddyPress field type
 * @return string HTML input type
 */
function unmask_get_input_type($bp_type) {
    $type_map = array(
        'textbox'        => 'text',
        'textarea'       => 'textarea',
        'selectbox'      => 'select',
        'multiselectbox' => 'multiselect',
        'checkbox'       => 'multiselect',
        'radio'          => 'select',
        'datebox'        => 'date',
        'url'            => 'url',
        'number'         => 'number',
    );

    return isset($type_map[$bp_type]) ? $type_map[$bp_type] : 'text';
}
