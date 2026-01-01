# UNMASK WPCode Snippets Audit

**Generated:** 2024-12-31
**Status:** Phase 1 - Initial Audit
**Total Snippets:** 29

---

## Summary by Type

| Type | Count | Action Required |
|------|-------|-----------------|
| CSS  | 2     | Extract to partials |
| JS   | 2     | Review dependencies |
| PHP  | 25    | Extract inline CSS, document classes |

---

## CSS Snippets (2)

### 1. Factory Page Styles
- **ID:** 2945
- **Type:** CSS
- **Location:** WPCode CSS snippet
- **Classes Targeted:** Booking forms, factory-specific layouts
- **CSS Variables Used:** `--unmask-*` tokens
- **Action:** Extract to `06-pages.css` (Factory section)
- **Dependencies:** Design system tokens

### 2. Dashboard Styles v2
- **ID:** 2958
- **Type:** CSS
- **Location:** WPCode CSS snippet
- **Classes Targeted:** Dashboard layouts, widget styling
- **CSS Variables Used:** `--unmask-*` tokens
- **Action:** Extract to `07-dashboard.css`
- **Dependencies:** Design system tokens

---

## JavaScript Snippets (2)

### 3. Collapse Memberpress
- **Type:** JavaScript
- **Purpose:** Collapse/expand MemberPress course sidebar sections
- **CSS Dependencies:**
  - `.mpcs-sidebar-wrapper`
  - `.mpcs-section-title`
  - `.mpcs-lesson-title`
- **Existing CSS:** Lines 139-202 in style.css (MemberPress Course sidebar)
- **Action:** Ensure collapse states have CSS

### 4. UNMASK Dossier Accordion
- **Type:** JavaScript
- **Purpose:** Accordion behavior for profile dossier sections
- **HTML Classes Generated:** Adds toggle states to accordion elements
- **CSS Dependencies:**
  - `.unmask-dossier-*` classes
  - Accordion open/closed states
- **Existing CSS:** Lines 2895-3023 in style.css (Dossier header elements)
- **Action:** Verify accordion transition CSS exists

---

## PHP Snippets (25)

### 5. Hive Mistress AI
- **Type:** PHP
- **Purpose:** AI chat interface integration
- **HTML Classes Generated:**
  - `#hm-chat`
  - `.hm-header`
  - `.hm-title`
  - `.hm-subtitle`
  - `.hm-messages`
  - `.hm-msg`
  - `.hm-msg-label`
  - `.hm-msg-user`
  - `.hm-msg-assistant`
  - `.hm-msg-content`
  - `.hm-input-area`
  - `.hm-footer`
  - `.hm-empty`
  - `.hm-loading`
- **Inline CSS:** None detected (uses stylesheet)
- **Existing CSS:** Lines 498-666 in style.css (complete styling)
- **Action:** ✅ CSS complete - extract to `02-components.css`

### 6. Name Conversion
- **Type:** PHP
- **Purpose:** Display name formatting logic
- **HTML Classes Generated:** None (logic only)
- **Inline CSS:** None
- **Action:** No CSS needed

### 7. Homepage Button
- **Type:** PHP
- **Purpose:** Custom homepage button/CTA
- **HTML Classes Generated:** TBD from JSON
- **Inline CSS:** Likely present
- **Action:** Extract button styles to `02-components.css`

### 8. Header Update
- **Type:** PHP
- **Purpose:** Modify header output
- **HTML Classes Generated:** TBD from JSON
- **Inline CSS:** Possible
- **Existing CSS:** Lines 228-232 (site title hide)
- **Action:** Document header customizations

### 9-11. ISO Board Snippets (3)
- **Type:** PHP
- **Purpose:** ISO board functionality (posting, filtering, display)
- **HTML Classes Generated:**
  - `.iso-board-wrapper`
  - `.iso-filters`
  - `.iso-filter-*`
  - `.iso-card`
  - `.iso-card-header`
  - `.iso-card-meta`
  - `.iso-card-actions`
  - `.iso-factory-grid`
  - `.iso-expiration-group`
- **Inline CSS:** Possible inline styles in output
- **Existing CSS:** Lines 3036-3727 in style.css (extensive ISO board styling)
- **Action:** ✅ CSS exists - extract to `02-components.css` (ISO Board section)

### 12. Homepage Zone Widgets
- **Type:** PHP
- **Purpose:** Widget zones for homepage layout
- **HTML Classes Generated:** TBD from JSON
- **Inline CSS:** Likely present for positioning
- **Action:** Extract to `06-pages.css` (Homepage section)

### 13. UNMASK Settings Page
- **Type:** PHP
- **Purpose:** Admin settings page
- **HTML Classes Generated:** Admin-side classes
- **Inline CSS:** Admin CSS
- **Action:** Keep in snippet (admin-only)

### 14. UNMASK Dashboard
- **Type:** PHP
- **Purpose:** User dashboard widget/display
- **HTML Classes Generated:** Dashboard-specific classes
- **CSS Dependencies:** Dashboard layout styles
- **Existing CSS:** Related to lines 1825+ (dashboard tokens)
- **Action:** Extract to `07-dashboard.css`

### 15. Record Data
- **Type:** PHP
- **Purpose:** Display record metadata widget
- **HTML Classes Generated:**
  - `.unmask-record-widget`
  - `.widget_unmask_record_data`
- **Inline CSS:** Possible
- **Existing CSS:** Lines 3965-3986 in style.css
- **Action:** ✅ CSS exists - extract to `02-components.css`

### 16. UNMASK Gallery Split
- **Type:** PHP
- **Purpose:** Split gallery layout
- **HTML Classes Generated:** TBD from JSON
- **Inline CSS:** Likely layout CSS
- **Action:** Extract to `02-components.css` (Gallery section)

### 17. UNMASK Gallery Picker
- **Type:** PHP
- **Purpose:** Gallery selection interface
- **HTML Classes Generated:** TBD from JSON
- **Inline CSS:** Likely present
- **Action:** Extract to `02-components.css` (Gallery section)

### 18. Tags Display
- **Type:** PHP
- **Purpose:** Display tags/taxonomies
- **HTML Classes Generated:** Tag-related classes
- **Inline CSS:** Possible
- **Action:** Extract to `02-components.css`

### 19. Archive Features Bundle
- **Type:** PHP
- **Purpose:** Archive page enhancements
- **HTML Classes Generated:** Archive-specific classes
- **Inline CSS:** Likely present
- **Action:** Extract to `06-pages.css` (Archive section)

### 20. Archive Stats
- **Type:** PHP
- **Purpose:** Display archive statistics
- **HTML Classes Generated:** Stats display classes
- **Inline CSS:** Likely present
- **Action:** Extract to `02-components.css`

### 21. UNMASK Card Template
- **Type:** PHP
- **Purpose:** Card layout template
- **HTML Classes Generated:** Card-related classes
- **Inline CSS:** Card layout CSS
- **Action:** Extract to `02-components.css` (Cards section)

### 22. Record Type Badge
- **Type:** PHP
- **Purpose:** Display record type badges
- **HTML Classes Generated:**
  - `.unmask-badge`
  - `.unmask-badge--photo`
  - `.unmask-badge--photographer`
  - `.unmask-badge--bookings`
  - `.unmask-badge--seeking`
- **Inline CSS:** Possible
- **Existing CSS:** Lines 2958-2997 in style.css
- **Action:** ✅ CSS exists - extract to `02-components.css`

### 23. Full Bleed Card Styles
- **Type:** PHP
- **Purpose:** Full-width card layouts
- **HTML Classes Generated:** Full-bleed classes
- **Inline CSS:** Likely present (layout CSS)
- **Action:** Extract to `02-components.css`

### 24. Hidden Card Link
- **Type:** PHP
- **Purpose:** Clickable card overlay
- **HTML Classes Generated:** Link overlay classes
- **Inline CSS:** Possible
- **Action:** Extract to `02-components.css`

### 25. Membership Page Styles
- **Type:** PHP
- **Purpose:** MemberPress page styling
- **HTML Classes Generated:** Membership-specific classes
- **Inline CSS:** Likely present
- **Existing CSS:** Lines 139-202, 441-496 (MemberPress styles)
- **Action:** Extract to `04-memberpress.css`

### 26. Web CPTs
- **Type:** PHP
- **Purpose:** Custom Post Types for "The Web" feature
- **HTML Classes Generated:** CPT-related classes
- **Inline CSS:** None (logic only)
- **Action:** No CSS needed

### 27. Web ACF Fields
- **Type:** PHP
- **Purpose:** ACF field definitions for The Web
- **HTML Classes Generated:** None (admin config)
- **Inline CSS:** None
- **Action:** No CSS needed

### 28. Web REST API
- **Type:** PHP
- **Purpose:** REST endpoints for The Web
- **HTML Classes Generated:** None (API only)
- **Inline CSS:** None
- **Action:** No CSS needed

### 29. Web Map
- **Type:** PHP
- **Purpose:** Map display for The Web
- **HTML Classes Generated:** Map-related classes
- **Inline CSS:** Likely present (map sizing/positioning)
- **Action:** Extract to `02-components.css` (Map section)

---

## CSS Architecture Mapping

### Where Snippet CSS Should Go:

| Target File | Snippets |
|-------------|----------|
| `00-design-system.css` | (tokens from style.css lines 713-725, 1825-1837) |
| `01-base.css` | (global resets from style.css lines 15-47) |
| `02-components.css` | Hive Mistress, ISO Board, Record Data, Record Type Badge, Gallery, Tags, Cards, Map |
| `03-buddyboss.css` | (BuddyBoss overrides from style.css) |
| `04-memberpress.css` | Collapse Memberpress, Membership Page Styles |
| `05-plugins.css` | (BuddyForms, Flatpickr from style.css) |
| `06-pages.css` | Factory Page Styles, Homepage Zone Widgets, Archive Features |
| `07-dashboard.css` | Dashboard Styles v2, UNMASK Dashboard |
| `08-utilities.css` | (helper classes) |

---

## Inline CSS Extraction Priority

### High Priority (known inline CSS)
1. Factory Page Styles (CSS snippet)
2. Dashboard Styles v2 (CSS snippet)

### Medium Priority (likely inline CSS)
3. Homepage Zone Widgets
4. Archive Features Bundle
5. Full Bleed Card Styles
6. Gallery Split/Picker
7. Web Map

### Low Priority (minimal/no inline CSS)
8. Name Conversion (logic only)
9. Web CPTs/ACF/REST (logic only)
10. UNMASK Settings (admin only)

---

## Existing style.css CSS Blocks

| Lines | Content | Target Partial |
|-------|---------|----------------|
| 12-47 | Hard edge theme, border-radius reset | `01-base.css` |
| 49-138 | BuddyPress profile card styling | `03-buddyboss.css` |
| 139-232 | MemberPress course styling | `04-memberpress.css` |
| 234-300 | Groups directory/single pages | `03-buddyboss.css` |
| 301-393 | Nav menus, sidebar ads, BuddyForms | `03-buddyboss.css`, `05-plugins.css` |
| 394-440 | Page-specific overrides (page-896) | `06-pages.css` |
| 441-496 | MemberPress sidebar positioning | `04-memberpress.css` |
| 498-666 | Hive Mistress chat UI | `02-components.css` |
| 667-712 | Square avatar overrides | `03-buddyboss.css` |
| 713-1133 | Dossier v1 + tokens | `00-design-system.css`, `02-components.css` |
| 1134-1243 | Form widget styling | `05-plugins.css` |
| 1244-1405 | Flatpickr calendar theme | `05-plugins.css` |
| 1406-1824 | Booking card layouts | `06-pages.css` |
| 1825-2168 | Dossier v2 + tokens | `00-design-system.css`, `02-components.css` |
| 2169-2894 | Dossier v2.0 main styles | `02-components.css` |
| 2895-3035 | Dossier header custom elements | `02-components.css` |
| 3036-3727 | ISO Board complete styles | `02-components.css` |
| 3728-3963 | Global overrides, typography | `01-base.css` |
| 3964-4072 | Record widget, access terminal, where section | `02-components.css` |

---

## Next Steps

1. ✅ Initial audit complete
2. ✅ Extract design tokens to `00-design-system.css` (600 lines)
3. ✅ Split style.css into partials (9 files)
4. ⏳ Extract inline CSS from PHP snippets
5. ✅ Update functions.php to enqueue partials
6. ⏳ Cross-reference and reconcile

---

## Completion Status (2025-12-31)

### CSS Architecture Migration Complete

All CSS partials now use the consolidated design system tokens:
- Primitive tokens: `--primitive-void`, `--primitive-red`, `--primitive-gray-*`, etc.
- Semantic tokens: `--bg-page`, `--text-primary`, `--border-default`, etc.
- Typography: `--font-ui`, `--font-body`, `--type-*`, `--leading-*`, `--tracking-*`
- Spacing: `--gap-section`, `--gap-element`, `--gap-tight`, `--gap-micro`

### Files Updated
| File | Lines | Status |
|------|-------|--------|
| `00-design-system.css` | 600 | Source of truth |
| `01-base.css` | 212 | Updated |
| `02-components.css` | 474 | Updated |
| `03-buddyboss.css` | 488 | Updated |
| `04-memberpress.css` | 145 | Updated |
| `05-plugins.css` | 246 | Updated |
| `06-pages.css` | 226 | Updated |
| `07-dashboard.css` | 238 | Updated |
| `08-utilities.css` | 252 | Updated |
| `style.css` | 10 | Header only |

### Snippet Classes Fully Documented
- Hive Mistress AI: 14 classes
- ISO Board: 9 classes
- Record Type Badge: 5 classes
- UNMASK Dossier: 6 classes
- Collapse Memberpress: 3 classes

### Remaining TBD Items (Require JSON Export Review)
The following snippets still need class extraction from the WPCode JSON export:
- Homepage Button (#7)
- Header Update (#8)
- Homepage Zone Widgets (#12)
- UNMASK Gallery Split (#16)
- UNMASK Gallery Picker (#17)
- Tags Display (#18)
- Archive Features Bundle (#19)
- Archive Stats (#20)
- UNMASK Card Template (#21)
- Full Bleed Card Styles (#23)
- Hidden Card Link (#24)
- Web Map (#29)

---

## Notes

- UNMASK uses Berkeley Mono as primary UI font
- Color tokens: `--unmask-bg`, `--unmask-border`, `--unmask-fg`, `--unmask-danger`, etc.
- Hard-edge design: `border-radius: 0` everywhere
- Red accent color: `#C23838` (--unmask-danger)
- Border color: `#5F5F5F` (--unmask-border)
