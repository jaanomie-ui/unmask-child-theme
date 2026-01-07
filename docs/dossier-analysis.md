# Dossier Notebook System — Analysis

This is a substantial spec. Let me break it down systematically.

## What It Does (Summary)

A custom profile editor layered on top of BuddyBoss where:
- Fields are grouped into sections (identity, creative, kink, custom)
- Each field has 4 visibility levels: interlinked (mutual connections), user (logged-in), visitor (public), hidden
- Sections can be "locked" to apply uniform visibility to all fields
- Users can create custom fields
- An "interlink" system handles mutual-connection relationships

---

## Pros

1. **Privacy-first architecture**
   Default to hidden is the right call for a community dealing with sensitive personal data. Users explicitly reveal rather than accidentally expose.

2. **Clean mental model**
   The color-coded visibility (green/yellow/red/gray) is intuitive. The section lock pattern ("set all fields to this level") is a nice UX shortcut.

3. **Minimal registration friction**
   Only 3 fields at signup — everything else is progressive disclosure on the Dossier page. Good for conversion.

4. **WordPress-native storage**
   Using user_meta and BuddyBoss xProfile fields means you're not fighting the platform. Portable and debuggable.

5. **Well-structured file organization**
   The PHP class separation (class-dossier-visibility.php, class-interlink.php, etc.) follows WordPress patterns. Template parts are logically named.

6. **The React prototype is useful**
   Even though it's client-only, having a working visual reference prevents ambiguity during implementation.

---

## Cons

1. **Duplicates BuddyBoss functionality**
   BuddyBoss already has profile field visibility. You're rebuilding this with a different UI. Worth asking: could you customize the existing system instead?

2. **Dual storage creates sync risk**
   ```php
   // Field value in xProfile
   xprofile_set_field_data($field_id, $user_id, $value);

   // Visibility in user_meta
   update_user_meta($user_id, 'unmask_field_visibility', $field_visibility);
   ```
   If someone edits their profile via the standard BuddyBoss UI, visibility meta won't update. You'll need to hook into xprofile_data_after_save and similar actions.

3. **No caching strategy**
   unmask_can_view_field() will be called potentially dozens of times per profile view. Each call hits:
   - get_user_meta() (serialized array lookup)
   - Possibly unmask_are_interlinked() (database query)

   For a high-traffic site, this needs object caching or at minimum per-request memoization.

4. **Custom fields don't scale**
   Storing custom fields as a serialized array in user_meta:
   ```php
   $custom_fields = [
     ['id' => 'custom_1704567890', 'label' => 'favorite bar', ...],
   ];
   ```
   You can't query "all users who have a custom field labeled X" without loading every user's meta. If you need search/filtering later, you'll need to migrate to a proper table.

5. **Interlink table design is awkward**
   The schema requires two rows to represent a mutual connection (A→B pending, B→A accepted, etc.). Consider a single-row approach:
   ```sql
   CREATE TABLE wp_unmask_interlinks (
     id BIGINT PRIMARY KEY,
     user_a BIGINT NOT NULL,  -- lower user_id always
     user_b BIGINT NOT NULL,  -- higher user_id always
     status_a_to_b ENUM('pending','accepted','rejected'),
     status_b_to_a ENUM('pending','accepted','rejected'),
     UNIQUE KEY (user_a, user_b)
   );
   ```
   This halves storage and simplifies "are they interlinked" queries.

---

## Red Flags

### Security gaps

Missing input sanitization on custom field labels:
```php
$label = sanitize_text_field($_POST['label']);
```
sanitize_text_field doesn't prevent XSS if the label is rendered without escaping. The template must use esc_html() or esc_attr() on output.

No rate limiting on AJAX endpoints:
A malicious user could spam unmask_add_custom_field to bloat the database. Add a wp_verify_nonce + field count limit.

### Race conditions

```php
// Save Section Lock endpoint
if ($locked) {
    foreach ($fields_in_section as $field_key) {
        $field_visibility[$field_key] = $locked;
    }
}
```
If a user clicks a field visibility circle while the section lock AJAX is in flight, you'll have conflicting updates. Consider optimistic locking or queuing.

### BuddyBoss override fragility

```
templates/buddyboss/members/single/profile.php
```
This overrides a BuddyBoss core template. When BuddyBoss updates, your override may break or miss new features. Document which version you're overriding and set up diff alerts.

### The React prototype is misleading

The React component handles state client-side:
```javascript
const [sections, setSections] = useState([...]);
```
The actual PHP implementation will re-render on each page load from database state. Behavior like "unlock section → field states revert" needs to be spec'd in PHP, not assumed from the React demo.

### Section unlock behavior undefined

If I lock a section to yellow, all non-hidden fields become yellow. Then I click gray (unlock). Do fields:
- Stay yellow? (persist lock state)
- Revert to their pre-lock state? (need to store previous state)
- Become hidden? (destructive)

The spec doesn't say. This will cause implementation confusion.

### No indexes on interlinks table

```sql
CREATE TABLE wp_unmask_interlinks (
  ...
  UNIQUE KEY unique_pair (user_id, target_user_id)
);
```
Missing: INDEX (target_user_id) for "who sent me interlink requests" queries. Also missing: INDEX (status) for "all pending requests".

### Error handling absent

What happens when:
- AJAX call fails mid-edit?
- User has JS disabled?
- xProfile field doesn't exist?

The spec shows happy-path only. Production needs error states, fallbacks, and user feedback.

---

## Questions to Resolve Before Building

1. Can BuddyBoss's native visibility system be extended instead of replaced? Check the xprofile_allowed_visibility_levels filter.
2. What's the expected user scale? 100 users vs 10,000 users changes whether user_meta arrays are acceptable.
3. How critical is the interlink system? If it's central to the product, the table design needs more thought (indexes, caching, possibly a graph database).
4. Who maintains this long-term? Custom profile systems are ongoing work. BuddyBoss updates, PHP version changes, etc.
5. What happens to existing profiles? If migrating from standard BuddyBoss profiles, you need a migration script.

---

## Verdict

The spec is thoughtfully designed but overengineered for what might be achievable by customizing BuddyBoss's existing systems. The visibility metaphor is strong, but the implementation creates parallel systems that will drift from BuddyBoss core.

**Recommendation:** Before building, spend 2-4 hours investigating:
1. BuddyBoss xProfile visibility hooks (bp_xprofile_get_visibility_levels, etc.)
2. Whether the "section lock" UX can be achieved via JS on the existing profile edit screen
3. Whether custom fields can use xProfile's repeater field type

If those don't work, this spec is a reasonable foundation — but add caching, fix the interlinks schema, and document the section unlock behavior first.
