# Dossier v3 Implementation Plan — Simplified

Last updated: 2025-01-05

## Scope

A streamlined implementation focused on shipping tonight. Maintains the terminal aesthetic and "different universe" vibe while cutting infrastructure complexity.

---

## Design Decisions

| Feature | Decision | Rationale |
|---------|----------|-----------|
| Activity stream | Restyle BuddyBoss activity | Don't build parallel system |
| Kink section | Optional creation prompt | No membership lock needed |
| Pink Panthers | Placeholder text | Feature not ready |
| ISO response count | Skip | Just show total messages |
| Factory booking | Integrate | Custom plugin has clean API |
| Credit claiming | Manual assignment | No claim workflow |
| Records tracking | Read count only | No "in progress" state |

---

## Files to Create

```
unmask-child-theme/
├── assets/
│   ├── css/
│   │   └── pages/
│   │       └── dossier.css              ← All dossier styles (NEW)
│   └── js/
│       └── dossier.js                   ← Visibility toggle, interactions (NEW)
├── inc/
│   └── dossier/
│       ├── enqueue.php                  ← Asset loading (NEW)
│       ├── functions.php                ← Helper functions (NEW)
│       └── queries.php                  ← Data queries (NEW)
└── template-parts/
    └── dossier/
        ├── header.php                   ← Profile header (NEW)
        ├── section-isos.php             ← Active ISOs (NEW)
        ├── section-practice.php         ← Creative practice (NEW)
        ├── section-kink.php             ← Kink profile (NEW)
        ├── section-credits.php          ← Magazine credits (NEW)
        └── my-unmask.php                ← Dashboard (NEW)
```

## Files to Modify

```
buddypress/members/single/member-header.php     ← Rewrite with new structure
buddypress/members/single/profile/profile-loop.php ← Replace with section includes
functions.php                                    ← Add require_once for dossier
```

## Files to Delete

```
buddypress/members/single/cover-image-header.php  ← Duplicate
assets/js/profile-accordion.js                    ← Obsolete hack
```

---

## Phase 1: CSS Foundation

**Goal:** Terminal aesthetic baseline

### 1.1 Create `assets/css/pages/dossier.css`

Port styles from HTML mockup with these blocks:
- Tokens (use existing 00-design-system.css where possible)
- Header block (.dossier-header, .dossier-avatar, .dossier-identity, etc.)
- Section block (.dossier-section, .dossier-section__head, etc.)
- ISO list block (.dossier-iso-row, .dossier-iso__type, etc.)
- Data list block (.dossier-data-row, .dossier-data__key, etc.)
- Credits block (.dossier-credit-row, etc.)
- MY UNMASK block (.my-unmask, .my-unmask-grid, .dash-cell, etc.)
- Activity stream override for BuddyBoss
- Responsive breakpoints

### 1.2 Activity Stream Override

Style BuddyBoss `.activity-list` to match terminal aesthetic:
```css
/* Override BuddyBoss activity to match dossier */
.my-unmask .activity-list { ... }
.my-unmask .activity-item { ... }
```

---

## Phase 2: PHP Functions

**Goal:** Helper functions for templates

### 2.1 Create `inc/dossier/functions.php`

```php
/**
 * Get member type (drone or visitor)
 */
function unmask_get_member_type($user_id) {
    $type = bp_get_member_type($user_id);
    return $type ?: 'visitor';
}

/**
 * Get designation string (D-047 or V-123)
 */
function unmask_get_designation($user_id) {
    $type = unmask_get_member_type($user_id);
    $prefix = ($type === 'drone') ? 'D' : 'V';
    $number = str_pad($user_id % 1000, 3, '0', STR_PAD_LEFT);
    return $prefix . '-' . $number;
}

/**
 * Get user's creative functions from xProfile
 */
function unmask_get_user_functions($user_id) {
    $functions = xprofile_get_field_data('Function', $user_id);
    if (empty($functions)) return [];
    return is_array($functions) ? $functions : explode(',', $functions);
}

/**
 * Get user's status indicators
 */
function unmask_get_user_status($user_id) {
    return [
        'bookable' => xprofile_get_field_data('Open to bookings', $user_id) === 'Yes',
        'seeking' => xprofile_get_field_data('Seeking collaborators', $user_id) === 'Yes'
    ];
}

/**
 * Check if user has created a kink profile
 */
function unmask_has_kink_profile($user_id) {
    $position = xprofile_get_field_data('Position', $user_id);
    return !empty($position);
}

/**
 * Get dashboard visibility preference
 */
function unmask_get_dashboard_visibility($user_id) {
    return get_user_meta($user_id, 'unmask_dashboard_visibility', true) ?: 'masked';
}

/**
 * Set dashboard visibility preference
 */
function unmask_set_dashboard_visibility($user_id, $visibility) {
    return update_user_meta($user_id, 'unmask_dashboard_visibility', $visibility);
}
```

### 2.2 Create `inc/dossier/queries.php`

```php
/**
 * Get user's active ISOs
 */
function unmask_get_user_isos($user_id, $limit = 10) {
    return new WP_Query([
        'post_type' => 'iso',
        'post_author' => $user_id,
        'posts_per_page' => $limit,
        'meta_query' => [
            [
                'key' => 'iso_expiration',
                'value' => date('Ymd'),
                'compare' => '>=',
                'type' => 'DATE'
            ]
        ],
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
}

/**
 * Get user's next Factory booking
 */
function unmask_get_next_factory_booking($user_id) {
    if (!class_exists('Factory_Booking_Bookings')) {
        return null;
    }

    $bookings = Factory_Booking_Bookings::get_all([
        'user_id' => $user_id,
        'date_from' => date('Y-m-d'),
        'status' => 'approved',
        'orderby' => 'booking_date',
        'order' => 'ASC',
        'limit' => 1
    ]);

    return !empty($bookings) ? $bookings[0] : null;
}

/**
 * Get user's records read count
 */
function unmask_get_records_read_count($user_id) {
    $read = get_user_meta($user_id, 'unmask_records_read', true);
    return is_array($read) ? count($read) : 0;
}

/**
 * Mark record as read
 */
function unmask_mark_record_read($user_id, $record_id) {
    $read = get_user_meta($user_id, 'unmask_records_read', true);
    if (!is_array($read)) $read = [];

    if (!in_array($record_id, $read)) {
        $read[] = $record_id;
        update_user_meta($user_id, 'unmask_records_read', $read);
    }
    return true;
}

/**
 * Get user's magazine credits (manual assignment via ACF on Records)
 * Assumes ACF repeater 'record_contributors' on posts with:
 *   - contributor_user (user ID)
 *   - contributor_role (select)
 */
function unmask_get_user_credits($user_id, $limit = 10) {
    global $wpdb;

    // Query posts where user is in contributors repeater
    // This is a simplified approach - adjust based on actual ACF structure
    $posts = get_posts([
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'meta_query' => [
            [
                'key' => 'record_contributors_%_contributor_user',
                'value' => $user_id,
                'compare' => '='
            ]
        ]
    ]);

    $credits = [];
    foreach ($posts as $post) {
        $contributors = get_field('record_contributors', $post->ID);
        if ($contributors) {
            foreach ($contributors as $contributor) {
                if ($contributor['contributor_user'] == $user_id) {
                    $credits[] = [
                        'post_id' => $post->ID,
                        'title' => $post->post_title,
                        'role' => $contributor['contributor_role'],
                        'issue' => get_field('issue_number', $post->ID) ?: '001'
                    ];
                }
            }
        }
    }

    return $credits;
}

/**
 * Get dashboard data aggregate
 */
function unmask_get_dashboard_data($user_id) {
    // ISOs
    $isos = unmask_get_user_isos($user_id);

    // Factory
    $factory = unmask_get_next_factory_booking($user_id);

    // BuddyBoss data
    $connections = function_exists('bp_get_total_friend_count')
        ? bp_get_total_friend_count($user_id) : 0;
    $pending = function_exists('friends_get_friend_user_ids')
        ? count(friends_get_friendship_request_user_ids($user_id)) : 0;
    $messages = function_exists('bp_get_total_unread_message_count')
        ? bp_get_total_unread_message_count($user_id) : 0;

    // Records & Credits
    $records_read = unmask_get_records_read_count($user_id);
    $credits = unmask_get_user_credits($user_id);

    return [
        'isos_active' => $isos->found_posts,
        'factory_next' => $factory,
        'connections' => $connections,
        'connections_pending' => $pending,
        'messages_unread' => $messages,
        'records_read' => $records_read,
        'credits' => count($credits),
        // Placeholder for Pink Panthers
        'pp_next' => null
    ];
}
```

### 2.3 Create `inc/dossier/enqueue.php`

```php
/**
 * Enqueue dossier assets on profile pages
 */
function unmask_enqueue_dossier_assets() {
    if (!bp_is_user()) {
        return;
    }

    $theme_dir = get_stylesheet_directory_uri();
    $theme_path = get_stylesheet_directory();

    // CSS
    wp_enqueue_style(
        'unmask-dossier',
        $theme_dir . '/assets/css/pages/dossier.css',
        ['buddyboss-theme-css'],
        filemtime($theme_path . '/assets/css/pages/dossier.css')
    );

    // JS
    wp_enqueue_script(
        'unmask-dossier',
        $theme_dir . '/assets/js/dossier.js',
        ['jquery'],
        filemtime($theme_path . '/assets/js/dossier.js'),
        true
    );

    wp_localize_script('unmask-dossier', 'unmaskDossier', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('unmask_dossier_nonce'),
        'isOwn' => bp_is_my_profile()
    ]);
}
add_action('wp_enqueue_scripts', 'unmask_enqueue_dossier_assets');

/**
 * AJAX: Toggle dashboard visibility
 */
function unmask_ajax_toggle_visibility() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $visibility = sanitize_text_field($_POST['visibility']);
    if (!in_array($visibility, ['masked', 'unmasked'])) {
        wp_send_json_error(['message' => 'Invalid visibility']);
    }

    unmask_set_dashboard_visibility(get_current_user_id(), $visibility);
    wp_send_json_success(['visibility' => $visibility]);
}
add_action('wp_ajax_unmask_toggle_visibility', 'unmask_ajax_toggle_visibility');

/**
 * AJAX: Expire ISO
 */
function unmask_ajax_expire_iso() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $iso_id = intval($_POST['iso_id']);
    $iso = get_post($iso_id);

    if (!$iso || $iso->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => 'Not your ISO']);
    }

    // Set expiration to yesterday
    update_field('iso_expiration', date('Ymd', strtotime('-1 day')), $iso_id);

    wp_send_json_success(['message' => 'ISO expired']);
}
add_action('wp_ajax_unmask_expire_iso', 'unmask_ajax_expire_iso');

/**
 * AJAX: Mark record as read
 */
function unmask_ajax_mark_record_read() {
    check_ajax_referer('unmask_dossier_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $record_id = intval($_POST['record_id']);
    unmask_mark_record_read(get_current_user_id(), $record_id);

    wp_send_json_success(['message' => 'Marked as read']);
}
add_action('wp_ajax_unmask_mark_record_read', 'unmask_ajax_mark_record_read');
```

---

## Phase 3: Template Parts

**Goal:** Modular components for dossier sections

### 3.1 Create `template-parts/dossier/header.php`

Displays: Avatar, name, designation, location, functions, status flags, bio, actions

### 3.2 Create `template-parts/dossier/section-isos.php`

Displays: User's active ISOs with type, title, location, factory pref, expiration
Actions: [respond] for others, [edit] [expire] for own

### 3.3 Create `template-parts/dossier/section-practice.php`

Displays: Disciplines, tools, influences from xProfile
Action: [edit] links to xProfile edit

### 3.4 Create `template-parts/dossier/section-kink.php`

Two states:
1. **Has kink profile:** Display position, interests, experience
2. **No kink profile:** Prompt "do you choose to create a kink profile?" with [create] link

### 3.5 Create `template-parts/dossier/section-credits.php`

Displays: Magazine credits with role, title (linked), issue
Footer: Manual — you assign credits via ACF on Records

### 3.6 Create `template-parts/dossier/my-unmask.php`

Only visible on own profile. Contains:
- Header with visibility toggle (mask/unmask)
- Dashboard grid (6 metrics):
  - ISOs (count)
  - Factory (next date or "—")
  - Connections (count + pending)
  - Messages (unread count)
  - Records Read (count)
  - Credits (count)
- Activity stream (restyled BuddyBoss)
- Membership bar (DRONE/VISITOR + manage link)

### 3.7 Pink Panthers Placeholder

In my-unmask.php dashboard grid:
```php
<div class="dash-cell">
    <div class="dash-key">Pink Panthers</div>
    <div class="dash-val dash-val--dim">coming soon</div>
</div>
```

---

## Phase 4: Template Overrides

### 4.1 Rewrite `member-header.php`

Replace entire contents with:
```php
<?php
get_template_part('template-parts/dossier/header');
?>
```

### 4.2 Rewrite `profile-loop.php`

Replace with section includes:
```php
<?php
// Active ISOs
get_template_part('template-parts/dossier/section-isos');

// Creative Practice
get_template_part('template-parts/dossier/section-practice');

// Kink Profile
get_template_part('template-parts/dossier/section-kink');

// Credits
get_template_part('template-parts/dossier/section-credits');

// MY UNMASK (own profile only)
if (bp_is_my_profile()) {
    get_template_part('template-parts/dossier/my-unmask');
}
?>
```

---

## Phase 5: Cleanup

### 5.1 Delete files
- `buddypress/members/single/cover-image-header.php`
- `assets/js/profile-accordion.js`

### 5.2 Remove from functions.php
- Any `profile-accordion.js` enqueue
- Old profile-specific hacks

### 5.3 CSS cleanup
- Remove profile rules from `03-buddyboss.css` (lines ~415-500)
- Keep `profile.css` as fallback or delete if fully replaced

---

## JavaScript (`assets/js/dossier.js`)

```javascript
(function($) {
    'use strict';

    // Visibility toggle
    $('.my-unmask-visibility__btn').on('click', function() {
        const visibility = $(this).data('visibility');

        $.post(unmaskDossier.ajaxUrl, {
            action: 'unmask_toggle_visibility',
            nonce: unmaskDossier.nonce,
            visibility: visibility
        }, function(response) {
            if (response.success) {
                $('.my-unmask-visibility__btn').removeClass('active');
                $('[data-visibility="' + visibility + '"]').addClass('active');
            }
        });
    });

    // Expire ISO
    $('.dossier-iso__expire').on('click', function(e) {
        e.preventDefault();

        if (!confirm('Expire this ISO? This cannot be undone.')) {
            return;
        }

        const $row = $(this).closest('.dossier-iso-row');
        const isoId = $row.data('iso-id');

        $.post(unmaskDossier.ajaxUrl, {
            action: 'unmask_expire_iso',
            nonce: unmaskDossier.nonce,
            iso_id: isoId
        }, function(response) {
            if (response.success) {
                $row.fadeOut();
            }
        });
    });

})(jQuery);
```

---

## Data Requirements

### xProfile Fields Needed

| Field | Group | Type | Notes |
|-------|-------|------|-------|
| Scene Name | Base | Text | Display name |
| Bio | Base | Textarea | Short bio |
| Pronouns | Base | Select | they/them, etc. |
| City | Base | Text | Location |
| Function | Creative Practice | Multi-checkbox | photographer, model, etc. |
| Disciplines | Creative Practice | Multi-checkbox | fashion, fetish, etc. |
| Tools | Creative Practice | Text | Equipment |
| Influences | Creative Practice | Text | Artists |
| Open to bookings | Availability | Radio | Yes/No |
| Seeking collaborators | Availability | Radio | Yes/No |
| Position | Kink Profile | Select | Kink field |
| Interests | Kink Profile | Multi-checkbox | Kink field |
| Experience | Kink Profile | Select | Kink field |

### User Meta

| Key | Type | Purpose |
|-----|------|---------|
| unmask_dashboard_visibility | string | 'masked' or 'unmasked' |
| unmask_records_read | array | Array of post IDs |

### ACF on Records (for credits)

| Field | Key | Type |
|-------|-----|------|
| Contributors | record_contributors | Repeater |
| → User | contributor_user | User select |
| → Role | contributor_role | Select |

---

## Checklist

### Phase 1: CSS
- [ ] Create `assets/css/pages/dossier.css`
- [ ] Port tokens from mockup
- [ ] Port header styles
- [ ] Port section styles
- [ ] Port ISO list styles
- [ ] Port data list styles
- [ ] Port credits styles
- [ ] Port MY UNMASK styles
- [ ] Add BuddyBoss activity override
- [ ] Add responsive breakpoints

### Phase 2: PHP
- [ ] Create `inc/dossier/functions.php`
- [ ] Create `inc/dossier/queries.php`
- [ ] Create `inc/dossier/enqueue.php`
- [ ] Add require_once in functions.php

### Phase 3: Templates
- [ ] Create `template-parts/dossier/header.php`
- [ ] Create `template-parts/dossier/section-isos.php`
- [ ] Create `template-parts/dossier/section-practice.php`
- [ ] Create `template-parts/dossier/section-kink.php`
- [ ] Create `template-parts/dossier/section-credits.php`
- [ ] Create `template-parts/dossier/my-unmask.php`

### Phase 4: Overrides
- [ ] Rewrite `member-header.php`
- [ ] Rewrite `profile-loop.php`

### Phase 5: Cleanup
- [ ] Delete `cover-image-header.php`
- [ ] Delete `profile-accordion.js`
- [ ] Clean `03-buddyboss.css`
- [ ] Test on staging

### Deployment
- [ ] rsync to staging
- [ ] chmod 644 on all new files
- [ ] Test own profile view
- [ ] Test other's profile view
- [ ] Test ISO expire
- [ ] Test visibility toggle
- [ ] Commit and push

---

## Time Estimate

| Phase | Estimate |
|-------|----------|
| Phase 1: CSS | 1-2 hours |
| Phase 2: PHP | 30-45 min |
| Phase 3: Templates | 1-1.5 hours |
| Phase 4: Overrides | 15 min |
| Phase 5: Cleanup | 15 min |
| Testing | 30 min |
| **Total** | **3-5 hours** |
