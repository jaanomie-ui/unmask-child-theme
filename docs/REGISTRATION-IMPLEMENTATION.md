# UNMASK Registration Page Implementation

**Status:** ✅ Code Complete, Needs WordPress Page Creation
**Date:** 2026-01-02
**Deployed:** staging4.houseofanomie.com

---

## Files Created

```
/wp-content/themes/buddyboss-theme-child/
├── assets/css/
│   └── unmask-registration.css          ← Page styles (695 lines)
├── page-templates/
│   ├── page-register-visitor.php        ← Registration template
│   └── page-welcome.php                 ← Welcome/onboarding template
└── functions.php                         ← Modified with hooks
```

---

## What Was Added to functions.php

### 1. CSS Enqueue (lines 114-127)
Conditionally loads `unmask-registration.css` only on registration/welcome templates.

### 2. MemberPress Custom Fields (lines 137-210)
- `mepr-checkout-before-submit` — Injects scene_name and pronouns fields
- `mepr-validate-signup` — Validation hook (currently passes all)
- `mepr-signup` — Saves unmask_function[], scene_name, unmask_pronouns to user meta

### 3. BuddyBoss Sync (lines 220-243)
- Syncs to xprofile fields: Function, Scene Name, Pronouns
- Runs on `mepr-signup` with priority 20

### 4. Registration Redirect (lines 252-255)
- Redirects to `/welcome/` after successful MemberPress signup

---

## Manual Steps Required

### 1. Create WordPress Pages

**Page: Register Visitor**
- Title: `Register Visitor`
- Slug: `register/visitor` or `register-visitor`
- Template: "Register Visitor"
- Content: Leave empty

**Page: Welcome**
- Title: `Welcome`
- Slug: `welcome`
- Template: "Welcome Onboarding"
- Content: Leave empty

### 2. Create BuddyBoss Profile Fields

Go to **BuddyBoss → Settings → Profiles → Profile Fields**:

| Field Name | Type | Options |
|------------|------|---------|
| Function | Multi-checkbox | Subject, Operator, Performer |
| Scene Name | Text field | — |
| Pronouns | Dropdown | they/them, he/him, she/her, he/they, she/they, ask me |

### 3. Verify MemberPress Membership ID

The template uses ID `2093` by default. To change:

```php
add_filter('unmask_visitor_membership_id', function() {
    return YOUR_MEMBERSHIP_ID;
});
```

---

## Test URLs (after pages created)

- Registration: https://staging4.houseofanomie.com/register/visitor/
- Welcome: https://staging4.houseofanomie.com/welcome/

---

## Design Details

- **Aesthetic:** Corporate but camp, terminal, drone mythology
- **Layout:** 2-column grid (form left, terminal right)
- **Mobile:** Single column, terminal hidden
- **Designation format:** V-XXX (padded user ID)

---

## Git Status

- **Commit:** `8c9da2d` - "Add UNMASK registration and welcome page templates"
- **Branch:** main
- **Remote:** Pushed to origin

---

## Reference Files

Original mockups from `/Users/ja/Downloads/`:
- `unmask-registration-mockup_1.html`
- `unmask-registration.css`
