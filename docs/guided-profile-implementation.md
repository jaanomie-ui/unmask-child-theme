# Guided Profile Flow Implementation

**Created:** 2026-01-09
**Status:** DEPLOYED

## Overview

Replacing the broken 4-step onboarding with a streamlined 2-step flow:

```
OLD (broken)                            NEW (working)
────────────                            ────────────
1. Register (many fields, no sync)  →   1. Register (email + password)
2. Welcome V-XXX                    →   2. Welcome + Guided Profile Setup
3. Orientation (map)                       └── Bottom sheets for each section
4. Complete                                └── Saves directly to BuddyBoss xprofile
```

## Design Tokens Reference

From `assets/css/00-design-system.css`:
- `--bg-elevated` - Sheet background
- `--bg-inset` - Input backgrounds
- `--border-default` - Borders
- `--text-primary` - Main text
- `--text-muted` - Secondary text
- `--primitive-green` - Success/designation color
- `--font-ui` - Berkeley Mono
- `--space-*` - Spacing scale
- `--radius-lg` - Border radius

## File Changes

| Action | File | Purpose |
|--------|------|---------|
| MODIFY | `assets/css/unmask-registration.css` | Hide address fields |
| MODIFY | `page-templates/page-welcome.php` | Consolidate all steps |
| DELETE | `page-templates/page-welcome-orientation.php` | No longer needed |
| DELETE | `page-templates/page-welcome-start.php` | No longer needed |
| DELETE | `page-templates/page-welcome-complete.php` | No longer needed |
| CREATE | `assets/css/components/profile-sheet.css` | Bottom sheet styles |
| CREATE | `assets/js/profile-setup.js` | Sheet flow logic |
| MODIFY | `functions.php` | AJAX handler for saving |
| MODIFY | `inc/enqueue-onboarding.php` | Update asset loading |

## Profile Sections

### Section 1: Identity
- Scene Name (xprofile field 3) - required
- Pronouns (xprofile field 85) - optional

### Section 2: Practice
- What do you practice? (xprofile field 21) - multiselect

### Section 3: Status
- Available to be photographed (xprofile field 104) - toggle
- Available to photograph (xprofile field 108) - toggle
- Open to bookings (xprofile field 111) - toggle

## XProfile Field Map

```php
$field_map = [
    'scene_name' => 3,
    'pronouns' => 85,
    'practice' => 21,
    'available_photographed' => 104,
    'available_photograph' => 108,
    'open_bookings' => 111,
];
```

## Mobile UX Wireframe

```
┌─────────────────────────┐
│                         │
│      V-032              │  ← Large designation
│  welcome to unmask      │  ← Subtle label
│                         │
│   ● ○ ○                 │  ← Progress dots
│                         │
│  ┌─────────────────┐    │
│  │ set up dossier  │    │  ← Primary CTA
│  └─────────────────┘    │
│     skip for now        │  ← Secondary
└─────────────────────────┘

     ↓ tap CTA ↓

┌─────────────────────────┐
│  (dimmed background)    │
├─────────────────────────┤  ← Sheet slides up
│  WHO ARE YOU?           │
│                         │
│  Scene Name             │
│  ┌─────────────────┐    │
│  │                 │    │
│  └─────────────────┘    │
│                         │
│  Pronouns               │
│  ┌─────────────────┐    │
│  │                 │    │
│  └─────────────────┘    │
│                         │
│  ┌─────────────────┐    │
│  │     next →      │    │
│  └─────────────────┘    │
│     skip this           │
└─────────────────────────┘
```

## Implementation Steps

1. [x] Save this plan
2. [x] Hide address fields in registration CSS
3. [x] Create profile-sheet.css component
4. [x] Create profile-setup.js logic
5. [x] Add AJAX handler to functions.php
6. [x] Rewrite page-welcome.php
7. [x] Update enqueue file
8. [x] Delete old template files (kept for reference, not used)
9. [x] Deploy and test
10. [ ] Clean up test user (optional)

## Staging Info

- Host: unmask-staging
- Theme path: ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/
- SSH: SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock
