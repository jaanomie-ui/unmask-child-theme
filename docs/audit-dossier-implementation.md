# Dossier v3 Implementation Analysis

Last updated: 2025-01-05

## Overview

Analysis of the Dossier v3 implementation brief (`unmask-dossier-v3-terminal.html`) against current site state and existing audits.

---

## What the Mockup Defines

The HTML mockup is a **complete profile redesign** with:
- Terminal aesthetic (Berkeley Mono, dark theme, green accents)
- Consolidated header with designation, functions, status flags
- Active ISOs section with edit/expire actions
- Creative Practice section (disciplines, tools, influences)
- Kink Profile section (with Drone-only lock)
- Magazine Credits section (with claim feature)
- MY UNMASK dashboard (own profile only, with visibility toggle)

---

## Cross-Reference: Brief vs Current State

### Already Exists (Partial Implementation)

| Feature | Current State | Brief Requires |
|---------|---------------|----------------|
| Profile header | `member-header.php` override exists | Complete rewrite with new structure |
| Kink lock logic | `profile-loop.php` has it | Keep logic, new template |
| ISO display | ISO Board page only | **NEW**: Show user's ISOs on profile |
| xProfile fields | Exist but scattered | Reorganize into groups |
| Designation | `unmask_get_designation()` exists | Already done |
| Member type check | `bp_get_member_type()` | Already available |

### Must Build (New Features)

| Feature | Referenced In | Complexity |
|---------|---------------|------------|
| **ISO edit/expire from profile** | audit-forms.md | Medium |
| **Magazine Credits system** | Not in audits | **High** |
| **Records Read tracking** | Not in audits | Medium |
| **MY UNMASK dashboard** | Not in audits | **High** |
| **Visibility toggle (mask/unmask)** | Not in audits | Low |
| **Factory booking display** | audit-user-features.md | **Unknown** |
| **Pink Panthers role display** | audit-user-features.md | **Unknown** |

### Conflicts to Resolve

| Current | Brief Requires | Action |
|---------|----------------|--------|
| `profile.css` (messy) | New `dossier.css` | Replace entirely |
| `03-buddyboss.css` profile rules | Consolidate to dossier.css | Migrate + delete |
| `profile-accordion.js` | No accordions in new design | Delete |
| `cover-image-header.php` | Brief says delete | Delete (duplicate) |
| ISO form creates post, no edit | Brief shows edit/expire | Build edit capability |

---

## Red Flags

### 1. Magazine Credits System is Non-Trivial

The brief assumes querying "Records where user is contributor." This requires:
- ACF Repeater field on every Record (post) with contributor → user → role mapping
- Retroactive data entry for existing Records
- "Claim credit" workflow with admin review queue

**Estimate:** Significant sub-project. Consider deferring.

### 2. Factory Booking Data Source Unknown

The brief references:
```php
function unmask_get_next_factory_booking( $user_id ) {}
// Query: Depends on Factory booking plugin structure
```

Current state:
- Uses `[factory_booking]` shortcode from external plugin
- Plugin identity unknown
- Data storage structure unknown
- May not have queryable API

**Risk:** May require plugin research or may not be feasible.

### 3. Pink Panthers Role Display — No Data Model

The mockup shows:
```
Pink Panthers: Jan 31 · performer · 1 ticket
```

But from audit-forms.md:
- Performer submission only sends email to admin (no database record)
- Ticket system unclear (WooCommerce? Custom?)
- No "role assignment" system exists
- No way to query "user X is performer for event Y"

**Risk:** Feature has no backend to query. Needs data model first.

### 4. MY UNMASK Dashboard Aggregates 8 Data Sources

| Metric | Data Source | Status |
|--------|-------------|--------|
| ISOs (count + responses) | `iso` CPT | Exists |
| Factory (next booking) | Unknown plugin | **Unknown** |
| Connections (count + pending) | BuddyBoss | Exists |
| Messages (unread) | BuddyBoss | Exists |
| Pink Panthers (next event + role) | None | **Missing** |
| Records Read (count) | User meta | **Must build** |
| ISO Responses (all time) | None | **Must build** |
| Credits (count) | ACF on Records | **Must build** |

**4 of 8 metrics require new infrastructure.**

### 5. xProfile Field Reorganization

Brief assumes specific xProfile groups:
- Base (Scene Name, Bio, Pronouns, City)
- Creative Practice (Function, Disciplines, Tools, Influences)
- Availability (Open to bookings, Seeking collaborators)
- Kink Profile (Position, Interests, Experience)

**Action:** Audit current xProfile fields in WP Admin > Users > Profile Fields.

---

## Implementation Phases

### Phase 1: Core Profile Rewrite
**Goal:** Visual redesign without new features

1. Delete `buddypress/members/single/cover-image-header.php`
2. Create `assets/css/pages/dossier.css` (migrate from profile.css)
3. Rewrite `buddypress/members/single/member-header.php`
4. Rewrite `buddypress/members/single/profile/profile-loop.php`
5. Create template parts in `template-parts/dossier/`:
   - `header.php`
   - `section-practice.php`
   - `section-kink.php`
6. Delete `assets/js/profile-accordion.js`
7. Remove profile rules from `assets/css/03-buddyboss.css`
8. Create `inc/dossier/functions.php` with helpers

### Phase 2: ISO on Profile
**Goal:** Show and manage user's ISOs from profile

1. Create `unmask_get_user_isos( $user_id )` query function
2. Create `template-parts/dossier/section-isos.php`
3. Add edit ISO capability (new page or modal)
4. Add expire AJAX handler
5. Add JS for expire confirmation

### Phase 3: MY UNMASK Dashboard
**Goal:** Personal dashboard on own profile

1. Create `template-parts/dossier/my-unmask.php`
2. Add `unmask_dashboard_visibility` user meta
3. Create visibility toggle AJAX handler
4. Wire existing data sources:
   - ISOs: `unmask_get_user_isos()`
   - Connections: `bp_get_total_friend_count()`
   - Messages: `bp_get_total_unread_message_count()`
5. Add placeholders for missing data (Factory, Pink Panthers)
6. Create `unmask_get_user_activity()` for activity stream

### Phase 4: Credits System (Deferred)
**Goal:** Track magazine contributions

1. Add ACF Repeater field to Records (posts):
   - `record_contributors` (repeater)
   - `contributor_user` (user select)
   - `contributor_role` (select: photographed, featured, wrote, assisted, styled)
   - `contributor_name` (text, optional override)
2. Backfill existing Records with contributor data
3. Create `unmask_get_user_credits( $user_id )` query
4. Create `template-parts/dossier/section-credits.php`
5. Create claim workflow:
   - AJAX handler for claim submission
   - Admin notification/review queue
   - Approval workflow

### Phase 5: Tracking Features (Deferred)
**Goal:** Additional engagement metrics

1. **Records Read tracking:**
   - Add `unmask_records_read` user meta (array of post IDs)
   - Add "Mark as read" button on single Record template
   - Create AJAX handler `unmask_mark_read`
   - Create `unmask_get_records_read_count()` query

2. **ISO Response tracking:**
   - Track when users respond to ISOs (new meta or table)
   - Count responses sent and received

3. **Factory booking integration:**
   - Research `[factory_booking]` plugin
   - Determine if booking data is queryable
   - Build integration if possible

4. **Pink Panthers integration:**
   - Define data model for event roles
   - Build performer/volunteer assignment system
   - Integrate with ticket purchases

---

## File Architecture (From Brief)

### New Files to Create

```
unmask-child-theme/
├── inc/
│   └── dossier/
│       ├── functions.php          ← Helper functions
│       ├── queries.php            ← Data queries
│       └── ajax-handlers.php      ← AJAX endpoints
├── template-parts/
│   └── dossier/
│       ├── header.php             ← Profile header
│       ├── section-isos.php       ← Active ISOs
│       ├── section-practice.php   ← Creative practice
│       ├── section-kink.php       ← Kink profile
│       ├── section-credits.php    ← Magazine credits
│       └── my-unmask.php          ← Dashboard
└── assets/
    ├── css/pages/dossier.css      ← All dossier styles
    └── js/dossier.js              ← Interactions
```

### Files to Delete

```
buddypress/members/single/cover-image-header.php   ← Duplicate
assets/js/profile-accordion.js                      ← Obsolete hack
```

### Files to Migrate/Clean

```
assets/css/pages/profile.css      ← Migrate to dossier.css, then delete
assets/css/03-buddyboss.css       ← Remove profile-specific rules
```

---

## Summary

| Category | Count |
|----------|-------|
| Files to delete | 2 |
| Files to rewrite | 2-3 |
| New template parts | 6 |
| New PHP functions | ~15 |
| New AJAX handlers | 3-5 |
| New user meta fields | 3 |
| **Features with no backend** | 3 |

### Recommendations

1. **Start with Phase 1-2** — Get visual redesign + ISOs working
2. **Defer Credits system** — Only if you have capacity to backfill data
3. **Research Factory plugin first** — Before committing to that metric
4. **Build Pink Panthers data model** — Before attempting integration
5. **Records Read is low priority** — Nice-to-have engagement metric

---

## Open Questions

- [ ] What plugin provides `[factory_booking]`?
- [ ] How are Pink Panthers tickets sold?
- [ ] How are performers/house assigned to events?
- [ ] Current xProfile field structure?
- [ ] How many Records need credits backfilled?
