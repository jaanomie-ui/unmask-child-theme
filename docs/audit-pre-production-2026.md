# UNMASK Pre-Production UX/UI Audit Report

**Date**: January 8, 2026
**Staging URL**: staging4.houseofanomie.com
**Audit Type**: Full (code review + live site verification)

---

## Executive Summary

| Category | Count |
|----------|-------|
| **Blockers** | 8 |
| **Polish Items** | 22 |
| **Nice-to-Haves** | 9 |
| **Total Issues** | 39 |

### Critical Path to Launch
1. Fix Pink Panthers forms (3 broken)
2. Fix CSS token violations in login.css
3. Add missing form labels for accessibility
4. Resolve touch scroll conflicts on mobile homepage
5. Add alt text to profile/group card images

---

## BLOCKERS (Must Fix Before Launch)

### B-001: Pink Panthers Performer Form - No Action/Method/Nonce
- **Page**: Pink Panthers (`/pink-panthers/`)
- **File**: `/page-templates/page-pink-panthers.php:76-98`
- **Description**: Form tag has no `action`, `method`, or nonce field. Submit button does nothing.
- **Impact**: Users cannot submit performer acts
- **Fix**: Add `method="post"`, `action="<?php echo admin_url('admin-post.php'); ?>"`, nonce field, and hidden action field

### B-002: Pink Panthers Volunteer Form - No Handler
- **Page**: Pink Panthers (`/pink-panthers/`)
- **File**: `/page-templates/page-pink-panthers.php:128-143`
- **Description**: Form exists but no backend handler processes submissions
- **Impact**: Volunteer signups are lost
- **Fix**: Create handler function in functions.php, wire form to admin_post action

### B-003: Pink Panthers Concept-Call Form - Incorrect Wiring
- **Page**: Pink Panthers (concept-call template part)
- **File**: `/template-parts/pink-panthers/concept-call.php:42-74`
- **Description**: Has nonce but empty action attribute. Handler exists but form doesn't call it.
- **Impact**: Form doesn't submit despite appearing functional
- **Fix**: Add `action="<?php echo esc_url(admin_url('admin-post.php')); ?>"` and hidden action input

### B-004: Submit Page - Missing Form Labels (WCAG Violation)
- **Page**: Submit (`/submit/`)
- **File**: `/page-templates/template-submit.php`
- **Description**: Form fields use placeholder text only, no `<label>` elements with `for` attributes
- **Impact**: Screen reader users cannot navigate form; violates WCAG 2.1 Level A
- **Fix**: Add proper `<label>` elements for all form fields

### B-005: ISO Board - Missing Alt Text on Profile Cards
- **Page**: ISO Board, Homepage, Archive
- **File**: Multiple template parts
- **Description**: Profile/group cards render with empty alt attributes: `<img src="" alt="">`
- **Impact**: Images invisible to screen reader users; WCAG violation
- **Fix**: Add descriptive alt text or aria-labels to avatar images

### B-006: Homepage Touch Scroll Conflicts
- **Page**: Homepage (`/`)
- **File**: `/assets/css/unmask-homepage.css`, BuddyBoss theme CSS
- **Description**: Multiple CSS rules fight over `touch-action` property. Homepage rails swipe may not work on iOS.
- **Impact**: Mobile users may not be able to horizontally scroll content rails
- **Fix**: Consolidate touch-action rules; test on iOS Safari
- **Reference**: See `/docs/homepage-carousel-swipe-fix.md` for debugging notes

### B-007: Members Directory Not Loading Data
- **Page**: Members Directory (`/members/`)
- **Description**: Page shows skeleton loaders and "Please add fields to search members" but no actual member data renders. Directory appears broken.
- **Impact**: Users cannot browse or discover other members
- **Fix**: Verify BuddyBoss member directory configuration; check if xProfile fields are properly configured for search

### B-008: Welcome Page Missing Core Content
- **Page**: Welcome (`/welcome/`)
- **Description**: Page lacks visible onboarding content. No V-XXX designation display, no function assignment confirmation, no clear navigation cards (Archive/Dossier/Factory) visible.
- **Impact**: New users have no guidance after registration
- **Fix**: Verify template loads correctly for logged-in users; ensure onboarding cards render

---

## POLISH ITEMS (Should Fix Before Launch)

### P-001: CSS Token Violations in login.css
- **Page**: Login/Registration
- **File**: `/assets/css/pages/login.css`
- **Description**: ~60+ hardcoded hex colors instead of design tokens. Examples: `#181818`, `#c2c2c2`, `#2e2e2e`
- **Impact**: Style drift from design system; harder to maintain
- **Fix**: Replace with `var(--primitive-*)` or semantic tokens

### P-002: CSS Token Violations in dashboard.css
- **File**: `/assets/css/07-dashboard.css:175, 441`
- **Description**: Raw `color: #fff` and `color: #000000 !important`
- **Fix**: Replace with `var(--text-primary)`, `var(--text-inverse)`

### P-003: CSS Specificity War on Profile Accordions
- **Page**: Profile/Dossier
- **Files**: `/assets/css/03-buddyboss.css`, `/assets/css/pages/profile.css`
- **Description**: Conflicting `!important` rules hide/show accordions. JS band-aid runs 5 times with timeouts.
- **Impact**: Potential flicker; hard to maintain
- **Fix**: Remove `!important` rules, use proper CSS layer cascade

### P-004: Duplicate Template Files
- **Files**: `/buddypress/members/single/member-header.php`, `/buddypress/members/single/cover-image-header.php`
- **Description**: These files are identical
- **Impact**: Maintenance burden; confusion about which to edit
- **Fix**: Delete one, update any includes

### P-005: Missing Helper Function Name Mismatch
- **File**: Profile templates
- **Description**: Templates call `unmask_get_user_designation()` but function is `unmask_get_designation()`
- **Impact**: Potential PHP errors or silent failures
- **Fix**: Align function names or create alias

### P-006: Pink Panthers Notify Button - No Functionality
- **Page**: Pink Panthers
- **File**: `/page-templates/page-pink-panthers.php:113`
- **Description**: Button has no click handler or form submission
- **Impact**: Users expect action but nothing happens
- **Fix**: Wire to newsletter signup or remove

### P-007: ISO Board Empty State Lacks Guidance
- **Page**: ISO Board
- **Description**: "No listings match your filters" with no next steps
- **Impact**: Dead end for users
- **Fix**: Add "Try resetting filters" link or suggest browsing all

### P-008: Submit Page Toggle UI Unclear
- **Page**: Submit (`/submit/`)
- **Description**: Toggle between "create" and "submit" lacks active state styling and explanation
- **Impact**: Users may not understand the difference
- **Fix**: Add clear active state; add explanatory text below each option

### P-009: Factory Page Price Information Scattered
- **Page**: Factory (`/the-factory/`)
- **Description**: "$75/hour" appears multiple times in different locations
- **Impact**: Confusing hierarchy; users may miss key info
- **Fix**: Consolidate pricing in one prominent location

### P-010: Factory Booking Form Missing Labels
- **Page**: Factory
- **Description**: Form fields show placeholders but lack visible labels
- **Impact**: Accessibility concerns
- **Fix**: Add `<label>` elements or aria-labels

### P-011: Homepage Secondary Text Contrast
- **Page**: Homepage
- **Description**: Text color `#878787` on `#181818` background = ~3:1 ratio
- **Impact**: May fail WCAG AA for normal text (needs 4.5:1)
- **Fix**: Lighten to at least `#a0a0a0` for 4.5:1 contrast

### P-012: Archive Pagination/Navigation Unclear
- **Page**: Magazine Archive (`/the-archive/`)
- **Description**: "Shuffle" button is primary navigation but paradigm is non-obvious
- **Impact**: Users may not know how to see more content
- **Fix**: Add explicit pagination or "load more" alongside shuffle

### P-013: ISO Board Filter Affordance Low
- **Page**: ISO Board
- **Description**: Filter buttons don't clearly signal interactivity
- **Impact**: Users may not realize they can filter
- **Fix**: Add hover states, active indicators, or underlines

### P-014: Homepage Mixed Spacing Units
- **Page**: Homepage
- **Description**: CSS mixes `0.5em`, `16px`, design token gaps
- **Impact**: Inconsistent spacing
- **Fix**: Standardize on design tokens (`var(--gap-*)`)

### P-015: Homepage Z-Index Stack Chaos
- **Page**: Homepage, global
- **Description**: Z-index values scattered (1, 100, 100000) with no system
- **Impact**: Layering bugs, hard to debug
- **Fix**: Define z-index scale in design system

### P-016: Profile Kink Section Lock Message
- **Page**: Dossier (viewing others)
- **Description**: When kink section is locked for Visitors, upgrade prompt could be clearer
- **Impact**: Missed conversion opportunity
- **Fix**: Improve CTA copy and styling

### P-017: Mobile Header Collapse to 44px
- **Page**: Global (mobile)
- **Description**: Header shrinks to 44px on scroll; logo goes to 28px max-height
- **Impact**: Logo may become illegible; brand recognition affected
- **Fix**: Test at minimum size; consider 48px minimum

### P-018: Registration Form JS-Dependent
- **Page**: Registration (`/register/`)
- **Description**: Form appears to load via JavaScript; may fail if JS blocked
- **Impact**: Registration impossible if JS fails
- **Fix**: Ensure graceful degradation or server-render form

### P-019: Single Post Missing Author Byline
- **Page**: Single Record (e.g., `/2025/11/magnus-the-story-of-two-becoming-one/`)
- **Description**: Article metadata sparse—no visible author attribution in main content area
- **Impact**: Attribution unclear; reduces credibility
- **Fix**: Add author byline near title or in article header

### P-020: Homepage Carousel JS Band-Aid
- **File**: `/assets/js/homepage-rails.js`
- **Description**: Script clones carousel elements to remove event listeners, runs 5 times with timeouts (500ms, 1500ms, 3000ms) to fight other scripts
- **Impact**: Fragile solution; race condition with other JS
- **Fix**: Identify root cause (likely BuddyBoss JS) and fix properly rather than band-aid

### P-021: Dossier JS Uses Alerts for Errors
- **File**: `/assets/js/dossier.js`
- **Description**: Uses `alert()` for error messages instead of toast notifications
- **Impact**: Poor UX; jarring for users
- **Fix**: Replace alerts with toast notification component

### P-022: Debug Console Logs in Production
- **File**: `/assets/js/archive-magazine.js`
- **Description**: Multiple `console.log()` statements for debugging left in code
- **Impact**: Clutters browser console; unprofessional
- **Fix**: Remove or wrap in debug flag

---

## NICE-TO-HAVES (Post-Launch Enhancements)

### N-001: My ISOs Dashboard
- **Description**: Users can create ISOs but cannot edit, delete, or view their own listings
- **Impact**: No way to manage own content
- **Recommendation**: Create dashboard showing user's active/expired ISOs with edit/delete

### N-002: ISO Expiration Notifications
- **Description**: No email when ISO is about to expire
- **Recommendation**: Send reminder 3 days before expiration

### N-003: Profile Function Editor
- **Description**: Creative functions set at registration cannot be updated later
- **Recommendation**: Add function editor to profile settings

### N-004: Factory Booking History
- **Description**: Users cannot view past bookings
- **Recommendation**: Add booking history page or profile section

### N-005: Pink Panthers Submission Status
- **Description**: Performers can't see if their act was accepted
- **Recommendation**: Add status display or email notification

### N-006: Inline Login on ISO Board
- **Description**: Logged-out users redirect to `/wp-login.php` instead of modal
- **Recommendation**: Implement inline login modal to reduce friction

### N-007: Skip Links for Accessibility
- **Description**: No skip-to-content links for keyboard navigation
- **Recommendation**: Add skip links to header for screen reader users

### N-008: Loading States
- **Description**: Forms and AJAX actions lack visible loading indicators
- **Recommendation**: Add spinners or skeleton screens during async operations

### N-009: ISO Board Reset Filters Button
- **Description**: No visible "reset all filters" button when filters are active
- **Recommendation**: Add clear reset option to improve filter UX

---

## CSS Token Audit Results

### Files with Token Violations (non-design-system hex values)

| File | Violations | Priority |
|------|------------|----------|
| `pages/login.css` | ~60+ | High |
| `07-dashboard.css` | 2 | Medium |
| `06-pages.css` | 3 | Medium |
| `02-components.css` | ~20 | Low (some are local tokens) |
| `pages/profile.css` | ~30 | Medium (most use fallback pattern) |
| `components/edit-split.css` | ~40 | Medium (most use fallback pattern) |
| `unmask-components.css` | ~80 | Low (fallback pattern acceptable) |
| `unmask-single-record.css` | 2 | Low |
| `pages/dossier-notebook.css` | 5 | Low |

**Note**: Many files use `var(--token, #fallback)` pattern which is acceptable. Focus on files with raw hex values.

---

## Known Issues from Existing Documentation

The following were already documented in `/docs/`:

| Issue | Document | Status |
|-------|----------|--------|
| Pink Panthers forms broken | `audit-forms.md` | Included as B-001, B-002, B-003 |
| CSS specificity war | `audit-profile.md` | Included as P-003 |
| Duplicate template files | `audit-profile.md` | Included as P-004 |
| Missing user features | `audit-user-features.md` | Included as N-001 through N-005 |
| Touch scroll fix needed | `homepage-carousel-swipe-fix.md` | Included as B-006 |

---

## Page-by-Page Summary

### Homepage (`/`)
- **Status**: Mostly functional
- **Blockers**: 1 (touch scroll)
- **Polish**: 4 (contrast, spacing, z-index, mixed units)

### Magazine Archive (`/the-archive/`)
- **Status**: Functional
- **Blockers**: 0
- **Polish**: 1 (pagination clarity)

### ISO Board (`/iso-board/`)
- **Status**: Functional with UX gaps
- **Blockers**: 1 (alt text)
- **Polish**: 2 (empty state, filter affordance)

### Submit (`/submit/`)
- **Status**: Needs accessibility fixes
- **Blockers**: 1 (missing labels)
- **Polish**: 1 (toggle UI)

### Factory (`/the-factory/`)
- **Status**: Functional with minor issues
- **Blockers**: 0
- **Polish**: 2 (price scatter, form labels)

### Registration (`/register/`)
- **Status**: Needs verification
- **Blockers**: 0
- **Polish**: 1 (JS dependency)
- **Note**: Form may be MemberPress-rendered; verify functionality manually

### Pink Panthers (`/pink-panthers/`)
- **Status**: Broken forms
- **Blockers**: 3 (all forms)
- **Polish**: 1 (notify button)

### Profile/Dossier (`/members/*/`)
- **Status**: Functional with CSS issues
- **Blockers**: 0
- **Polish**: 4 (CSS war, duplicate files, function mismatch, lock message)

### Members Directory (`/members/`)
- **Status**: BROKEN
- **Blockers**: 1 (not loading member data)
- **Polish**: 0
- **Note**: Shows skeleton loaders but no members render; BuddyBoss config issue

### Welcome (`/welcome/`)
- **Status**: BROKEN for new users
- **Blockers**: 1 (missing onboarding content)
- **Polish**: 0
- **Note**: Critical for new user experience; needs content verification

### Single Post/Record (`/yyyy/mm/title/`)
- **Status**: Functional
- **Blockers**: 0
- **Polish**: 1 (missing author byline)

---

## Manual Testing Checklist

The following items require interactive browser testing and cannot be verified through code review alone:

### Performance (Lighthouse)
- [ ] Homepage LCP < 2.5s
- [ ] Homepage CLS < 0.1
- [ ] Archive page performance
- [ ] ISO Board with 30+ listings

### Browser Compatibility
- [ ] Chrome (latest) - all pages
- [ ] Safari macOS (latest) - all pages
- [ ] Safari iOS - homepage carousels, touch scrolling
- [ ] Firefox (latest) - all pages
- [ ] Edge (latest) - spot check

### Mobile Breakpoints
Test at: 320px, 375px, 414px, 768px
- [ ] Homepage hero and rails
- [ ] Bottom navigation behavior
- [ ] ISO Board cards and modal
- [ ] Profile/Dossier layout
- [ ] Form layouts

### Keyboard Navigation
- [ ] Tab through homepage (logical order)
- [ ] Tab through ISO Board filters
- [ ] Tab through forms (Submit, Factory)
- [ ] Modal focus trapping (ISO detail)
- [ ] Escape key closes modals

### Form Submissions (Logged In)
- [ ] ISO Board: Create new ISO listing
- [ ] Submit page: Submit content form
- [ ] Factory: Booking request form
- [ ] Profile: Edit profile fields

### Form Submissions (Logged Out)
- [ ] Registration: Complete signup flow
- [ ] Login: Successful authentication
- [ ] Password reset: Email delivery

### User Flows
- [ ] New user: Register → Welcome → Profile setup
- [ ] Returning user: Login → Dashboard
- [ ] ISO flow: Browse → Filter → View detail → Contact
- [ ] Content submission: Submit page → Form → Confirmation

### Accessibility (Manual)
- [ ] Screen reader: Homepage navigation
- [ ] Screen reader: ISO Board card content
- [ ] High contrast mode: All pages readable
- [ ] Zoom 200%: No content clipping

---

## Recommended Fix Order

### Week 1: Critical Fixes
1. Fix Pink Panthers forms (B-001, B-002, B-003)
2. Add form labels to Submit page (B-004)
3. Add alt text to profile cards (B-005)
4. Fix homepage touch scroll (B-006)
5. Fix Members Directory data loading (B-007)
6. Restore Welcome page onboarding content (B-008)

### Week 2: Polish
1. Migrate login.css to tokens (P-001)
2. Fix CSS specificity war (P-003)
3. Delete duplicate template (P-004)
4. Improve contrast on homepage (P-011)
5. Add pagination to archive (P-012)

### Week 3: Remaining Polish
1. Fix remaining token violations
2. Improve ISO Board empty states
3. Clarify Submit toggle UI
4. Add loading states where needed

### Post-Launch
- Implement My ISOs dashboard
- Add booking history
- Consider inline login modal

---

## Files Audited

### Templates
- `/page-templates/template-homepage.php`
- `/page-templates/template-archive-magazine.php`
- `/page-templates/template-iso-board.php`
- `/page-templates/template-submit.php`
- `/page-templates/page-pink-panthers.php`
- `/page-templates/page-register-visitor.php`
- `/page-the-factory.php`
- `/buddypress/members/single/member-header.php`
- `/buddypress/members/single/profile/profile-loop.php`

### CSS (Token Compliance)
- `/assets/css/00-design-system.css` (source of truth)
- `/assets/css/01-base.css`
- `/assets/css/02-components.css`
- `/assets/css/03-buddyboss.css`
- `/assets/css/06-pages.css`
- `/assets/css/07-dashboard.css`
- `/assets/css/pages/*.css` (all page-specific)
- `/assets/css/components/*.css`
- `/assets/css/unmask-*.css`

### JavaScript
- `/assets/js/homepage-rails.js` - Carousel touch fix
- `/assets/js/dossier.js` - Dossier interactions
- `/assets/js/archive-magazine.js` - Archive shuffle/filter
- `/assets/js/iso-submit.js` - ISO form submission

### PHP Handlers
- `/inc/enqueue-iso-board.php` - ISO AJAX handlers
- `/inc/dossier/functions.php` - Dossier AJAX handlers

### Documentation Reviewed
- `/docs/audit-forms.md`
- `/docs/audit-profile.md`
- `/docs/audit-user-features.md`
- `/docs/homepage-carousel-swipe-fix.md`

---

## Next Steps

1. **Triage**: Review this report with stakeholders
2. **Prioritize**: Confirm blocker list matches business priorities
3. **Assign**: Distribute fixes across team/sessions
4. **Retest**: Verify fixes on staging before production push
5. **Document**: Update `/docs/` with any new patterns discovered

---

*Report generated by Claude Code audit session - January 8-9, 2026*
