# Profile System Audit

Last updated: 2025-01-05

## Overview

The UNMASK profile (called "Dossier") is built on BuddyBoss/BuddyPress with custom overrides in the child theme.

---

## File Structure

### Template Overrides
```
buddypress/members/single/
├── member-header.php        ← Main profile header (designation, bio, flags)
├── cover-image-header.php   ← DUPLICATE of member-header.php (delete one)
└── profile/
    └── profile-loop.php     ← Profile sections with kink lock logic
```

### CSS Files (in load order)
```
assets/css/
├── 00-design-system.css     ← Design tokens
├── 02-components.css        ← .unmask-accordion styles
├── 03-buddyboss.css         ← BuddyBoss overrides (hides accordions by default)
└── pages/profile.css        ← Profile-specific (forces accordions visible)
```

### JavaScript
```
assets/js/
└── profile-accordion.js     ← Force-expands accordions (symptom of CSS war)
```

---

## Visibility Logic

### Private vs Public View

```php
// In any profile template:
$bp_is_my_profile = bp_is_my_profile();  // Am I viewing MY profile?
$bp_displayed_user_id = bp_displayed_user_id();  // Whose profile is this?

if ($bp_is_my_profile) {
    // Show edit controls, private info
} else {
    // Show public view only
}
```

### What Changes Between Views

| Element | Own Profile | Others' Profile |
|---------|-------------|-----------------|
| Avatar change icon | Visible | Hidden |
| Edit profile link | Visible | Hidden |
| Kink section (Visitors) | Visible | **Locked** |
| Kink section (Drones) | Visible | Visible |
| Private xProfile fields | Visible | Hidden (by BuddyBoss) |

### Kink Section Lock Logic

Location: `buddypress/members/single/profile/profile-loop.php`

```php
$is_kink_section = (stripos($group_name, 'kink') !== false);
$viewer_member_type = bp_get_member_type(get_current_user_id());
$is_locked = $is_kink_section && $viewer_member_type === 'visitor' && !$bp_is_my_profile;

if ($is_locked) {
    // Show "Upgrade to Drone" prompt instead of content
}
```

---

## Known Issues

### 1. Duplicate Files
`member-header.php` and `cover-image-header.php` are **identical**. One should be deleted.

### 2. CSS Specificity War
Multiple files fight over accordion visibility:

```css
/* 03-buddyboss.css - hides by default */
.unmask-accordion { display: none !important; }

/* pages/profile.css - forces visible */
.unmask-accordion { display: block !important; }
```

The JS file (`profile-accordion.js`) runs **5 times with timeouts** as a band-aid.

### 3. Missing Helper Functions
These are called in templates but may not be defined:
- `unmask_get_user_designation()` — Called, but actual function is `unmask_get_designation()`
- `unmask_user_is_drone()` — Called but not found in functions.php

---

## xProfile Fields

BuddyBoss xProfile groups (viewable in WP Admin > Users > Profile Fields):

| Group | Fields | Visibility |
|-------|--------|------------|
| Base | Display Name, Bio | Public |
| Details | Location, Pronouns, etc. | Public |
| Kink | Roles, Interests, Limits | Drone-only (for others' profiles) |

---

## User Meta Fields

Custom user meta set during registration:

```php
// Set during registration
update_user_meta($user_id, 'unmask_functions', $functions_array);
update_user_meta($user_id, 'unmask_account_created', current_time('mysql'));

// Member type
bp_set_member_type($user_id, 'visitor');  // or 'drone'
```

---

## Recommendations

1. **Delete duplicate file** — Remove either `member-header.php` or `cover-image-header.php`
2. **Fix CSS cascade** — Remove `!important` rules, use proper specificity
3. **Remove JS band-aid** — Once CSS is fixed, `profile-accordion.js` can be deleted
4. **Audit helper functions** — Ensure `unmask_get_user_designation()` and `unmask_user_is_drone()` exist
