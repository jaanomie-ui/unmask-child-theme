# GTM Implementation Guide — UNMASK

**Container ID:** GTM-5NJKFDSX
**GA4 Measurement ID:** G-27MFSBL8G6
**Created:** 2026-01-13

---

## Pre-Flight Checklist

- [x] GTM container created and ID obtained
- [x] GA4 property created and Measurement ID obtained
- [x] GTM head script added to functions.php (wp_head hook, priority 1)
- [x] GTM noscript added to functions.php (wp_body_open hook)
- [x] Deployed to staging server
- [x] Verified GTM code present in page source (2 instances)
- [ ] Verified in GTM Preview mode
- [ ] Verified in GA4 Realtime reports
- [ ] DataLayer events firing correctly

---

## Implementation Steps

### Step 1: Add GTM Functions to functions.php

Location: `/Users/ja/unmask-child-theme/functions.php`

**Head Script (inject in `<head>`):**
```php
/**
 * Google Tag Manager - Head Script
 * Must be as high in <head> as possible
 */
function unmask_gtm_head() {
    ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5NJKFDSX');</script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action('wp_head', 'unmask_gtm_head', 1);
```

**Body Script (inject after `<body>` opens):**
```php
/**
 * Google Tag Manager - NoScript Fallback
 * Must be immediately after opening <body> tag
 */
function unmask_gtm_body() {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5NJKFDSX"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action('wp_body_open', 'unmask_gtm_body', 1);
```

### Step 2: Deploy to Staging

```bash
# Deploy functions.php
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/functions.php \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/

# Fix permissions
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "chmod 644 ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/functions.php"
```

### Step 3: Verify Installation

1. **GTM Preview Mode:**
   - Go to tagmanager.google.com
   - Click "Preview" button
   - Enter staging URL: https://staging4.houseofanomie.com
   - Verify "Container Loaded" event fires

2. **Browser DevTools:**
   - Open DevTools → Network tab
   - Filter by "gtm"
   - Confirm gtm.js loads successfully
   - Check Console for any errors

3. **GA4 Realtime:**
   - Go to analytics.google.com
   - Navigate to Reports → Realtime
   - Visit staging site in another tab
   - Confirm pageview appears

---

## Event Tracking Plan (Flywheel Metrics)

### Phase 1: Foundation Events

| Event Name | Trigger | Parameters |
|------------|---------|------------|
| `page_view` | All pages | page_title, page_location |
| `scroll` | 25%, 50%, 75%, 90% | percent_scrolled |
| `cta_click` | Any CTA button | cta_text, cta_location |

### Phase 2: Engagement Events

| Event Name | Trigger | Parameters |
|------------|---------|------------|
| `hero_click` | Hero section click | destination_url |
| `rail_scroll` | Horizontal rail scroll | rail_name, direction |
| `card_click` | Any content card | card_type, card_title |
| `iso_view` | ISO listing viewed | iso_id, iso_type |

### Phase 3: Conversion Events

| Event Name | Trigger | Parameters |
|------------|---------|------------|
| `registration_start` | Visit registration page | referrer |
| `registration_complete` | Account created | user_type |
| `iso_submit_start` | Start ISO submission | iso_type |
| `iso_submit_complete` | ISO published | iso_id |

---

## DataLayer Push Examples

For custom event tracking, push to dataLayer:

```javascript
// CTA Click
dataLayer.push({
  'event': 'cta_click',
  'cta_text': 'tell your story',
  'cta_location': 'hero'
});

// Card Click
dataLayer.push({
  'event': 'card_click',
  'card_type': 'record',
  'card_title': 'Interview with Artist'
});
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| GTM not loading | Check wp_head hook priority, clear cache |
| noscript not showing | Verify wp_body_open is supported by theme |
| Events not firing | Check dataLayer syntax in console |
| GA4 not receiving | Verify GA4 tag configured in GTM |

---

## Files Modified

- `functions.php` — Added GTM head and body hooks

## Related Documentation

- [GA4 Implementation Plan](./ga4-implementation-plan.md)
- [User Journey — Seduction Flywheel](Notion)
- [Error Log](./gtm-error-log.md)
- [Session Tracker](./gtm-session-tracker.md)
