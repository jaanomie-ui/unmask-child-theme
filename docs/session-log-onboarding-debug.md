# UNMASK Onboarding Debug Session Log

**Last Updated:** 2026-01-09
**Status:** TESTING READY
**Context:** Fixing onboarding flow, registration form, and MemberPress → BuddyBoss sync

---

## Objective

Fix the 4-screen onboarding flow and resolve data sync issues between MemberPress (registration) and BuddyBoss (profile/dossier).

---

## Current Status

### Completed
- [x] Fixed CSS dependency mismatch (`unmask-design-system` → `unmask-00-design-system`)
- [x] Fixed bottom nav appearing on onboarding pages
- [x] Fixed header template checks for all onboarding screens
- [x] Fixed 403 permission errors on ALL theme files (many had 600 instead of 644)
- [x] Fixed dossier CSS/JS not loading (permission issue)
- [x] Fixed registration form groupings (label/input/error proximity)
- [x] Fixed premature validation errors (now hidden until form submit)
- [x] Hidden "Price: Free" row on registration form
- [x] Retrieved exact BuddyBoss xprofile field names from database
- [x] Updated MemberPress → BuddyBoss sync function with correct field names
- [x] Fixed constellation not showing on desktop (display: block → display: flex)
- [x] Condensed Step 2 mobile cards (smaller icons, 2-line clamp, tighter spacing)

### Ready for Testing
- [ ] Test registration → verify fields sync to BuddyBoss profile
- [x] Make ID field read-only in dossier edit form (filter + CSS fallback)
- [ ] Full E2E test of onboarding flow

---

## BuddyBoss XProfile Fields (EXACT NAMES)

Retrieved from database `qvq_bp_xprofile_fields`:

### Group 1: Visitor Profile
| ID | Field Name | Type | Required |
|----|------------|------|----------|
| 1 | Name | textbox | YES |
| 2 | Last Name | textbox | YES |
| 3 | Scene Name | textbox | YES |
| 84 | ID | number | NO |
| 85 | Pronouns | textbox | NO |
| 87 | City | textbox | NO |
| 88 | Bio | textarea | NO |

### Group 2: Creative Practice
| ID | Field Name | Type | Required |
|----|------------|------|----------|
| 21 | What do you practice? | multiselectbox | NO |
| 103 | Portfolio  | url | NO |
| 104 | Available to be photographed | checkbox | NO |
| 108 | Available to photograph | checkbox | NO |
| 111 | Open to bookings | checkbox | NO |

### Group 4: Kink Profile
| ID | Field Name | Type | Required |
|----|------------|------|----------|
| 57 | Dominance & Submission Orientation  | selectbox | NO |
| 71 | Kink interests | multiselectbox | NO |

---

## Data Flow Architecture

```
REGISTRATION (MemberPress)          PROFILE/DOSSIER (BuddyBoss)
─────────────────────────────       ──────────────────────────────
first_name  → wp_users.first_name   → xprofile "Name" (id: 1)
last_name   → wp_users.last_name    → xprofile "Last Name" (id: 2)
scene_name  → user_meta             → xprofile "Scene Name" (id: 3)
pronouns    → user_meta             → xprofile "Pronouns" (id: 85)
(auto)      → (generate V-XXX)      → xprofile "ID" (id: 84)
```

### Current Sync Code Location
`functions.php` line 568: `unmask_sync_to_buddyboss()`

### What's Missing
1. First name not synced to xprofile "Name"
2. Last name not synced to xprofile "Last Name"
3. Designation (V-XXX) not synced to xprofile "ID"

---

## Sync Function Fix Required

```php
// In functions.php, update unmask_sync_to_buddyboss():

add_action('mepr-signup', 'unmask_sync_to_buddyboss', 20);
function unmask_sync_to_buddyboss($txn) {
    $user_id = $txn->user_id;
    $user = get_userdata($user_id);

    if (!function_exists('xprofile_set_field_data')) return;

    // Sync first name to "Name" field (id: 1)
    if (!empty($user->first_name)) {
        xprofile_set_field_data('Name', $user_id, $user->first_name);
    }

    // Sync last name to "Last Name" field (id: 2)
    if (!empty($user->last_name)) {
        xprofile_set_field_data('Last Name', $user_id, $user->last_name);
    }

    // Sync scene name (id: 3)
    $scene_name = get_user_meta($user_id, 'scene_name', true);
    if (!empty($scene_name)) {
        xprofile_set_field_data('Scene Name', $user_id, $scene_name);
    }

    // Sync pronouns (id: 85)
    $pronouns = get_user_meta($user_id, 'unmask_pronouns', true);
    if (!empty($pronouns)) {
        xprofile_set_field_data('Pronouns', $user_id, $pronouns);
    }

    // Auto-generate and sync designation to "ID" field (id: 84)
    $designation_num = str_pad($user_id, 3, '0', STR_PAD_LEFT);
    xprofile_set_field_data('ID', $user_id, $designation_num);
}
```

---

## Constellation Debug Notes

**Issue:** Constellation map not showing on desktop at `/welcome/orientation/`

**Root Cause:** `display: block` in media query broke flex layout. The container needs to remain a flex item for proper sizing.

**Fix Applied:**
```css
@media (min-width: 768px) {
    .unmask-constellation-container {
        display: flex;
        min-height: 500px;
        align-items: center;
        justify-content: center;
    }
}
```

**Status:** FIXED (deployed 2026-01-09)

---

## Files Modified This Session

| File | Changes |
|------|---------|
| `inc/enqueue-onboarding.php` | Fixed CSS dependency name |
| `inc/enqueue-bottom-nav.php` | Added onboarding template exclusions |
| `header-fullbleed.php` | Expanded template checks |
| `assets/css/unmask-registration.css` | Form grouping, validation hiding, price hiding |
| `page-templates/page-register-visitor.php` | Added JS for error message handling |
| `functions.php` | Updated sync function with correct xprofile field names |
| `assets/css/onboarding.css` | Fixed constellation display, condensed mobile cards |

---

## Staging Server Info

- **Host:** unmask-staging
- **Theme path:** `~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/`
- **SSH auth:** `SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock`
- **DB prefix:** `qvq_`

---

## Next Steps (Priority Order)

1. ~~**Update sync function** in `functions.php`~~ ✅ DONE
2. **Test registration** → verify fields sync to BuddyBoss profile (check error_log for "UNMASK Sync:" messages)
3. ~~**Debug constellation** on desktop~~ ✅ FIXED
4. ~~**Condense Step 2 mobile**~~ ✅ DONE
5. ~~**Make ID field read-only**~~ ✅ DONE (bp_xprofile_is_field_edit_allowed filter + CSS fallback)
6. **Full E2E test** of onboarding flow

---

## Debug Commands Reference

```bash
# SSH to staging
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging

# Deploy file
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz /local/path unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/

# Fix permissions
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging "chmod 644 ~/path/to/file"

# Query xprofile fields
wp db query "SELECT id, name, type FROM qvq_bp_xprofile_fields ORDER BY group_id, field_order"

# Check user's xprofile data
wp db query "SELECT field_id, value FROM qvq_bp_xprofile_data WHERE user_id = X"
```
