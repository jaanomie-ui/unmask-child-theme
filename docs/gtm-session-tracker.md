# GTM Session Tracker — UNMASK

**Container ID:** GTM-5NJKFDSX
**Started:** 2026-01-13

---

## Session Log

### Session 1 — 2026-01-13

**Objective:** Install GTM and set up foundation tracking

**Status:** In Progress

#### Completed
- [x] Created GTM implementation guide (`docs/gtm-implementation-guide.md`)
- [x] Created error log (`docs/gtm-error-log.md`)
- [x] Created session tracker (`docs/gtm-session-tracker.md`)
- [x] Reviewed functions.php structure
- [x] Confirmed child theme uses hooks (no header.php to edit)
- [x] Added GTM head script to functions.php (line 17-28)
- [x] Added GTM noscript to functions.php (line 34-42)
- [x] Deployed to staging
- [x] Verified GTM code present in page source (2 instances confirmed)

#### In Progress
- [ ] User to verify in GTM Preview mode
- [ ] User to check GA4 Realtime reports

#### Pending
- [ ] Configure GA4 tag in GTM console
- [ ] Set up scroll tracking
- [ ] Set up CTA click tracking
- [ ] Set up custom events for flywheel metrics

#### Decisions Made
1. **Hook approach over file edit:** Since child theme doesn't have header.php, using `wp_head` (priority 1) and `wp_body_open` hooks
2. **Staging first:** All changes deployed to staging4.houseofanomie.com before production

#### Files Modified This Session
- `docs/gtm-implementation-guide.md` — Created
- `docs/gtm-error-log.md` — Created
- `docs/gtm-session-tracker.md` — Created
- `functions.php` — Modified (added GTM hooks at lines 8-42)

---

## Future Sessions

### Planned: Event Tracking Setup
- Configure GTM triggers for CTA clicks
- Set up scroll depth tracking
- Create custom events for flywheel stages
- Test all events in Preview mode

### Planned: A/B Testing Infrastructure
- Git branching strategy for homepage variants
- Version control workflow documentation
- Feature flag system (if needed)

---

## Quick Reference

| Item | Value |
|------|-------|
| GTM Container | GTM-5NJKFDSX |
| GA4 Property | G-27MFSBL8G6 |
| Staging URL | https://staging4.houseofanomie.com |
| Theme Path (local) | /Users/ja/unmask-child-theme/ |
| Theme Path (staging) | ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/ |
