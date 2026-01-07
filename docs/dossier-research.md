# Research Results: Dossier Notebook System Analysis

## 1. Can BuddyBoss's native visibility system be extended?

Yes, and it's well-supported. BuddyBoss provides two key hooks:

**bp_xprofile_get_visibility_levels** - Add/remove visibility levels:
```php
add_filter('bp_xprofile_get_visibility_levels', function($levels) {
    $levels['interlinked'] = [
        'id' => 'interlinked',
        'label' => __('Connections Only', 'unmask')
    ];
    return $levels;
});
```

**bp_xprofile_get_hidden_field_types_for_user** - Implement hiding logic:
```php
add_filter('bp_xprofile_get_hidden_field_types_for_user', function($hidden, $displayed_user_id, $current_user_id) {
    if ($displayed_user_id != $current_user_id && !is_super_admin()) {
        if (!friends_check_friendship($displayed_user_id, $current_user_id)) {
            $hidden[] = 'interlinked';
        }
    }
    return $hidden;
}, 10, 3);
```

**Default visibility levels available:**

| Key        | Label       | Who can see                   |
|------------|-------------|-------------------------------|
| public     | Anyone      | Everyone including logged-out |
| loggedin   | All Members | Logged-in users only          |
| friends    | My Friends  | Friend connections only       |
| adminsonly | Only Me     | User + admins                 |

**Storage:** Visibility is stored per-user in wp_usermeta under `bp_xprofile_visibility_levels` as a serialized array mapping field IDs to visibility level strings.

**Sources:**
- https://www.buddyboss.com/resources/reference/functions/bp_xprofile_get_visibility_levels/
- https://buddydev.com/extending-buddypress-profile-field-visibility/
- https://github.com/buddydev/extended-xprofile-field-visibility-levels

---

## 2. Could "interlink" use BuddyBoss's existing friend system?

Yes — BuddyBoss already has exactly what's needed:

```php
// Check if two users are friends (confirmed mutual connection)
$status = BP_Friends_Friendship::check_is_friend($user_a, $user_b);
// Returns: 'is_friend' | 'not_friends' | 'pending' | 'awaiting_response' | false

// Simpler template function
$status = bp_is_friend($user_id);
// Returns: 'is_friend' | 'not_friends' | 'pending' | false
```

BuddyBoss friendships are already mutual by design — both parties must accept. The `is_friend` status means confirmed bidirectional relationship.

**Built-in caching:** `BP_Friends_Friendship::check_is_friend()` uses `bp_core_get_incremented_cache()` with key format `userid1:userid2` in the `bp_friends` cache group. This prevents repeated DB queries.

Your existing code already uses this (line 288-293 in `inc/dossier/functions.php`):
```php
function unmask_are_users_connected($user_id_1, $user_id_2) {
    if (!function_exists('friends_check_friendship')) {
        return false;
    }
    return friends_check_friendship($user_id_1, $user_id_2);
}
```

**Recommendation:** No custom `wp_unmask_interlinks` table needed. The BuddyBoss friend system IS your interlink system.

**Sources:**
- https://www.buddyboss.com/resources/reference/functions/bp_is_friend/
- https://www.buddyboss.com/resources/reference/classes/bp_friends_friendship/check_is_friend/

---

## 3. Can repeater fields handle custom user-created fields?

**Partially.** BuddyBoss has native repeater support:

- Admins create a Field Set → enable "Repeater Set" toggle
- Users see "Add Another" button to add multiple entries
- Good for structured data (education, work history)

**Limitations for user-created fields:**
- Only admins can create field sets and fields
- Users cannot create arbitrary custom fields through the UI
- Repeater is for multiple instances of the same field, not custom field creation

**Known issues:**
- Some users report repeater fields duplicating on backend
- Frontend can miss fields with certain visibility parameters

For true user-created custom fields, you'd still need:
- Custom user_meta storage (as your spec proposes)
- Or a dedicated custom fields table

**Sources:**
- https://www.buddyboss.com/resources/docs/components/profiles/profile-repeater-fields/
- https://www.buddyboss.com/resources/docs/integrations/buddypress-add-ons/buddypress-xprofile-custom-field-types/

---

## 4. Performance/Caching Analysis

**BuddyBoss has built-in xProfile caching:**

```php
// Cache groups:
'xprofile_group_meta'  // Field groups
'xprofile_field_meta'  // Field definitions
'xprofile_data_meta'   // User field values

// Key pattern for user data: "{$user_id}:{$field_id}"
```

`BP_XProfile_ProfileData::get_data_for_user()` primes cache by:
1. Checking which fields are uncached
2. Querying only uncached fields
3. Caching empty results to prevent repeated misses

**Your visibility checks add overhead.** Each `unmask_can_view_section()` call does:
- `get_user_meta()` (cached by WP)
- Possibly `friends_check_friendship()` (cached by BP)

**Recommendation:** Your current section-level approach (7 sections) is more efficient than per-field visibility. But consider memoizing results per-request:

```php
function unmask_can_view_section($owner_id, $viewer_id, $section) {
    static $cache = [];
    $key = "{$owner_id}:{$viewer_id}:{$section}";

    if (!isset($cache[$key])) {
        $cache[$key] = _unmask_check_visibility($owner_id, $viewer_id, $section);
    }
    return $cache[$key];
}
```

**Sources:**
- https://www.buddyboss.com/resources/reference/functions/bp_xprofile_update_meta_cache/
- https://github.com/buddyboss/buddyboss-platform/issues/1417

---

## 5. Your Current Implementation Analysis

Looking at `inc/dossier/functions.php`, you've already built a hybrid approach:

| Feature            | Your Implementation          | BuddyBoss Native                        |
|--------------------|------------------------------|-----------------------------------------|
| Field data         | `xprofile_get_field_data()` ✓| Same                                    |
| Visibility storage | user_meta per-section        | `bp_xprofile_visibility_levels` per-field|
| Visibility levels  | user/visitor/interlinked     | public/loggedin/friends/adminsonly      |
| Friend checking    | `friends_check_friendship()` ✓| Same                                   |
| Section lock       | Not implemented              | Not native                              |

You're doing **section-level visibility**, BuddyBoss does **per-field**. This is actually a reasonable simplification for UX, but means:
- You can't use BuddyBoss's visibility UI
- You must hide/show entire sections yourself

---

## Recommendations

1. **Keep your section-level visibility** — it's simpler UX than per-field. But rename levels to match BuddyBoss mental model:
   - `user` → `public` (everyone)
   - `visitor` → `loggedin` (members)
   - `interlinked` → `friends` (connections)

2. **Don't build custom interlink table** — BuddyBoss friends system is identical and already cached.

3. **Add request-level memoization** to `unmask_can_view_section()` and `unmask_get_section_visibility()`.

4. **Section unlock behavior** (from the red flag): Define it explicitly. Suggest: "Unlocking preserves current field states" (no revert, no destructive change).

5. **For custom user fields:** If truly needed, use user_meta with a counter limit (max 5 custom fields) and sanitize on output, not just input.
