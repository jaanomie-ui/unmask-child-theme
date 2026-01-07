# UNMASK Dossier Notebook System — Implementation Spec

## Overview

Build a profile field editor for UNMASK (WordPress + BuddyBoss + MemberPress) that follows a "notebook" metaphor. Every user has the same profile structure. Fields are grouped into sections. Users control visibility per-field or per-section using a lock/unlock pattern.

**Tech Stack:**
- WordPress 6.x
- BuddyBoss Platform (xProfile fields)
- MemberPress (membership tiers)
- Child theme: `unmask-child-theme`
- NO inline CSS — all styles in external stylesheets
- NO Elementor

---

## 1. Registration Flow

**Minimal signup — only 3 fields:**

```php
// Fields collected at registration
- scene_name (display name) — required, min 2 characters
- email — required, valid email format
- password — required, min 8 characters
```

**Everything else is configured on the Dossier page after signup.**

Default state for ALL profile fields = **hidden (gray)** until user explicitly sets visibility.

---

## 2. Visibility System

### 2.1 Visibility Levels (Order: Private → Public)

| Color | Level | Key | Description |
|-------|-------|-----|-------------|
| Green | Interlinked | `interlinked` | Only mutual connections can see |
| Yellow | User | `user` | Any logged-in member can see |
| Red | Visitor | `visitor` | Public, even logged-out visitors |
| Gray | Hidden | `hidden` | Hidden from all (only owner sees) |

### 2.2 Color Tokens (Heavily Desaturated)

```css
/* Green — sage gray */
--visibility-green: #4a5550;
--visibility-green-dark: #3a4340;
--visibility-green-tint: #252a28;

/* Yellow — warm gray/ochre */
--visibility-yellow: #6a6350;
--visibility-yellow-dark: #4a4538;
--visibility-yellow-tint: #252420;

/* Red — dusty mauve */
--visibility-red: #5a4548;
--visibility-red-dark: #453538;
--visibility-red-tint: #231e20;

/* Gray — hidden state */
--visibility-gray: #3a3a3a;
--visibility-gray-dark: #2a2a2a;
--visibility-gray-tint: #1c1c1c;
```

### 2.3 Circle Controls

**All circles are the same size: 16px diameter**

```css
.visibility-circle {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 1px solid var(--border-subtle);
  background: transparent;
  cursor: pointer;
  padding: 0;
}

.visibility-circle--active {
  background: var(--color-dark);
  border-color: var(--color);
}

.visibility-circle--disabled {
  opacity: 0.3;
  cursor: default;
}
```

---

## 3. Section Lock/Unlock Behavior

### 3.1 Section States

Each section has a `locked` property:
- `locked: null` — Section is OPEN, individual field editing enabled
- `locked: 'green'|'yellow'|'red'` — Section is LOCKED to that color

### 3.2 Section Header Layout

```
┌─────────────────────────────────────────────────────────────┐
│ SECTION LABEL (editing)               ○  │  ○  ○  ○        │
│                                      gray    G  Y  R        │
└─────────────────────────────────────────────────────────────┘
```

### 3.3 Section Behavior

| User Action | Result |
|-------------|--------|
| Click gray circle | Opens section → "(editing)" shows, fields editable |
| Click G/Y/R circle | Locks section → ALL non-hidden fields set to that color |
| Section locked | Field circles disabled (opacity 0.3), inherit section color |
| Section open | Each field can have individual visibility |

### 3.4 PHP Data Structure

```php
// Section meta stored in user_meta
$section_meta = [
  'identity' => [
    'locked' => null,  // or 'green', 'yellow', 'red'
    'hidden' => false,
  ],
  'creative' => [
    'locked' => 'yellow',
    'hidden' => false,
  ],
  // ...
];

update_user_meta($user_id, 'unmask_section_meta', $section_meta);
```

---

## 4. Field Layout — One Line Per Field

### 4.1 Grid Structure

```
┌────────────┬─────────────────────────────┬────────────────┐
│ LABEL      │ VALUE                       │ ○  ○  ○  ○     │
│ 120px      │ flex                        │ 100px          │
└────────────┴─────────────────────────────┴────────────────┘
```

```css
.dossier-field-row {
  display: grid;
  grid-template-columns: 120px 1fr 100px;
  align-items: center;
  padding: 8px 20px;
  border-bottom: 1px solid var(--border-subtle);
  border-left: 3px solid var(--visibility-color);
}
```

### 4.2 Field Data Structure

```php
// Field visibility stored in xProfile field meta or user_meta
$field_visibility = [
  'scene_name' => 'red',
  'pronouns' => 'yellow',
  'kink_orientation' => 'green',
  'limits' => 'hidden',  // gray
];

update_user_meta($user_id, 'unmask_field_visibility', $field_visibility);
```

### 4.3 Visibility Logic (PHP)

```php
/**
 * Check if current viewer can see a field
 *
 * @param int $profile_user_id  The profile being viewed
 * @param int $viewer_id        The person viewing (0 = logged out)
 * @param string $field_key     The field to check
 * @return bool
 */
function unmask_can_view_field($profile_user_id, $viewer_id, $field_key) {
    $visibility = get_user_meta($profile_user_id, 'unmask_field_visibility', true);
    $field_vis = $visibility[$field_key] ?? 'hidden';

    // Hidden = only owner
    if ($field_vis === 'hidden') {
        return $viewer_id === $profile_user_id;
    }

    // Red = public (visitors)
    if ($field_vis === 'red') {
        return true;
    }

    // Yellow = logged-in users
    if ($field_vis === 'yellow') {
        return $viewer_id > 0;
    }

    // Green = interlinked only
    if ($field_vis === 'green') {
        return unmask_are_interlinked($profile_user_id, $viewer_id);
    }

    return false;
}
```

---

## 5. Default Sections & Fields

### 5.1 Section: Identity

| Field Key | Label | Default Visibility | Editable |
|-----------|-------|-------------------|----------|
| scene_name | scene name | hidden | yes |
| designation | designation | hidden | no (system) |
| pronouns | pronouns | hidden | yes |
| city | city | hidden | yes |

### 5.2 Section: Creative Practice

| Field Key | Label | Default Visibility | Editable |
|-----------|-------|-------------------|----------|
| functions | functions | hidden | yes |
| availability | availability | hidden | yes |
| portfolio | portfolio link | hidden | yes |
| bio | bio | hidden | yes |

### 5.3 Section: Kink Profile

| Field Key | Label | Default Visibility | Editable |
|-----------|-------|-------------------|----------|
| ds_orientation | d/s orientation | hidden | yes |
| interests | interests | hidden | yes |
| limits | limits | hidden | yes |
| looking_for | looking for | hidden | yes |

### 5.4 Section: Custom Fields

User-created fields. Stored in user_meta as array.

```php
$custom_fields = [
  [
    'id' => 'custom_1704567890',
    'label' => 'favorite bar',
    'value' => 'Berlin',
    'visibility' => 'yellow',
  ],
  // ...
];

update_user_meta($user_id, 'unmask_custom_fields', $custom_fields);
```

---

## 6. Custom Field Creation

### 6.1 UI Layout

Both label and value fields visible on one line:

```
┌────────────┬─────────────────────────────┬────────────────┐
│ [label___] │ [value_____________________]│ ○  ○  ○  ○     │
└────────────┴─────────────────────────────┴────────────────┘
```

### 6.2 Validation Rules

**Minimum 2 characters for both label and value**

```javascript
// Client-side validation
const MIN_CHARS = 2;

function validateCustomField(label, value) {
  const errors = [];

  if (label.length < MIN_CHARS) {
    errors.push({ field: 'label', rule: `min_char_${MIN_CHARS}` });
  }

  if (value.length < MIN_CHARS) {
    errors.push({ field: 'value', rule: `min_char_${MIN_CHARS}` });
  }

  return errors;
}
```

```php
// Server-side validation (PHP)
function unmask_validate_custom_field($label, $value) {
    $min_chars = 2;
    $errors = [];

    if (strlen($label) < $min_chars) {
        $errors['label'] = sprintf('min_char_%d', $min_chars);
    }

    if (strlen($value) < $min_chars) {
        $errors['value'] = sprintf('min_char_%d', $min_chars);
    }

    return $errors;
}
```

### 6.3 Error Indicator

**Red triangle icon** — no text, just visual indicator

```css
.field-error-indicator {
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-bottom: 10px solid var(--visibility-red);
  display: inline-block;
  margin-left: 4px;
}

.field-error-indicator::after {
  content: attr(data-error);  /* e.g., "min_char_2" */
  position: absolute;
  /* tooltip styles */
}
```

```html
<!-- Error state -->
<span class="field-error-indicator" data-error="min_char_2" title="Minimum 2 characters"></span>
```

---

## 7. Interlink System

### 7.1 Use BuddyBoss Friends System

**Do NOT create custom interlinks table.** Use BuddyBoss's built-in friends system:

```php
// Check if two users are friends (mutual connection)
$status = BP_Friends_Friendship::check_is_friend($user_a, $user_b);
// Returns: 'is_friend' | 'not_friends' | 'pending' | 'awaiting_response' | false

// Simpler check
function unmask_are_interlinked($user_a, $user_b) {
    if (!function_exists('friends_check_friendship')) {
        return false;
    }
    return friends_check_friendship($user_a, $user_b);
}
```

### 7.2 Interlink Status Labels (for display)

| BuddyBoss Status | UNMASK Label |
|------------------|--------------|
| `not_friends` | not filed |
| `pending` | pending |
| `awaiting_response` | unanswered |
| `is_friend` | interlinked |

### 7.3 Dossier Hero Display

```php
// In dossier-hero.php template
$profile_user_id = bp_displayed_user_id();
$current_user_id = get_current_user_id();

if ($current_user_id && $current_user_id !== $profile_user_id) {
    $status = BP_Friends_Friendship::check_is_friend($current_user_id, $profile_user_id);
    $label = unmask_get_interlink_label($status);
    ?>
    <div class="interlink-status interlink-status--<?php echo esc_attr($status); ?>">
        <span class="interlink-status__label">interlink status:</span>
        <span class="interlink-status__value"><?php echo esc_html($label); ?></span>
    </div>
    <?php
}
```

---

## 8. AJAX Endpoints

### 8.1 Save Field Visibility

```php
add_action('wp_ajax_unmask_save_field_visibility', 'unmask_save_field_visibility');

function unmask_save_field_visibility() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    $user_id = get_current_user_id();
    $field_key = sanitize_key($_POST['field_key']);
    $visibility = sanitize_key($_POST['visibility']);

    // Validate visibility value
    $allowed = ['green', 'yellow', 'red', 'hidden'];
    if (!in_array($visibility, $allowed)) {
        wp_send_json_error('Invalid visibility value');
    }

    $field_visibility = get_user_meta($user_id, 'unmask_field_visibility', true) ?: [];
    $field_visibility[$field_key] = $visibility;
    update_user_meta($user_id, 'unmask_field_visibility', $field_visibility);

    wp_send_json_success(['field' => $field_key, 'visibility' => $visibility]);
}
```

### 8.2 Save Section Lock

```php
add_action('wp_ajax_unmask_save_section_lock', 'unmask_save_section_lock');

function unmask_save_section_lock() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    $user_id = get_current_user_id();
    $section_id = sanitize_key($_POST['section_id']);
    $locked = $_POST['locked'] === 'null' ? null : sanitize_key($_POST['locked']);

    $section_meta = get_user_meta($user_id, 'unmask_section_meta', true) ?: [];
    $section_meta[$section_id]['locked'] = $locked;
    update_user_meta($user_id, 'unmask_section_meta', $section_meta);

    // If locking, update all field visibilities in this section
    if ($locked) {
        $fields_in_section = unmask_get_section_fields($section_id);
        $field_visibility = get_user_meta($user_id, 'unmask_field_visibility', true) ?: [];

        foreach ($fields_in_section as $field_key) {
            // Don't change hidden fields
            if (($field_visibility[$field_key] ?? 'hidden') !== 'hidden') {
                $field_visibility[$field_key] = $locked;
            }
        }

        update_user_meta($user_id, 'unmask_field_visibility', $field_visibility);
    }

    wp_send_json_success(['section' => $section_id, 'locked' => $locked]);
}
```

### 8.3 Save Field Value

```php
add_action('wp_ajax_unmask_save_field_value', 'unmask_save_field_value');

function unmask_save_field_value() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    $user_id = get_current_user_id();
    $field_key = sanitize_key($_POST['field_key']);
    $value = sanitize_textarea_field($_POST['value']);

    // For xProfile fields
    $field_id = unmask_get_xprofile_field_id($field_key);
    if ($field_id) {
        xprofile_set_field_data($field_id, $user_id, $value);
    }

    wp_send_json_success(['field' => $field_key, 'value' => $value]);
}
```

### 8.4 Add/Delete Custom Field

```php
add_action('wp_ajax_unmask_add_custom_field', 'unmask_add_custom_field');

function unmask_add_custom_field() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    $user_id = get_current_user_id();
    $label = sanitize_text_field($_POST['label']);
    $value = sanitize_text_field($_POST['value']);

    // Validation: min 2 characters each
    $errors = unmask_validate_custom_field($label, $value);
    if (!empty($errors)) {
        wp_send_json_error(['errors' => $errors]);
    }

    // Rate limit: max 10 custom fields
    $custom_fields = get_user_meta($user_id, 'unmask_custom_fields', true) ?: [];
    if (count($custom_fields) >= 10) {
        wp_send_json_error(['message' => 'Maximum custom fields reached']);
    }

    $new_field = [
        'id' => 'custom_' . time(),
        'label' => $label,
        'value' => $value,
        'visibility' => 'hidden',  // Default to hidden
    ];

    $custom_fields[] = $new_field;
    update_user_meta($user_id, 'unmask_custom_fields', $custom_fields);

    wp_send_json_success(['field' => $new_field]);
}

add_action('wp_ajax_unmask_delete_custom_field', 'unmask_delete_custom_field');

function unmask_delete_custom_field() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    $user_id = get_current_user_id();
    $field_id = sanitize_key($_POST['field_id']);

    $custom_fields = get_user_meta($user_id, 'unmask_custom_fields', true) ?: [];
    $custom_fields = array_filter($custom_fields, fn($f) => $f['id'] !== $field_id);
    update_user_meta($user_id, 'unmask_custom_fields', array_values($custom_fields));

    wp_send_json_success(['deleted' => $field_id]);
}
```

---

## 9. File Structure

```
unmask-child-theme/
├── functions.php
├── style.css
├── assets/
│   └── css/
│       ├── 00-design-system.css    # Add visibility tokens here
│       └── pages/
│           └── dossier.css         # Dossier page styles
├── inc/
│   └── dossier/
│       ├── functions.php           # Core dossier functions
│       ├── ajax.php                # AJAX handlers
│       └── fields.php              # Field definitions
├── buddypress/
│   └── members/
│       └── single/
│           ├── member-header.php   # Override for dossier hero
│           └── profile/
│               └── profile-loop.php # Override for field display
├── template-parts/
│   └── dossier/
│       ├── header.php              # Dossier header
│       ├── section-identity.php
│       ├── section-creative.php
│       ├── section-kink.php
│       └── section-custom.php
└── assets/
    └── js/
        └── dossier.js              # Frontend interactivity
```

---

## 10. CSS Classes Reference

```css
/* Sections */
.dossier-section { }
.dossier-section--open { }
.dossier-section--locked { }
.dossier-section__header { }
.dossier-section__label { }
.dossier-section__controls { }

/* Fields */
.dossier-field { }
.dossier-field--hidden { }
.dossier-field--editable { }
.dossier-field__label { }
.dossier-field__value { }
.dossier-field__controls { }

/* Visibility */
.visibility-circle { }
.visibility-circle--green { }
.visibility-circle--yellow { }
.visibility-circle--red { }
.visibility-circle--gray { }
.visibility-circle--active { }
.visibility-circle--disabled { }

/* Interlink */
.interlink-status { }
.interlink-status--not_friends { }
.interlink-status--pending { }
.interlink-status--awaiting_response { }
.interlink-status--is_friend { }

/* Errors */
.field-error-indicator { }
```

---

## 11. Implementation Order

1. **CSS tokens** — Add visibility colors to design system
2. **PHP functions** — Visibility logic, field definitions, memoization
3. **AJAX endpoints** — Save visibility, lock sections, save values, custom fields
4. **Template parts** — Section templates with visibility circles
5. **BuddyBoss overrides** — member-header.php, profile-loop.php
6. **JavaScript** — Click handlers, inline editing, validation
7. **Testing** — Verify visibility logic across user states

---

## 12. Key Constraints

- **NO inline CSS** — All styles in external stylesheets
- **NO Elementor** — Pure PHP templates
- **All circles same size** — 16px throughout
- **Default visibility = hidden** — User must explicitly reveal
- **Minimum 2 characters** — For custom field label and value
- **Max 10 custom fields** — Rate limit to prevent abuse
- **Registration = minimal** — Only name, email, password
- **Use BuddyBoss friends** — No custom interlinks table
- **Section unlock = preserve states** — Fields keep their individual visibility when section is unlocked

---

## 13. Security Checklist

- [ ] All AJAX endpoints use `check_ajax_referer()`
- [ ] All output uses `esc_html()` or `esc_attr()`
- [ ] Custom field labels sanitized with `sanitize_text_field()`
- [ ] Custom field count limited to 10
- [ ] Visibility values validated against allowlist
- [ ] User can only edit their own profile
