# Forms Inventory Audit

Last updated: 2025-01-05

## Overview

Comprehensive inventory of all forms on the UNMASK site, their handlers, and current status.

---

## Working Forms

### 1. ISO Submit Form
| Property | Value |
|----------|-------|
| **Location** | `/template-parts/forms/iso-submit-form.php` |
| **Handler** | `unmask_ajax_iso_submit` in `/inc/enqueue-iso-form.php` |
| **Method** | AJAX (wp_ajax) |
| **Auth** | Logged-in users only |
| **Shortcode** | `[unmask_iso_submit_form]` or `[iso_submit_form]` |

5-step wizard: Type → Category → Title/Description → Logistics → Review

Creates `iso` custom post type with ACF fields:
- `iso_type` (seeking/offering)
- `iso_category` (photographer/model/collaborator/connection)
- `iso_location` (chicago/remote/other)
- `iso_factory` (yes/preferred/open/no)
- `iso_expiration` (date, max 30 days)

### 2. Registration Form
| Property | Value |
|----------|-------|
| **Location** | `/page-templates/page-register-visitor.php` |
| **Handler** | MemberPress plugin |
| **Method** | Plugin handles submission |
| **Shortcode** | `[mepr-membership-registration-form id="2093"]` |

Custom additions:
- Function checkbox grid (synced to `unmask_functions` user meta)
- Custom xProfile field sync

### 3. Admin Activity Post (Pink Panthers)
| Property | Value |
|----------|-------|
| **Location** | `/template-parts/pink-panthers/concept-updates.php` |
| **Handler** | `unmask_pp_post_update` in `functions.php:856-881` |
| **Method** | admin_post action |
| **Auth** | Administrators only |

Posts to BuddyBoss activity feed.

---

## Broken Forms

### 1. Performer Form (Main Pink Panthers Page)

**Location:** `/page-templates/page-pink-panthers.php:76-98`

```php
<form>  <!-- NO action, NO method, NO nonce -->
    <select class="pp-form-select">...</select>
    <textarea class="pp-form-textarea">...</textarea>
    <button type="submit">submit act</button>
</form>
```

**Status:** Completely non-functional. Submit does nothing.

**Fix needed:**
- Add `method="post"`
- Add `action="<?php echo admin_url('admin-post.php'); ?>"`
- Add nonce field
- Add `<input type="hidden" name="action" value="pp_performer_submit">`

---

### 2. Volunteer Form

**Location:** `/page-templates/page-pink-panthers.php:128-143`

```php
<form>  <!-- NO action, NO method, NO handler -->
    <select class="pp-form-select">...</select>
    <button type="submit">volunteer</button>
</form>
```

**Status:** Completely non-functional. No handler exists.

**Fix needed:**
- Create handler function
- Wire form to handler
- Add nonce for security

---

### 3. Performer Form (Concept Call Template Part)

**Location:** `/template-parts/pink-panthers/concept-call.php:42-74`

```php
<form class="pp-performer-form" method="post" action="">
    <?php wp_nonce_field('pp_performer_submit', 'pp_nonce'); ?>
    <!-- fields... -->
</form>
```

**Status:** Handler exists but form is incorrectly wired.

Handler location: `functions.php:821-851` — Sends email to admin.

**Fix needed (line 42):**
```php
<form class="pp-performer-form" method="post"
      action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('pp_performer_submit', 'pp_nonce'); ?>
    <input type="hidden" name="action" value="pp_performer_submit">
```

---

### 4. Notify Me Button

**Location:** `/page-templates/page-pink-panthers.php:113`

```php
<button class="pp-notify-btn">notify me</button>
```

**Status:** Just a button with no functionality.

**Options:**
- Wire to Mailchimp/newsletter signup
- Create custom email list
- Remove if not needed

---

## External/Plugin Forms

| Shortcode | Location | Handler |
|-----------|----------|---------|
| `[factory_booking]` | `/page-the-factory.php:220` | External plugin |
| `[mepr-membership-registration-form]` | Registration page | MemberPress |
| `[gravityform]` | Commented out in concept-call.php | Not active |

---

## AJAX Handlers (Non-Form)

| Handler | File | Purpose | Auth |
|---------|------|---------|------|
| `unmask_ajax_iso_filter` | `inc/enqueue-iso-board.php:141` | Filter ISO listings | Public |
| `unmask_ajax_iso_detail` | `inc/enqueue-iso-board.php:225` | Get ISO for modal | Public |
| `unmask_ajax_iso_submit` | `inc/enqueue-iso-form.php:211` | Submit new ISO | Logged-in |
| `unmask_get_shuffle_records` | `inc/enqueue-archive-magazine.php:190` | Magazine shuffle | Public |
| `unmask_filter_archive_records` | `inc/enqueue-archive-magazine.php:356` | Magazine filter | Public |

---

## Missing Forms (Should Exist)

### 1. Edit/Delete My ISOs
Users can create ISO listings but cannot:
- Edit their own listings
- Delete/expire early
- See a "My ISOs" dashboard

### 2. Update Profile Functions
The function checkboxes from registration cannot be updated later (separate from xProfile).

### 3. Cancel Factory Booking
If bookings exist, there's no visible cancellation form.

---

## Summary

| Category | Count |
|----------|-------|
| Working forms | 3 |
| Broken forms | 4 |
| Plugin forms | 2 |
| AJAX handlers | 5 |
| Missing features | 3 |
