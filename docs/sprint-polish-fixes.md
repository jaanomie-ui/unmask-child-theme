# UNMASK Polish Fixes Sprint Plan

**Created**: January 9, 2026
**Scope**: High & Medium Priority Polish Items (P-011, P-010, P-022, P-021, P-005, P-007, P-012, P-008)

---

## Sprint Overview

| Priority | Item | Effort | Status |
|----------|------|--------|--------|
| High | P-011 Homepage contrast | 15 min | ⬜ |
| High | P-022 Remove console.logs | 10 min | ⬜ |
| High | P-021 Replace alerts with toasts | 30 min | ⬜ |
| High | P-010 Factory form labels | 20 min | ⬜ |
| High | P-005 Function name mismatch | 15 min | ⬜ |
| Medium | P-007 ISO Board empty state | 20 min | ⬜ |
| Medium | P-012 Archive pagination | 30 min | ⬜ |
| Medium | P-008 Submit toggle clarity | 25 min | ⬜ |

**Total Estimated Time**: ~2.5 hours

---

## HIGH PRIORITY FIXES

### P-011: Homepage Secondary Text Contrast

**Problem**: Text color `#878787` on `#181818` background = ~3:1 contrast ratio. WCAG AA requires 4.5:1 for normal text.

**File**: `/assets/css/unmask-homepage.css`

**Solution**: Update secondary text color to `#a3a3a3` (achieves 4.6:1 ratio)

```css
/* BEFORE - Find instances of #878787 or similar low-contrast grays */
.homepage-card-meta,
.homepage-secondary-text {
    color: #878787;
}

/* AFTER - Use design token or higher contrast value */
.homepage-card-meta,
.homepage-secondary-text {
    color: var(--text-secondary, #a3a3a3);
}
```

**Verification**:
1. Use Chrome DevTools color picker to verify contrast ratio ≥ 4.5:1
2. Or use https://webaim.org/resources/contrastchecker/

**Files to check**:
- `/assets/css/unmask-homepage.css`
- `/assets/css/unmask-cards.css`
- `/assets/css/00-design-system.css` (verify `--text-secondary` token value)

---

### P-022: Debug Console Logs in Production

**Problem**: `console.log()` statements left in production JavaScript.

**File**: `/assets/js/archive-magazine.js`

**Solution**: Remove or wrap in debug flag.

```javascript
/* BEFORE */
console.log('Shuffling articles...');
console.log('Filter applied:', filterValue);

/* AFTER - Option A: Remove entirely */
// (delete the lines)

/* AFTER - Option B: Wrap in debug flag (if needed for future debugging) */
const DEBUG = false; // Set to true only in development

if (DEBUG) console.log('Shuffling articles...');
if (DEBUG) console.log('Filter applied:', filterValue);
```

**Files to audit for console.log**:
```bash
grep -rn "console.log" /Users/ja/unmask-child-theme/assets/js/
```

**Expected files with console.log**:
- `/assets/js/archive-magazine.js` - REMOVE
- `/assets/js/dossier.js` - CHECK
- `/assets/js/homepage-rails.js` - CHECK

---

### P-021: Dossier JS Uses Alerts for Errors

**Problem**: `alert()` is jarring and feels dated. Should use toast notifications.

**File**: `/assets/js/dossier.js`

**Solution**: Create a simple toast notification system and replace alerts.

**Step 1: Add toast CSS** to `/assets/css/components/toast.css`

```css
/* Toast Notification Component */
.unmask-toast {
    position: fixed;
    bottom: 80px; /* Above bottom nav */
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: var(--bg-elevated, #2a2a2a);
    color: var(--text-primary, #fff);
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 10000;
    opacity: 0;
    transition: transform 0.3s ease, opacity 0.3s ease;
    max-width: 90vw;
    text-align: center;
}

.unmask-toast.unmask-toast--visible {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

.unmask-toast--error {
    background: var(--color-error, #811e279c);
}

.unmask-toast--success {
    background: var(--color-success, #355e3fd3);
}
```

**Step 2: Add toast JS helper** to `/assets/js/unmask-toast.js`

```javascript
/**
 * UNMASK Toast Notification System
 * Usage: unmaskToast('Message here', 'error') or unmaskToast('Success!', 'success')
 */
(function() {
    'use strict';

    window.unmaskToast = function(message, type = 'default', duration = 4000) {
        // Remove existing toast if present
        const existing = document.querySelector('.unmask-toast');
        if (existing) existing.remove();

        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'unmask-toast';
        if (type === 'error') toast.classList.add('unmask-toast--error');
        if (type === 'success') toast.classList.add('unmask-toast--success');
        toast.textContent = message;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');

        document.body.appendChild(toast);

        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('unmask-toast--visible');
        });

        // Auto-dismiss
        setTimeout(() => {
            toast.classList.remove('unmask-toast--visible');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };
})();
```

**Step 3: Update dossier.js** - Replace alert() calls

```javascript
/* BEFORE */
alert('Error saving changes. Please try again.');

/* AFTER */
unmaskToast('Error saving changes. Please try again.', 'error');

/* BEFORE */
alert('Changes saved!');

/* AFTER */
unmaskToast('Changes saved!', 'success');
```

**Step 4: Enqueue toast assets** in `/inc/enqueue-dossier.php` or `functions.php`

```php
// Add to existing dossier enqueue or create new
wp_enqueue_style('unmask-toast', get_stylesheet_directory_uri() . '/assets/css/components/toast.css', array(), '1.0.0');
wp_enqueue_script('unmask-toast', get_stylesheet_directory_uri() . '/assets/js/unmask-toast.js', array(), '1.0.0', true);
```

---

### P-010: Factory Booking Form Missing Labels

**Problem**: Form fields use placeholders only, no `<label>` elements for accessibility.

**File**: `/page-the-factory.php` (or `/page-the-factory-book.php`)

**Solution**: Add visible or visually-hidden labels to all form fields.

```php
<!-- BEFORE -->
<input type="text" name="name" placeholder="Your name">
<input type="email" name="email" placeholder="Email address">
<input type="date" name="date" placeholder="Preferred date">
<textarea name="message" placeholder="Tell us about your booking"></textarea>

<!-- AFTER - Option A: Visible labels (recommended) -->
<div class="form-field">
    <label for="factory-name">Your name</label>
    <input type="text" id="factory-name" name="name" placeholder="e.g. Alex Smith">
</div>

<div class="form-field">
    <label for="factory-email">Email address</label>
    <input type="email" id="factory-email" name="email" placeholder="you@example.com">
</div>

<div class="form-field">
    <label for="factory-date">Preferred date</label>
    <input type="date" id="factory-date" name="date">
</div>

<div class="form-field">
    <label for="factory-message">Tell us about your booking</label>
    <textarea id="factory-message" name="message" placeholder="What are you planning?"></textarea>
</div>

<!-- AFTER - Option B: Visually hidden labels (if design requires no visible labels) -->
<label for="factory-name" class="screen-reader-text">Your name</label>
<input type="text" id="factory-name" name="name" placeholder="Your name">
```

**Add screen-reader-text class** to `/assets/css/01-base.css` if not present:

```css
.screen-reader-text {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
```

---

### P-005: Function Name Mismatch

**Problem**: Templates call `unmask_get_user_designation()` but function is named `unmask_get_designation()`.

**Files to check**:
- Profile templates in `/buddypress/members/single/`
- `/template-parts/dossier/`
- `/inc/dossier/functions.php`

**Solution**: Create alias function or rename consistently.

**Option A: Add alias function** (safest, backwards compatible)

Add to `/inc/dossier/functions.php` or `functions.php`:

```php
/**
 * Alias for unmask_get_designation() for backwards compatibility
 * Some templates use unmask_get_user_designation()
 */
if (!function_exists('unmask_get_user_designation')) {
    function unmask_get_user_designation($user_id = null) {
        return unmask_get_designation($user_id);
    }
}
```

**Option B: Find and replace** (cleaner, but more files to change)

```bash
# Find all files calling the wrong function name
grep -rn "unmask_get_user_designation" /Users/ja/unmask-child-theme/

# Then update each file to use unmask_get_designation()
```

**Verification**:
```bash
# After fix, this should return no results (or only the alias definition)
grep -rn "unmask_get_user_designation" /Users/ja/unmask-child-theme/ --include="*.php"
```

---

## MEDIUM PRIORITY FIXES

### P-007: ISO Board Empty State Lacks Guidance

**Problem**: "No listings match your filters" provides no next steps.

**File**: `/template-parts/components/iso-filters.php` or `/page-templates/template-iso-board.php`

**Solution**: Add helpful empty state with reset action.

```php
<!-- BEFORE -->
<div class="iso-empty-state">
    <p>No listings match your filters.</p>
</div>

<!-- AFTER -->
<div class="iso-empty-state">
    <div class="iso-empty-state__icon">
        <!-- Optional: Add an icon or illustration -->
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
        </svg>
    </div>
    <p class="iso-empty-state__title">No listings match your filters</p>
    <p class="iso-empty-state__hint">Try broadening your search or exploring all listings.</p>
    <div class="iso-empty-state__actions">
        <button type="button" class="iso-reset-filters btn btn--secondary" onclick="resetIsoFilters()">
            Reset all filters
        </button>
        <a href="/iso-board/" class="btn btn--text">Browse all ISOs</a>
    </div>
</div>
```

**Add CSS** to `/assets/css/pages/iso-board.css`:

```css
.iso-empty-state {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-secondary);
}

.iso-empty-state__icon {
    margin-bottom: 16px;
    opacity: 0.5;
}

.iso-empty-state__title {
    font-size: 18px;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.iso-empty-state__hint {
    font-size: 14px;
    margin-bottom: 24px;
}

.iso-empty-state__actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
```

**Add JS reset function** to `/assets/js/iso-board.js` (or inline):

```javascript
function resetIsoFilters() {
    // Reset all filter dropdowns/checkboxes to default
    document.querySelectorAll('.iso-filter-select').forEach(el => el.value = '');
    document.querySelectorAll('.iso-filter-checkbox').forEach(el => el.checked = false);

    // Trigger filter update (adjust based on existing filter logic)
    if (typeof filterIsoListings === 'function') {
        filterIsoListings();
    } else {
        // Fallback: reload page without filter params
        window.location.href = '/iso-board/';
    }
}
```

---

### P-012: Archive Pagination/Navigation Unclear

**Problem**: "Shuffle" button is non-obvious as primary navigation paradigm.

**File**: `/page-templates/template-archive-magazine.php` and `/assets/js/archive-magazine.js`

**Solution**: Add explicit "Load More" alongside shuffle, with explanatory text.

```php
<!-- BEFORE -->
<div class="archive-controls">
    <button class="archive-shuffle">Shuffle</button>
</div>

<!-- AFTER -->
<div class="archive-controls">
    <div class="archive-controls__info">
        <span class="archive-count">Showing <strong id="visible-count">12</strong> of <strong id="total-count"><?php echo $total_posts; ?></strong> records</span>
    </div>
    <div class="archive-controls__buttons">
        <button type="button" class="archive-shuffle btn btn--secondary" title="Randomize the order">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="16 3 21 3 21 8"></polyline>
                <line x1="4" y1="20" x2="21" y2="3"></line>
                <polyline points="21 16 21 21 16 21"></polyline>
                <line x1="15" y1="15" x2="21" y2="21"></line>
                <line x1="4" y1="4" x2="9" y2="9"></line>
            </svg>
            Shuffle
        </button>
        <button type="button" class="archive-load-more btn btn--primary" id="load-more-btn">
            Load more
        </button>
    </div>
</div>
```

**Add CSS** to `/assets/css/pages/archive-magazine.css`:

```css
.archive-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 16px 0;
    border-top: 1px solid var(--border-subtle);
    margin-top: 32px;
}

.archive-controls__info {
    font-size: 14px;
    color: var(--text-secondary);
}

.archive-controls__buttons {
    display: flex;
    gap: 12px;
}

.archive-shuffle svg {
    margin-right: 6px;
}

/* Hide load more when all items shown */
.archive-load-more[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}
```

**Update JS** in `/assets/js/archive-magazine.js`:

```javascript
// Add load more functionality
const loadMoreBtn = document.getElementById('load-more-btn');
const ITEMS_PER_PAGE = 12;
let currentlyShowing = ITEMS_PER_PAGE;

if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', function() {
        const hiddenItems = document.querySelectorAll('.archive-item.hidden');
        const toShow = Array.from(hiddenItems).slice(0, ITEMS_PER_PAGE);

        toShow.forEach(item => item.classList.remove('hidden'));
        currentlyShowing += toShow.length;

        // Update count
        document.getElementById('visible-count').textContent = currentlyShowing;

        // Disable button if no more items
        if (document.querySelectorAll('.archive-item.hidden').length === 0) {
            loadMoreBtn.disabled = true;
            loadMoreBtn.textContent = 'All loaded';
        }
    });
}
```

---

### P-008: Submit Page Toggle UI Unclear

**Problem**: Toggle between "create" and "submit" lacks active state styling and explanation.

**File**: `/page-templates/template-submit.php`

**Solution**: Add clear active states and explanatory text.

```php
<!-- BEFORE -->
<div class="submit-toggle">
    <button class="toggle-create">Create</button>
    <button class="toggle-submit">Submit</button>
</div>

<!-- AFTER -->
<div class="submit-mode-selector" role="tablist" aria-label="Submission type">
    <button
        role="tab"
        class="submit-mode-tab submit-mode-tab--create"
        id="tab-create"
        aria-selected="true"
        aria-controls="panel-create"
        data-mode="create"
    >
        Create
    </button>
    <button
        role="tab"
        class="submit-mode-tab submit-mode-tab--submit"
        id="tab-submit"
        aria-selected="false"
        aria-controls="panel-submit"
        data-mode="submit"
    >
        Submit
    </button>
</div>

<div class="submit-mode-description" aria-live="polite">
    <p id="mode-description-create" class="submit-mode-description__text">
        <strong>Create</strong> — Start a new project from scratch. We'll help shape your idea into something publishable.
    </p>
    <p id="mode-description-submit" class="submit-mode-description__text" hidden>
        <strong>Submit</strong> — You already have finished work ready for publication. Send it our way.
    </p>
</div>

<!-- Tab panels -->
<div role="tabpanel" id="panel-create" aria-labelledby="tab-create">
    <!-- Create form content -->
</div>
<div role="tabpanel" id="panel-submit" aria-labelledby="tab-submit" hidden>
    <!-- Submit form content -->
</div>
```

**Add CSS** to `/assets/css/pages/submit.css`:

```css
.submit-mode-selector {
    display: flex;
    gap: 0;
    background: var(--bg-subtle, #1a1a1a);
    border-radius: 8px;
    padding: 4px;
    margin-bottom: 16px;
    max-width: 300px;
}

.submit-mode-tab {
    flex: 1;
    padding: 12px 24px;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.submit-mode-tab:hover {
    color: var(--text-primary);
}

.submit-mode-tab[aria-selected="true"] {
    background: var(--bg-elevated, #2a2a2a);
    color: var(--text-primary);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.submit-mode-description {
    margin-bottom: 24px;
    padding: 16px;
    background: var(--bg-subtle, #1a1a1a);
    border-radius: 8px;
    border-left: 3px solid var(--color-accent, #ff4081);
}

.submit-mode-description__text {
    font-size: 14px;
    line-height: 1.5;
    color: var(--text-secondary);
    margin: 0;
}

.submit-mode-description__text strong {
    color: var(--text-primary);
}
```

**Add JS** for toggle behavior:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.submit-mode-tab');
    const descriptions = {
        create: document.getElementById('mode-description-create'),
        submit: document.getElementById('mode-description-submit')
    };
    const panels = {
        create: document.getElementById('panel-create'),
        submit: document.getElementById('panel-submit')
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const mode = this.dataset.mode;

            // Update tab states
            tabs.forEach(t => t.setAttribute('aria-selected', 'false'));
            this.setAttribute('aria-selected', 'true');

            // Update descriptions
            Object.values(descriptions).forEach(d => d.hidden = true);
            descriptions[mode].hidden = false;

            // Update panels
            Object.values(panels).forEach(p => p.hidden = true);
            panels[mode].hidden = false;
        });
    });
});
```

---

## Deployment Checklist

After implementing fixes:

```bash
# 1. Deploy all changed files
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/assets/ \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/assets/

# 2. Deploy PHP files
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/page-templates/ \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/page-templates/

SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/template-parts/ \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/template-parts/

SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/inc/ \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/inc/

# 3. Fix permissions
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "find ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/assets -type f -exec chmod 644 {} \;"
```

## Verification Tests

| Item | Test | Pass? |
|------|------|-------|
| P-011 | Chrome DevTools → Inspect homepage text → Contrast ratio ≥ 4.5:1 | ⬜ |
| P-022 | Open browser console on Archive page → No console.log output | ⬜ |
| P-021 | Trigger error in Dossier → Toast appears instead of alert | ⬜ |
| P-010 | Factory form → Tab through fields → Screen reader announces labels | ⬜ |
| P-005 | Profile page loads without PHP errors | ⬜ |
| P-007 | ISO Board → Apply impossible filter → Reset button appears | ⬜ |
| P-012 | Archive page → "Load more" button visible and functional | ⬜ |
| P-008 | Submit page → Toggle is clear, description updates | ⬜ |

---

*Sprint plan created January 9, 2026*
