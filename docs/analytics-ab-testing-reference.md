# UNMASK Analytics & A/B Testing Reference

**Date:** January 2026
**Version:** 2.0

This document covers the analytics infrastructure, A/B testing system, and visitor experience changes implemented to address user feedback that the site felt "overwhelming" and visitors were "confused about what Unmask is."

---

## Table of Contents

1. [Visitor Experience Changes](#visitor-experience-changes)
2. [A/B Testing Infrastructure](#ab-testing-infrastructure)
3. [Analytics v2 Script](#analytics-v2-script)
4. [Analytics Dashboard](#analytics-dashboard)
5. [REST API Endpoint](#rest-api-endpoint)
6. [File Reference](#file-reference)

---

## Visitor Experience Changes

### Problem
First-time visitors found the homepage overwhelming with too many features (ISOs, Activity, System Bar) without context about what UNMASK is.

### Solution
Simplified homepage for logged-out visitors using `is_user_logged_in()` conditionals.

### What Visitors See

| Element | Logged Out | Logged In |
|---------|------------|-----------|
| Wordlogo + Tagline | ✓ | ✗ |
| "Latest Record" Label | ✗ | ✓ |
| System Bar | ✗ | ✓ |
| Records Rail | ✓ | ✓ |
| ISO Rail | ✗ | ✓ |
| Activity Feed | ✗ | ✓ |
| Factory Rail | ✓ | ✓ |
| CTA Widgets | ✓ | ✓ |

### Files Modified

**`template-parts/homepage/hero-section.php`** (lines 93-100)
```php
<?php if (!is_user_logged_in()) : ?>
    <a href="<?php echo esc_url(home_url('/')); ?>" class="unmask-hero__wordlogo">
        <img src="<?php echo esc_url(home_url('/wp-content/uploads/2025/10/brown-unmask.webp')); ?>"
             alt="UNMASK" class="unmask-hero__wordlogo-img">
    </a>
    <div class="unmask-hero__tagline">documentation of people who refuse to fit into boxes</div>
<?php else : ?>
    <div class="unmask-hero__label"><?php echo esc_html($hero_label); ?></div>
<?php endif; ?>
```

**`template-parts/homepage/below-fold-grid.php`** (line 90)
```php
<?php if (is_user_logged_in()) : ?>
    <!-- ISO Rail -->
    <!-- Activity Widget -->
<?php endif; ?>
```

**`page-templates/template-homepage.php`** (line 28)
```php
<?php if (is_user_logged_in()) : ?>
    <?php get_template_part('template-parts/global/system-bar'); ?>
<?php endif; ?>
```

**`assets/css/unmask-homepage.css`**
```css
.unmask-hero__wordlogo-img {
    height: 24px;
    width: auto;
    /* Invert brown logo to light gray matching --text-primary */
    filter: brightness(0) invert(0.85);
    opacity: 0.9;
}

.unmask-hero__tagline {
    font-family: var(--font-mono);
    font-size: var(--type-xs);
    color: var(--text-muted);
    text-transform: lowercase;
}
```

---

## A/B Testing Infrastructure

### Overview
Cookie-based A/B testing system for running element-level experiments. Variants persist for 30 days and are automatically pushed to GA4 dataLayer.

### File: `inc/ab-testing.php`

### Configuration
Edit `unmask_get_experiments()` to add/modify tests:

```php
function unmask_get_experiments() {
    return [
        'homepage_v2' => [
            'variants' => ['A', 'B'],
            'weights'  => [50, 50],  // Traffic split
            'active'   => true,
        ],
        'cta_copy' => [
            'variants' => ['A', 'B'],
            'weights'  => [50, 50],
            'active'   => false,     // Paused
        ],
    ];
}
```

### PHP Functions

#### Get Current Variant
```php
$variant = unmask_get_variant('homepage_v2');
// Returns 'A', 'B', or null if test inactive
```

#### Check Specific Variant
```php
if (unmask_is_variant('homepage_v2', 'B')) {
    // Show variant B content
}
```

#### Get All Active Variants
```php
$all = unmask_get_all_variants();
// Returns ['homepage_v2' => 'B', 'cta_copy' => 'A']
```

### Template Usage Example
```php
<?php if (unmask_is_variant('cta_copy', 'A')) : ?>
    <a href="/submit/">tell your story</a>
<?php else : ?>
    <a href="/records/">browse the archive</a>
<?php endif; ?>
```

### DataLayer Integration
Variants are automatically pushed to `window.dataLayer` on page load:

```javascript
{
    event: 'ab_variants_loaded',
    ab_variants: {
        homepage_v2: 'B',
        cta_copy: 'A'
    }
}
```

### Testing/Preview
Force a specific variant by adding URL parameter:
```
https://staging4.houseofanomie.com/?ab_force=homepage_v2:B
```

### Cookie Details
- Name: `unmask_ab_{test_id}` (e.g., `unmask_ab_homepage_v2`)
- Duration: 30 days
- Secure: Yes (HTTPS only)
- HttpOnly: Yes

---

## Analytics v2 Script

### Overview
Lean analytics script (~240 lines) replacing the previous 640-line version. Focused on 6 priority metrics.

### File: `assets/js/unmask-analytics-v2.js`

### Priority Metrics

| Metric | Tracking Method |
|--------|-----------------|
| Unique Visitors | GA4 native `page_view` |
| Bounce Rate | GA4 automatic |
| Pages per Session | GA4 automatic |
| Registration Rate | Custom `registration_complete` event |
| New Registrations | Count of above |
| Profile Completion | Custom `profile_complete` event |

### Automatic Tracking

The script automatically tracks:

- **CTA Clicks**: All links matching configured selectors
- **Rail Scrolls**: First horizontal scroll on each rail
- **Card Clicks**: Record cards and factory cards
- **Scroll Depth**: 25%, 50%, 75%, 90% milestones
- **Time on Page**: 30s, 60s, 120s milestones

### CTA Selectors (Configurable)
```javascript
const CONFIG = {
    ctaSelectors: [
        '.unmask-hero__cta',
        '.widget-sq--submit a',
        '.widget-sq--panthers a',
        '.rail__link',
        '.factory-card',
        '[data-track-cta]'  // Custom attribute
    ]
};
```

### Global Tracking Functions

Call these from PHP or other JS when events occur:

#### Track Registration
```javascript
window.unmaskTrackRegistration(userId, source);
// Example: unmaskTrackRegistration(123, 'checkout');
```

#### Track Profile Completion
```javascript
window.unmaskTrackProfileComplete(userId, completionPercent);
// Example: unmaskTrackProfileComplete(123, 75);
```

#### Track Email Signup
```javascript
window.unmaskTrackEmailSignup(context);
// Example: unmaskTrackEmailSignup('homepage_rail');
```

### Event Schema

All events include:
```javascript
{
    event: 'event_name',
    ab_variants: { homepage_v2: 'B' },
    page_type: 'homepage',
    user_status: 'visitor'  // or 'member'
}
```

### Switching Between v1 and v2

Edit `inc/enqueue-analytics.php`:
```php
// Set to false to revert to original script
$use_v2 = true;
```

---

## Analytics Dashboard

### Overview
Terminal-style cockpit dashboard for viewing site metrics. Admin-only access. Data populated via n8n → REST API.

### File: `page-templates/template-dashboard-analytics.php`

### Setup
1. Create a new WordPress page
2. Assign template: "Analytics Dashboard"
3. Page is automatically restricted to administrators

### Layout

```
┌─────────────────────────────────────────────────────────┐
│  UNMASK // SYSTEM METRICS         [live] ● 2026.01.13  │
├─────────────────────────────────────────────────────────┤
│  DISCOVERY          │  ENTRY            │  ACTIVATION   │
│  ─────────────────  │  ──────────────── │  ──────────── │
│  visitors: 1,247    │  registrations: 23│  profiles: 67%│
│  bounce: 42.3%      │  rate: 1.8%       │  complete: 15 │
│  pages/sess: 2.4    │  ▲ +12% vs last   │  ▲ +8% vs last│
├─────────────────────────────────────────────────────────┤
│  A/B STATUS                                              │
│  ──────────────────────────────────────────────────────  │
│  homepage_v2: B winning (+23% bounce reduction)          │
├─────────────────────────────────────────────────────────┤
│  TREND // 7 DAY                                          │
│  ▁▂▃▄▅▆▇█▇▆▅▄▃▂▁ visitors                               │
│  ▁▁▂▂▃▃▄▄▅▅▆▆▇▇█ registrations                          │
└─────────────────────────────────────────────────────────┘
```

### Styling
- File: `assets/css/pages/dashboard-analytics.css`
- Uses design tokens from `00-design-system.css`
- Monospace font (`var(--font-mono)`)
- Green/amber/red status indicators
- ASCII trend bars

### Auto-Refresh
Page automatically refreshes every 5 minutes.

---

## REST API Endpoint

### Overview
REST API for n8n to push GA4 data to WordPress. Data stored in `wp_options` for dashboard consumption.

### File: `inc/analytics-data-endpoint.php`

### Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| POST | `/wp-json/unmask/v1/analytics` | Update analytics data |
| GET | `/wp-json/unmask/v1/analytics` | Retrieve current data |

### Authentication
Requires API key in header:
```
X-Analytics-Key: your-api-key-here
```

### Configure API Key
1. Go to **Settings → Analytics API** in WP Admin
2. Generate or enter API key
3. Save

### POST Payload Schema

```json
{
    "visitors": 1247,
    "bounce_rate": 42.3,
    "pages_per_session": 2.4,
    "registrations": 23,
    "registration_rate": 1.8,
    "registrations_change": 12,
    "profile_completions": 15,
    "profile_completion_rate": 67,
    "profile_change": 8,
    "trend_visitors": [100, 120, 150, 130, 180, 200, 220],
    "trend_registrations": [2, 3, 3, 4, 5, 5, 6],
    "ab_tests": [
        {
            "name": "homepage_v2",
            "status": "winning",
            "result": "B winning (+23% bounce reduction)"
        },
        {
            "name": "cta_copy",
            "status": "inconclusive",
            "result": "need 847 more sessions"
        }
    ]
}
```

### n8n Workflow Setup

1. **Trigger**: Schedule (daily at midnight)
2. **GA4 Node**: Pull metrics via GA4 Data API
3. **Transform**: Format data to match schema
4. **HTTP Request**: POST to `/wp-json/unmask/v1/analytics`
   - Header: `X-Analytics-Key: your-key`
   - Body: JSON payload

### WordPress Options Used

| Option Key | Description |
|------------|-------------|
| `unmask_analytics_data` | Stored metrics array |
| `unmask_analytics_updated` | Last sync timestamp |
| `unmask_analytics_api_key` | API authentication key |

---

## File Reference

### New Files Created

| File | Purpose |
|------|---------|
| `inc/ab-testing.php` | A/B testing infrastructure |
| `inc/analytics-data-endpoint.php` | REST API for n8n |
| `inc/enqueue-dashboard-analytics.php` | Dashboard CSS enqueue |
| `assets/js/unmask-analytics-v2.js` | Lean analytics script |
| `assets/css/pages/dashboard-analytics.css` | Dashboard styling |
| `page-templates/template-dashboard-analytics.php` | Dashboard template |

### Modified Files

| File | Changes |
|------|---------|
| `functions.php` | Added require_once for new inc files |
| `inc/enqueue-analytics.php` | Added v1/v2 toggle |
| `template-parts/homepage/hero-section.php` | Added wordlogo + tagline for visitors |
| `template-parts/homepage/below-fold-grid.php` | Wrapped ISO/Activity in login check |
| `page-templates/template-homepage.php` | Wrapped system bar in login check |
| `assets/css/unmask-homepage.css` | Added wordlogo/tagline styles |

---

## Success Metrics

### UX Improvements (Target)
- Bounce rate for first-time visitors: **↓ 15%+**
- Records archive pageviews: **↑ 20%+**
- User feedback: No longer mentions "confusing" or "overwhelming"

### Analytics Goals
- 6 priority metrics visible in dashboard
- A/B variants trackable across all events
- Weekly summary working via n8n

---

## Troubleshooting

### A/B Test Not Assigning
1. Check `unmask_get_experiments()` - is test `active: true`?
2. Clear browser cookies
3. Check for PHP errors in debug log

### Dashboard Shows No Data
1. Verify API key is set in Settings → Analytics API
2. Check n8n workflow is running
3. Test endpoint: `GET /wp-json/unmask/v1/analytics`

### Analytics Events Not Firing
1. Check browser console for errors
2. Verify GTM container is loaded
3. Check `$use_v2 = true` in `enqueue-analytics.php`

### Visitor Still Sees ISO Rail
1. Clear page cache (SG Optimizer)
2. Verify `is_user_logged_in()` conditional is in place
3. Check if logged in via incognito window
