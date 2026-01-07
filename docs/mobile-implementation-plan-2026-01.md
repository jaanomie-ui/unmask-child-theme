# UNMASK Mobile Implementation Plan

**Date:** January 6, 2026
**Status:** In Progress

---

## Overview

Mobile-first optimization focused on:
1. Magazine reading experience
2. Submitting/joining the project
3. Booking the Factory studio

---

## Revised Mobile Navigation

```
┌────────┬────────┬────────┬────────┬────────┐
│  Home  │Magazine│  ISO   │ Events │Dossier │
│   🏠   │   📖   │   🔗   │   📅   │   👤   │
└────────┴────────┴────────┴────────┴────────┘
```

| Position | Item | Flywheel Role |
|----------|------|---------------|
| 1 | Home | Activity + highlights |
| 2 | Magazine | **Discovery/Bait** — read content |
| 3 | ISO | **Activation** — post/respond |
| 4 | Events | IRL engagement |
| 5 | Dossier | Profile + "Me" dashboard |

**Sidebar (secondary):**
- Factory (conversion point — booking)
- Submit Content (CTA)
- Shop
- Settings

---

## Phase 1: Navigation Foundation ✅ COMPLETE

### 1.1 Sticky Bottom Nav Component
- [x] Create `template-parts/global/bottom-nav.php`
- [x] Create `assets/css/components/bottom-nav.css`
- [x] Create `inc/enqueue-bottom-nav.php`
- [x] Add body class `has-bottom-nav` for padding
- [x] 56px height + safe area inset
- [x] Active state detection
- [x] Hide on desktop (768px+)

### 1.2 Sidebar Cleanup ✅ COMPLETE
- [x] Remove groups/forums references (CSS hide)
- [x] Add prominent "Submit" CTA (PHP + JS injection)
- [x] Style sidebar to match terminal aesthetic
- [ ] Move Factory to sidebar (via WP Admin menu)
- [ ] Reorder menu items (via WP Admin menu)

### 1.3 Collapsing Header ✅ COMPLETE
- [x] JS for scroll detection
- [x] Reduce 60px → 44px on scroll
- [x] Smooth CSS transition

---

## Phase 2: Magazine Experience

### 2.1 Magazine Archive ✅ COMPLETE
- [x] Card grid with lazy loading
- [x] Horizontal filter chips (scroll on mobile)
- [x] Sticky filters on scroll
- [x] Infinite scroll (mobile only)

### 2.2 Single Record View ✅ COMPLETE
- [x] Full-width images (60vh mobile)
- [x] Reader typography (16px mobile)
- [x] Sticky "Submit" footer CTA
- [x] Share actions (native share + clipboard)

### 2.3 Reading Progress
- [ ] Track records read
- [ ] Visual "read" indicator on cards

---

## Phase 3: Submission Flow ✅ COMPLETE

### 3.1 Submit Entry Point
- [x] `/submit/` hub page with submission options
- [x] Options: Article/Content, ISO posting

### 3.2 ISO Submission
- [x] Mobile-optimized form (exists at /post-iso/)
- [ ] Auto-save drafts (future enhancement)

### 3.3 CTA Placement
- [x] Article footers (sticky mobile CTA)
- [x] Homepage hero ("open call" CTA)
- [ ] Profile prompts (future enhancement)

---

## Phase 4: Factory Booking ✅ COMPLETE

### 4.1 Factory Page Mobile
- [x] Touch-friendly calendar (Flatpickr with UNMASK theme)
- [x] Clear available slots (calendar shows availability)
- [x] One-tap booking (sticky mobile footer CTA)
- [x] Bottom nav clearance added

### 4.2 My Bookings Dashboard
- [x] /my-bookings/ page exists with booking cards
- [ ] Show next booking in Dossier (future enhancement)

### 4.3 ISO ↔ Factory Integration
- [x] Factory preference on ISO (exists in form)
- [ ] "Book together" link (future enhancement)

---

## Phase 5: Dossier Polish

### 5.1 Notebook Editor
- [x] Visibility circles CSS fix
- [x] Mobile full-width
- [x] Toast notifications
- [x] Mobile touch targets (40x40px min)

### 5.2 Designation Display
- [x] V-XXX/D-XXX everywhere (ISO cards, visitor grid, dossier header)
- [x] Color matches visibility level (green=connected, yellow=logged-in, red=visitor)

### 5.3 Profile Preview ✅ COMPLETE
- [x] "Preview as visitor" button
- [x] Preview bar with visibility level options (visitor/logged-in/interlinked)
- [x] CSS-based content visibility simulation
- [x] Escape key to exit preview mode

---

## Key Decisions

### ISO Response Flow
**Keep BuddyBoss Messages** — already functional, supports threaded conversations.

### Profile Completion
**No mandatory completion** — only scene_name, email, password required at signup. All other fields optional and default to hidden.

### Designation Coloring
Match visibility system:
- Green border = interlinked visibility
- Yellow border = user visibility
- Red border = visitor/public visibility
- Gray = hidden

---

## Files Reference

| Component | File |
|-----------|------|
| Bottom Nav | `template-parts/global/bottom-nav.php` |
| Bottom Nav CSS | `assets/css/components/bottom-nav.css` |
| Bottom Nav Enqueue | `inc/enqueue-bottom-nav.php` |
| Collapsing Header | `inc/collapsing-header.php` |
| Sidebar Submit CTA | `inc/sidebar-submit-cta.php` |
| Notebook Editor | `template-parts/dossier/notebook.php` |
| Notebook CSS | `assets/css/pages/dossier-notebook.css` |
| Notebook JS | `assets/js/dossier-notebook.js` |
| Submit Hub | `page-templates/template-submit.php` |
| Submit Hub CSS | `assets/css/pages/submit.css` |
| Submit Hub Enqueue | `inc/enqueue-submit.php` |
| Dossier CSS | `assets/css/pages/dossier.css` |
| Dossier JS | `assets/js/dossier.js` |
| Dossier Header | `template-parts/dossier/header.php` |

---

*Last updated: January 6, 2026 — Phase 5 Complete*
