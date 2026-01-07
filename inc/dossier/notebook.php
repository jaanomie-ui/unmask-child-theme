<?php
/**
 * Dossier Notebook System v4
 *
 * Per-field visibility with section lock/unlock pattern.
 * Replaces section-level visibility with field-level control.
 *
 * Visibility levels (order: private → public):
 *   - hidden (gray): Only owner can see
 *   - green (interlinked): Only mutual connections can see
 *   - yellow (user): Any logged-in member can see
 *   - red (visitor): Public, even logged-out visitors
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

/* ============================================================================
   FIELD DEFINITIONS
   Maps field keys to xProfile field IDs
   ============================================================================ */

/**
 * Get all notebook field definitions organized by section
 */
function unmask_get_notebook_sections() {
    return [
        'identity' => [
            'label' => 'identity',
            'fields' => [
                'scene_name' => [
                    'label' => 'scene name',
                    'xprofile_id' => 3,
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
                'designation' => [
                    'label' => 'designation',
                    'xprofile_id' => null, // System-generated
                    'editable' => false,
                    'default_visibility' => 'hidden',
                ],
                'pronouns' => [
                    'label' => 'pronouns',
                    'xprofile_id' => 85,
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
                'city' => [
                    'label' => 'city',
                    'xprofile_id' => 87,
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
            ],
        ],
        'creative' => [
            'label' => 'creative practice',
            'fields' => [
                'functions' => [
                    'label' => 'functions',
                    'xprofile_id' => 21,
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
                'availability' => [
                    'label' => 'availability',
                    'xprofile_id' => null, // Composite of 104, 108, 111
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
                'portfolio' => [
                    'label' => 'portfolio link',
                    'xprofile_id' => 103,
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
                'bio' => [
                    'label' => 'bio',
                    'xprofile_id' => 88,
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
            ],
        ],
        'kink' => [
            'label' => 'kink profile',
            'fields' => [
                'ds_orientation' => [
                    'label' => 'd/s orientation',
                    'xprofile_id' => 57,
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
                'interests' => [
                    'label' => 'interests',
                    'xprofile_id' => 71,
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
                'limits' => [
                    'label' => 'limits',
                    'xprofile_id' => null, // Custom field
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
                'looking_for' => [
                    'label' => 'looking for',
                    'xprofile_id' => null, // Custom field
                    'editable' => true,
                    'default_visibility' => 'hidden',
                ],
            ],
        ],
        'custom' => [
            'label' => 'custom fields',
            'is_custom' => true,
            'fields' => [], // Populated dynamically
        ],
    ];
}

/**
 * Get flat list of all field keys in a section
 */
function unmask_get_section_field_keys($section_id) {
    $sections = unmask_get_notebook_sections();
    if (!isset($sections[$section_id])) {
        return [];
    }
    return array_keys($sections[$section_id]['fields']);
}

/* ============================================================================
   PER-FIELD VISIBILITY
   ============================================================================ */

/**
 * Get visibility for a specific field
 *
 * @param int    $user_id   User ID
 * @param string $field_key Field key (e.g., 'scene_name', 'pronouns')
 * @return string Visibility level: hidden|green|yellow|red
 */
function unmask_get_field_visibility($user_id, $field_key) {
    static $cache = [];

    $cache_key = "{$user_id}";
    if (!isset($cache[$cache_key])) {
        $cache[$cache_key] = get_user_meta($user_id, 'unmask_field_visibility', true) ?: [];
    }

    if (isset($cache[$cache_key][$field_key])) {
        return $cache[$cache_key][$field_key];
    }

    // Return default visibility from field definition
    $sections = unmask_get_notebook_sections();
    foreach ($sections as $section) {
        if (isset($section['fields'][$field_key])) {
            return $section['fields'][$field_key]['default_visibility'] ?? 'hidden';
        }
    }

    return 'hidden';
}

/**
 * Set visibility for a specific field
 *
 * @param int    $user_id    User ID
 * @param string $field_key  Field key
 * @param string $visibility Visibility level: hidden|green|yellow|red
 * @return bool Success
 */
function unmask_set_field_visibility($user_id, $field_key, $visibility) {
    // Validate visibility level
    $valid_levels = ['hidden', 'green', 'yellow', 'red'];
    if (!in_array($visibility, $valid_levels)) {
        return false;
    }

    $field_visibility = get_user_meta($user_id, 'unmask_field_visibility', true) ?: [];
    $field_visibility[$field_key] = $visibility;

    return update_user_meta($user_id, 'unmask_field_visibility', $field_visibility);
}

/**
 * Get all field visibilities for a user
 */
function unmask_get_all_field_visibilities($user_id) {
    return get_user_meta($user_id, 'unmask_field_visibility', true) ?: [];
}

/**
 * Check if viewer can see a specific field
 *
 * @param int    $owner_id   Profile owner user ID
 * @param int    $viewer_id  Viewer user ID (0 for logged-out)
 * @param string $field_key  Field key
 * @return bool Whether viewer can see the field
 */
function unmask_can_view_field($owner_id, $viewer_id, $field_key) {
    // Memoize per-request
    static $cache = [];
    $cache_key = "{$owner_id}:{$viewer_id}:{$field_key}";

    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    // Owner can always see their own content
    if ($owner_id === $viewer_id && $viewer_id > 0) {
        $cache[$cache_key] = true;
        return true;
    }

    $visibility = unmask_get_field_visibility($owner_id, $field_key);

    $result = false;
    switch ($visibility) {
        case 'hidden':
            // Only owner can see
            $result = false;
            break;

        case 'red':
            // Public - everyone can see
            $result = true;
            break;

        case 'yellow':
            // Logged-in users only
            $result = $viewer_id > 0;
            break;

        case 'green':
            // Interlinked (friends) only
            if ($viewer_id <= 0) {
                $result = false;
            } else {
                $result = unmask_are_users_connected($owner_id, $viewer_id);
            }
            break;
    }

    $cache[$cache_key] = $result;
    return $result;
}

/* ============================================================================
   SECTION LOCK/UNLOCK
   ============================================================================ */

/**
 * Get section lock state
 *
 * @param int    $user_id    User ID
 * @param string $section_id Section ID
 * @return string|null Lock color (green|yellow|red) or null if unlocked
 */
function unmask_get_section_lock($user_id, $section_id) {
    $section_meta = get_user_meta($user_id, 'unmask_section_meta', true) ?: [];
    return $section_meta[$section_id]['locked'] ?? null;
}

/**
 * Set section lock state
 *
 * When locking, all non-hidden fields in the section are set to the lock color.
 * When unlocking, fields retain their current visibility (no revert).
 *
 * @param int         $user_id    User ID
 * @param string      $section_id Section ID
 * @param string|null $locked     Lock color (green|yellow|red) or null to unlock
 * @return bool Success
 */
function unmask_set_section_lock($user_id, $section_id, $locked) {
    // Validate lock value
    if ($locked !== null && !in_array($locked, ['green', 'yellow', 'red'])) {
        return false;
    }

    // Update section meta
    $section_meta = get_user_meta($user_id, 'unmask_section_meta', true) ?: [];
    $section_meta[$section_id] = [
        'locked' => $locked,
    ];
    update_user_meta($user_id, 'unmask_section_meta', $section_meta);

    // If locking, update all non-hidden fields to the lock color
    if ($locked) {
        $field_keys = unmask_get_section_field_keys($section_id);
        $field_visibility = get_user_meta($user_id, 'unmask_field_visibility', true) ?: [];

        foreach ($field_keys as $field_key) {
            $current = $field_visibility[$field_key] ?? 'hidden';
            // Only change non-hidden fields
            if ($current !== 'hidden') {
                $field_visibility[$field_key] = $locked;
            }
        }

        update_user_meta($user_id, 'unmask_field_visibility', $field_visibility);
    }

    return true;
}

/**
 * Check if a section is locked
 */
function unmask_is_section_locked($user_id, $section_id) {
    return unmask_get_section_lock($user_id, $section_id) !== null;
}

/**
 * Get effective visibility for a field (accounting for section lock)
 *
 * @param int    $user_id    User ID
 * @param string $section_id Section ID
 * @param string $field_key  Field key
 * @return string Effective visibility: hidden|green|yellow|red
 */
function unmask_get_effective_field_visibility($user_id, $section_id, $field_key) {
    $field_visibility = unmask_get_field_visibility($user_id, $field_key);

    // Hidden fields are always hidden regardless of section lock
    if ($field_visibility === 'hidden') {
        return 'hidden';
    }

    // Check if section is locked
    $section_lock = unmask_get_section_lock($user_id, $section_id);
    if ($section_lock) {
        return $section_lock;
    }

    return $field_visibility;
}

/* ============================================================================
   CUSTOM FIELDS
   ============================================================================ */

define('UNMASK_MAX_CUSTOM_FIELDS', 5);

/**
 * Get user's custom fields
 *
 * @param int $user_id User ID
 * @return array Custom fields array
 */
function unmask_get_custom_fields($user_id) {
    return get_user_meta($user_id, 'unmask_custom_fields', true) ?: [];
}

/**
 * Add a custom field
 *
 * @param int    $user_id User ID
 * @param string $label   Field label
 * @param string $value   Field value
 * @return array|WP_Error New field data or error
 */
function unmask_add_custom_field($user_id, $label, $value) {
    $custom_fields = unmask_get_custom_fields($user_id);

    // Check limit
    if (count($custom_fields) >= UNMASK_MAX_CUSTOM_FIELDS) {
        return new WP_Error('limit_reached', sprintf('Maximum %d custom fields allowed', UNMASK_MAX_CUSTOM_FIELDS));
    }

    // Validate
    $errors = unmask_validate_custom_field($label, $value);
    if (!empty($errors)) {
        return new WP_Error('validation_failed', 'Validation failed', $errors);
    }

    // Create new field
    $new_field = [
        'id' => 'custom_' . time() . '_' . wp_rand(100, 999),
        'label' => sanitize_text_field($label),
        'value' => sanitize_text_field($value),
        'visibility' => 'hidden', // Default to hidden
        'created_at' => current_time('mysql'),
    ];

    $custom_fields[] = $new_field;
    update_user_meta($user_id, 'unmask_custom_fields', $custom_fields);

    return $new_field;
}

/**
 * Update a custom field
 *
 * @param int    $user_id  User ID
 * @param string $field_id Custom field ID
 * @param array  $updates  Fields to update (label, value, visibility)
 * @return bool Success
 */
function unmask_update_custom_field($user_id, $field_id, $updates) {
    $custom_fields = unmask_get_custom_fields($user_id);

    foreach ($custom_fields as &$field) {
        if ($field['id'] === $field_id) {
            if (isset($updates['label'])) {
                $field['label'] = sanitize_text_field($updates['label']);
            }
            if (isset($updates['value'])) {
                $field['value'] = sanitize_text_field($updates['value']);
            }
            if (isset($updates['visibility'])) {
                $valid = ['hidden', 'green', 'yellow', 'red'];
                if (in_array($updates['visibility'], $valid)) {
                    $field['visibility'] = $updates['visibility'];
                }
            }
            $field['updated_at'] = current_time('mysql');
            break;
        }
    }

    return update_user_meta($user_id, 'unmask_custom_fields', $custom_fields);
}

/**
 * Delete a custom field
 *
 * @param int    $user_id  User ID
 * @param string $field_id Custom field ID
 * @return bool Success
 */
function unmask_delete_custom_field($user_id, $field_id) {
    $custom_fields = unmask_get_custom_fields($user_id);
    $custom_fields = array_filter($custom_fields, function($f) use ($field_id) {
        return $f['id'] !== $field_id;
    });
    return update_user_meta($user_id, 'unmask_custom_fields', array_values($custom_fields));
}

/**
 * Validate custom field
 *
 * @param string $label Field label
 * @param string $value Field value
 * @return array Errors array (empty if valid)
 */
function unmask_validate_custom_field($label, $value) {
    $min_chars = 2;
    $errors = [];

    if (strlen(trim($label)) < $min_chars) {
        $errors['label'] = sprintf('min_char_%d', $min_chars);
    }

    if (strlen(trim($value)) < $min_chars) {
        $errors['value'] = sprintf('min_char_%d', $min_chars);
    }

    return $errors;
}

/* ============================================================================
   FIELD VALUE GETTERS
   ============================================================================ */

/**
 * Get field value from xProfile or custom storage
 *
 * @param int    $user_id   User ID
 * @param string $field_key Field key
 * @return mixed Field value
 */
function unmask_get_field_value($user_id, $field_key) {
    // Check if it's a custom field
    if (strpos($field_key, 'custom_') === 0) {
        $custom_fields = unmask_get_custom_fields($user_id);
        foreach ($custom_fields as $field) {
            if ($field['id'] === $field_key) {
                return $field['value'];
            }
        }
        return '';
    }

    // Handle special fields
    switch ($field_key) {
        case 'designation':
            return unmask_get_designation($user_id);

        case 'availability':
            $status = unmask_get_user_status($user_id);
            $parts = [];
            if ($status['bookable']) $parts[] = 'bookable';
            if ($status['available_photographed']) $parts[] = 'model';
            if ($status['available_photograph']) $parts[] = 'photographer';
            return implode(', ', $parts);
    }

    // Get from xProfile
    $sections = unmask_get_notebook_sections();
    foreach ($sections as $section) {
        if (isset($section['fields'][$field_key])) {
            $xprofile_id = $section['fields'][$field_key]['xprofile_id'];
            if ($xprofile_id && function_exists('xprofile_get_field_data')) {
                return xprofile_get_field_data($xprofile_id, $user_id);
            }
        }
    }

    // Fall back to user meta
    return get_user_meta($user_id, 'unmask_' . $field_key, true);
}

/**
 * Set field value in xProfile or custom storage
 *
 * @param int    $user_id   User ID
 * @param string $field_key Field key
 * @param mixed  $value     Field value
 * @return bool Success
 */
function unmask_set_field_value($user_id, $field_key, $value) {
    // Check if it's a custom field
    if (strpos($field_key, 'custom_') === 0) {
        return unmask_update_custom_field($user_id, $field_key, ['value' => $value]);
    }

    // Special non-editable fields
    if ($field_key === 'designation') {
        return false; // Cannot edit designation
    }

    // Get xProfile field ID
    $sections = unmask_get_notebook_sections();
    foreach ($sections as $section) {
        if (isset($section['fields'][$field_key])) {
            $xprofile_id = $section['fields'][$field_key]['xprofile_id'];
            if ($xprofile_id && function_exists('xprofile_set_field_data')) {
                return xprofile_set_field_data($xprofile_id, $user_id, $value);
            }
        }
    }

    // Fall back to user meta
    return update_user_meta($user_id, 'unmask_' . $field_key, $value);
}

/* ============================================================================
   VISIBILITY COUNTS (for preview bar)
   ============================================================================ */

/**
 * Get visibility counts for the preview bar
 *
 * @param int $user_id User ID
 * @return array Counts per visibility level
 */
function unmask_get_visibility_counts($user_id) {
    $counts = [
        'green' => 0,
        'yellow' => 0,
        'red' => 0,
        'hidden' => 0,
    ];

    // Count standard fields
    $sections = unmask_get_notebook_sections();
    foreach ($sections as $section_id => $section) {
        if (!empty($section['is_custom'])) continue;

        foreach ($section['fields'] as $field_key => $field_def) {
            $effective = unmask_get_effective_field_visibility($user_id, $section_id, $field_key);
            if (isset($counts[$effective])) {
                $counts[$effective]++;
            }
        }
    }

    // Count custom fields
    $custom_fields = unmask_get_custom_fields($user_id);
    foreach ($custom_fields as $field) {
        $vis = $field['visibility'] ?? 'hidden';
        if (isset($counts[$vis])) {
            $counts[$vis]++;
        }
    }

    return $counts;
}

/* ============================================================================
   AJAX HANDLERS
   ============================================================================ */

/**
 * AJAX: Save field visibility
 */
function unmask_ajax_save_field_visibility_v4() {
    check_ajax_referer('unmask_notebook_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $field_key = sanitize_key($_POST['field_key'] ?? '');
    $visibility = sanitize_key($_POST['visibility'] ?? '');

    if (empty($field_key) || empty($visibility)) {
        wp_send_json_error(['message' => 'Missing parameters']);
    }

    $result = unmask_set_field_visibility($user_id, $field_key, $visibility);

    if ($result !== false) {
        wp_send_json_success([
            'message' => 'Saved',
            'field' => $field_key,
            'visibility' => $visibility,
            'counts' => unmask_get_visibility_counts($user_id),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to save']);
    }
}
add_action('wp_ajax_unmask_save_field_visibility_v4', 'unmask_ajax_save_field_visibility_v4');

/**
 * AJAX: Save section lock
 */
function unmask_ajax_save_section_lock() {
    check_ajax_referer('unmask_notebook_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $section_id = sanitize_key($_POST['section_id'] ?? '');
    $locked_raw = $_POST['locked'] ?? '';
    $locked = ($locked_raw === 'null' || $locked_raw === '') ? null : sanitize_key($locked_raw);

    if (empty($section_id)) {
        wp_send_json_error(['message' => 'Missing section_id']);
    }

    $result = unmask_set_section_lock($user_id, $section_id, $locked);

    if ($result) {
        // Get updated field visibilities
        $field_visibilities = unmask_get_all_field_visibilities($user_id);

        wp_send_json_success([
            'message' => 'Saved',
            'section' => $section_id,
            'locked' => $locked,
            'field_visibilities' => $field_visibilities,
            'counts' => unmask_get_visibility_counts($user_id),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to save']);
    }
}
add_action('wp_ajax_unmask_save_section_lock', 'unmask_ajax_save_section_lock');

/**
 * AJAX: Save field value
 */
function unmask_ajax_save_field_value_v4() {
    check_ajax_referer('unmask_notebook_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $field_key = sanitize_key($_POST['field_key'] ?? '');
    $value = wp_kses_post($_POST['value'] ?? '');

    if (empty($field_key)) {
        wp_send_json_error(['message' => 'Missing field_key']);
    }

    $result = unmask_set_field_value($user_id, $field_key, $value);

    if ($result !== false) {
        wp_send_json_success([
            'message' => 'Saved',
            'field' => $field_key,
            'value' => $value,
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to save']);
    }
}
add_action('wp_ajax_unmask_save_field_value_v4', 'unmask_ajax_save_field_value_v4');

/**
 * AJAX: Add custom field
 */
function unmask_ajax_add_custom_field() {
    check_ajax_referer('unmask_notebook_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $label = sanitize_text_field($_POST['label'] ?? '');
    $value = sanitize_text_field($_POST['value'] ?? '');

    $result = unmask_add_custom_field($user_id, $label, $value);

    if (is_wp_error($result)) {
        wp_send_json_error([
            'message' => $result->get_error_message(),
            'errors' => $result->get_error_data(),
        ]);
    } else {
        wp_send_json_success([
            'message' => 'Added',
            'field' => $result,
            'counts' => unmask_get_visibility_counts($user_id),
        ]);
    }
}
add_action('wp_ajax_unmask_add_custom_field', 'unmask_ajax_add_custom_field');

/**
 * AJAX: Delete custom field
 */
function unmask_ajax_delete_custom_field() {
    check_ajax_referer('unmask_notebook_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $field_id = sanitize_key($_POST['field_id'] ?? '');

    if (empty($field_id)) {
        wp_send_json_error(['message' => 'Missing field_id']);
    }

    $result = unmask_delete_custom_field($user_id, $field_id);

    if ($result) {
        wp_send_json_success([
            'message' => 'Deleted',
            'field_id' => $field_id,
            'counts' => unmask_get_visibility_counts($user_id),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to delete']);
    }
}
add_action('wp_ajax_unmask_delete_custom_field', 'unmask_ajax_delete_custom_field');

/**
 * AJAX: Update custom field visibility
 */
function unmask_ajax_update_custom_field_visibility() {
    check_ajax_referer('unmask_notebook_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $field_id = sanitize_key($_POST['field_id'] ?? '');
    $visibility = sanitize_key($_POST['visibility'] ?? '');

    if (empty($field_id) || empty($visibility)) {
        wp_send_json_error(['message' => 'Missing parameters']);
    }

    $result = unmask_update_custom_field($user_id, $field_id, ['visibility' => $visibility]);

    if ($result) {
        wp_send_json_success([
            'message' => 'Saved',
            'field_id' => $field_id,
            'visibility' => $visibility,
            'counts' => unmask_get_visibility_counts($user_id),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to save']);
    }
}
add_action('wp_ajax_unmask_update_custom_field_visibility', 'unmask_ajax_update_custom_field_visibility');

/**
 * AJAX: Update custom field value
 */
function unmask_ajax_update_custom_field_value() {
    check_ajax_referer('unmask_notebook_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $field_id = sanitize_key($_POST['field_id'] ?? '');
    $value = sanitize_text_field($_POST['value'] ?? '');

    if (empty($field_id)) {
        wp_send_json_error(['message' => 'Missing field_id']);
    }

    $result = unmask_update_custom_field($user_id, $field_id, ['value' => $value]);

    if ($result) {
        wp_send_json_success([
            'message' => 'Saved',
            'field_id' => $field_id,
            'value' => $value,
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to save']);
    }
}
add_action('wp_ajax_unmask_update_custom_field_value', 'unmask_ajax_update_custom_field_value');

/* ============================================================================
   HELPER FUNCTIONS
   Note: The following functions are defined in other files - do not duplicate:
   - unmask_are_users_connected() - inc/dossier/functions.php
   - unmask_get_user_status() - inc/dossier/functions.php
   - unmask_get_designation() - functions.php
   ============================================================================ */
