# UNMASK Site Audits

Technical documentation and audits for the UNMASK child theme.

## Audits

| Document | Description | Last Updated |
|----------|-------------|--------------|
| [Profile System](audit-profile.md) | BuddyBoss profile, visibility logic, CSS conflicts | 2025-01-05 |
| [Forms Inventory](audit-forms.md) | All forms, handlers, broken elements | 2025-01-05 |
| [User Features](audit-user-features.md) | Everything a user can "own" on the site | 2025-01-05 |

## Quick Reference

### Key Files
- `buddypress/members/single/` — Profile template overrides
- `inc/enqueue-*.php` — Conditional asset loading + AJAX handlers
- `template-parts/forms/` — Form templates
- `assets/css/pages/` — Page-specific styles

### Visibility Logic
```php
bp_is_my_profile()      // true if viewing own profile
bp_displayed_user_id()  // ID of profile being viewed
get_current_user_id()   // ID of logged-in user
```

### Member Types
- **Drone** — Paid member, full access
- **Visitor** — Free member, restricted access (e.g., no kink section on others' profiles)
