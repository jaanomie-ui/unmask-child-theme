# Pink Panthers Forms Fix Plan

**Goal**: Wire 3 broken forms to submit data into WordPress as custom post types, viewable in WP Admin.

---

## Architecture Overview

```
USER SUBMITS FORM
       ↓
admin-post.php receives POST
       ↓
WordPress routes to handler via 'action' field
       ↓
Handler verifies nonce, sanitizes input
       ↓
Creates CPT post (pp_submission)
       ↓
Redirects back with success message
```

---

## Step 1: Register Custom Post Type

**File**: `inc/pink-panthers-submissions.php` (new file)

Creates `pp_submission` post type with:
- **Title**: Auto-generated (e.g., "Performer: Jane Doe - Drag")
- **Post meta**: Stores all form fields
- **Admin columns**: Type, Name, Status, Date
- **Statuses**: `pending` (new), `approved`, `rejected`

```php
register_post_type('pp_submission', [
    'labels' => ['name' => 'PP Submissions', ...],
    'public' => false,
    'show_ui' => true,
    'menu_icon' => 'dashicons-tickets-alt',
    'supports' => ['title'],
    'capability_type' => 'post',
]);
```

---

## Step 2: Fix Form Markup

### Form 1: Performer (page-pink-panthers.php:76-97)

**Current**: `<form>` with no attributes
**Fixed**:
```php
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('pp_performer_submit', 'pp_performer_nonce'); ?>
    <input type="hidden" name="action" value="pp_performer_submit">
    <input type="hidden" name="submission_type" value="performer">

    <select name="act_type" required>...</select>
    <textarea name="act_description" required>...</textarea>
    <button type="submit">submit act</button>
</form>
```

### Form 2: Volunteer/House (page-pink-panthers.php:128-143)

**Current**: `<form>` with no attributes
**Fixed**:
```php
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('pp_volunteer_submit', 'pp_volunteer_nonce'); ?>
    <input type="hidden" name="action" value="pp_volunteer_submit">
    <input type="hidden" name="submission_type" value="volunteer">

    <select name="role_interest" required>...</select>
    <button type="submit">volunteer</button>
</form>
```

### Form 3: Concept-Call (concept-call.php:42-74)

**Current**: Has nonce but `action=""`
**Fixed**:
```php
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('pp_performer_submit', 'pp_performer_nonce'); ?>
    <input type="hidden" name="action" value="pp_performer_submit">
    <input type="hidden" name="submission_type" value="performer">
    <!-- existing fields already have name attrs -->
</form>
```

---

## Step 3: Create Form Handlers

**File**: `inc/pink-panthers-submissions.php`

### Handler: Performer Submissions

```php
add_action('admin_post_pp_performer_submit', 'unmask_handle_pp_performer');
add_action('admin_post_nopriv_pp_performer_submit', 'unmask_handle_pp_performer');

function unmask_handle_pp_performer() {
    // 1. Verify nonce
    if (!wp_verify_nonce($_POST['pp_performer_nonce'], 'pp_performer_submit')) {
        wp_die('Security check failed');
    }

    // 2. Sanitize inputs
    $performer_name = sanitize_text_field($_POST['performer_name'] ?? '');
    $act_type = sanitize_text_field($_POST['act_type'] ?? '');
    $act_description = sanitize_textarea_field($_POST['act_description'] ?? '');
    $performer_link = esc_url_raw($_POST['performer_link'] ?? '');

    // 3. Get user info (if logged in)
    $user_id = get_current_user_id();
    $user_email = $user_id ? get_userdata($user_id)->user_email : '';

    // 4. Create submission post
    $post_id = wp_insert_post([
        'post_type' => 'pp_submission',
        'post_status' => 'pending',
        'post_title' => sprintf('Performer: %s - %s', $performer_name ?: 'Anonymous', $act_type),
    ]);

    // 5. Save meta
    if ($post_id) {
        update_post_meta($post_id, '_pp_type', 'performer');
        update_post_meta($post_id, '_pp_name', $performer_name);
        update_post_meta($post_id, '_pp_act_type', $act_type);
        update_post_meta($post_id, '_pp_description', $act_description);
        update_post_meta($post_id, '_pp_link', $performer_link);
        update_post_meta($post_id, '_pp_user_id', $user_id);
        update_post_meta($post_id, '_pp_user_email', $user_email);
    }

    // 6. Redirect with success
    $redirect_url = add_query_arg('pp_submitted', 'performer', wp_get_referer());
    wp_safe_redirect($redirect_url);
    exit;
}
```

### Handler: Volunteer Submissions

```php
add_action('admin_post_pp_volunteer_submit', 'unmask_handle_pp_volunteer');
add_action('admin_post_nopriv_pp_volunteer_submit', 'unmask_handle_pp_volunteer');

function unmask_handle_pp_volunteer() {
    // Similar pattern - verify nonce, sanitize, create post, redirect
}
```

---

## Step 4: Admin UI Enhancements

### Custom Columns in WP Admin

Show at-a-glance info in the submissions list:

| Title | Type | Act/Role | Status | Date |
|-------|------|----------|--------|------|
| Performer: Jane - Drag | Performer | Drag | Pending | Jan 9 |
| Volunteer: John | Volunteer | Photography | Approved | Jan 8 |

### Meta Box for Viewing Details

When editing a submission, show all fields in a clean meta box:
- Name / Stage Name
- Act Type or Role Interest
- Description
- Instagram / Link
- User Email (if logged in)
- Submission Date

---

## Step 5: Success Messages on Frontend

Add to `page-pink-panthers.php` at the top:

```php
<?php if (isset($_GET['pp_submitted'])): ?>
    <div class="pp-success-message">
        <?php if ($_GET['pp_submitted'] === 'performer'): ?>
            Thanks for submitting! We'll be in touch if you're selected.
        <?php elseif ($_GET['pp_submitted'] === 'volunteer'): ?>
            Thanks for volunteering! We'll reach out with next steps.
        <?php endif; ?>
    </div>
<?php endif; ?>
```

---

## Files to Create/Modify

| File | Action |
|------|--------|
| `inc/pink-panthers-submissions.php` | **CREATE** - CPT + handlers |
| `functions.php` | **MODIFY** - Add require_once |
| `page-templates/page-pink-panthers.php` | **MODIFY** - Fix 2 forms, add success message |
| `template-parts/pink-panthers/concept-call.php` | **MODIFY** - Fix action URL |
| `assets/css/pink-panthers.css` | **MODIFY** - Add success message styles |

---

## Optional Enhancements (Post-Launch)

1. **Email notifications** - Send admin email on new submission
2. **Approval workflow** - Email performer when approved/rejected
3. **Duplicate detection** - Warn if same email submits twice
4. **Submission limit** - Close forms after 6 performers confirmed

---

## Testing Checklist

- [ ] Submit performer form as guest → creates pending post
- [ ] Submit performer form as logged-in user → captures user ID
- [ ] Submit volunteer form → creates pending post with role
- [ ] Submit concept-call form → creates pending post
- [ ] Success message appears after redirect
- [ ] Submissions visible in WP Admin under "PP Submissions"
- [ ] Can approve/reject submissions
- [ ] Nonce failure shows error (test with expired form)

---

## Deployment Steps

1. Create `inc/pink-panthers-submissions.php`
2. Add require_once to `functions.php`
3. Update form templates
4. Deploy to staging via rsync
5. Test all 3 forms
6. Verify submissions appear in WP Admin
