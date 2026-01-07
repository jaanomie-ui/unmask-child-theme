# UNMASK Mobile UX/UI Audit

**Date:** January 6, 2026
**Status:** Draft - Pending Flywheel Review

---

## Platform Overview

- **Site:** unmask magazine - a queer documentation project
- **Location:** Chicago, Illinois
- **Core Purpose:** Magazine content + community submissions
- **Tech Stack:** BuddyBoss + MemberPress + WooCommerce

---

## Confirmed Features (No Groups/Forums)

- Magazine / Archive
- ISO Board (classifieds/connections)
- Events (Modern Events Calendar)
- Factory Booking (studio space)
- Member Profiles / Dossiers
- Shop

---

## Key Goals

1. **Create content for the magazine**
2. **Get people to submit content**
3. Build engaged community around documentation

---

## Navigation Audit

### Current State
- 11+ menu items in side panel
- No sticky bottom navigation
- Header: 60px height
- No clear content submission CTA
- Cluttered with unused items (forums, groups references)

### Recommended Bottom Nav (5 items)

```
┌────────┬────────┬────────┬────────┬────────┐
│  Home  │Magazine│ Submit │ Events │   Me   │
│   🏠   │   📖   │   ✏️   │   📅   │   👤   │
└────────┴────────┴────────┴────────┴────────┘
```

| Position | Item | Purpose |
|----------|------|---------|
| 1 | Home | Activity feed + ISO highlights |
| 2 | Magazine | Archive / content discovery |
| 3 | Submit | **Primary CTA** - content submission |
| 4 | Events | IRL community engagement |
| 5 | Me | Profile, dossier, messages |

### Sidebar (Secondary)
- ISO Board
- Factory Booking
- Shop
- Settings
- About/Help

---

## Content Submission Flow (Core Goal)

### Current Problem
- No obvious "submit" entry point
- Submission process likely buried
- No encouragement/prompts

### Recommended Flow

```
User Journey: Content Submission

1. DISCOVER
   └── See "Submit Your Story" CTA everywhere
       - Bottom nav center button
       - Magazine article footers
       - Profile completion prompts
       - Homepage hero

2. START
   └── Tap Submit → Bottom sheet with options:
       - Write an article
       - Share photos/gallery
       - Submit event
       - Post to ISO

3. CREATE
   └── Mobile-optimized editor
       - Auto-save drafts
       - Easy image upload
       - Progress indicator
       - Preview before submit

4. PUBLISH
   └── Confirmation + share prompt
       - "Share to your profile"
       - "Tell interlinked members"
       - Track submission status
```

---

## Profile Edit (Dossier Notebook)

### Current Issues
- Visibility circles CSS conflict with BuddyBoss
- No save feedback
- Section pattern may confuse new users
- Not full-width on mobile

### Fixes Applied
- [x] CSS specificity for circles
- [x] Full-width mobile layout
- [x] Debug logging added

### Still Needed
- [ ] Toast notifications on save
- [ ] Onboarding tooltip for first-time users
- [ ] "Preview as visitor" mode
- [ ] Collapse sections by default

---

## Magazine-First Engagement Strategy

### Discovery Loop
```
READ → ENGAGE → SUBMIT → GET FEATURED → READ MORE
```

1. **Read**: Surface best content on homepage
2. **Engage**: Comments, shares, "interlink" with authors
3. **Submit**: Clear CTAs after reading ("Tell your story too")
4. **Get Featured**: Notification when published
5. **Loop**: Featured content brings readers back

### Prompts Throughout Site
- Article footer: "Have a story? Submit yours →"
- Empty states: "Be the first to share..."
- Profile: "Add to your story collection"
- ISO Board: "Document your connections"

---

## Mobile Optimizations

### Touch & Gestures
- 44px minimum touch targets
- Swipe to archive/favorite
- Pull-to-refresh on feeds
- Bottom sheets for actions (not modals)

### Performance
- Lazy load images below fold
- Skeleton loaders
- Compress uploads automatically
- Cache magazine articles offline

### Layout Principles
- Full-width content (no wasted space)
- Thumb-zone friendly (important actions at bottom)
- Collapsible header on scroll
- Safe area respect for notch/home indicator

---

## Recommended Page Hierarchy

```
HOME
├── Activity Feed (interlinked + ISO highlights)
├── Featured Magazine Content
└── Upcoming Events

MAGAZINE (Discovery)
├── Latest
├── Featured
├── Categories/Tags
└── Search

SUBMIT (Primary Action)
├── Write Article
├── Photo Gallery
├── Event
└── ISO Post

EVENTS
├── Upcoming
├── My RSVPs
└── Past

ME
├── My Dossier (Edit)
├── My Submissions
├── Messages
├── Notifications
└── Settings
```

---

## Implementation Priority

### Phase 1: Navigation (This Week)
1. Build sticky bottom nav component
2. Clean up sidebar menu
3. Add "Submit" as prominent CTA

### Phase 2: Dossier Polish
1. Fix remaining CSS issues
2. Add save feedback (toast)
3. Mobile collapse behavior

### Phase 3: Submission Flow
1. Streamlined mobile editor
2. Draft auto-save
3. Submission prompts throughout site

### Phase 4: Engagement
1. "Your story" CTAs
2. Notification improvements
3. Profile completion gamification

---

## CSS Quick Reference

```css
/* Bottom nav */
.unmask-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 56px;
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  background: var(--bg-elevated);
  border-top: 1px solid var(--border-subtle);
  z-index: 1000;
  padding-bottom: env(safe-area-inset-bottom);
}

/* Body padding for nav */
body.has-bottom-nav {
  padding-bottom: calc(56px + env(safe-area-inset-bottom));
}

/* Collapsing header */
.site-header.is-scrolled {
  --bb-header-height: 44px;
  transition: all 0.2s ease;
}
```

---

## Questions for Flywheel Review

1. What's the primary content type for submissions? (articles, photos, both?)
2. Is there an editorial review process?
3. What membership level can submit?
4. How does ISO Board fit into the magazine flywheel?
5. What role does Factory booking play in content creation?

---

*Audit by Claude Code - January 2026*
*To be updated after flywheel review*
