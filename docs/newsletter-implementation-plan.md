# Newsletter & Email Capture Implementation Plan

**Created:** 2026-01-09
**Status:** Phase 3 Complete (Phases 1-3 done, Phase 4 pending)
**MC4WP Version:** 4.10.9 (active, no forms deployed)
**Newsletter Frequency:** Weekly

---

## Executive Summary

This document outlines the implementation plan for a comprehensive newsletter capture system across UNMASK. The approach uses contextual, card-based capture points that integrate seamlessly with the existing design system.

---

## Table of Contents

1. [Current State Audit](#current-state-audit)
2. [Implementation Phases](#implementation-phases)
3. [Template Conflict Analysis](#template-conflict-analysis)
4. [Technical Specifications](#technical-specifications)
5. [Error Log](#error-log)
6. [Rollback Plan](#rollback-plan)

---

## Current State Audit

### Email Infrastructure
| Component | Status | Notes |
|-----------|--------|-------|
| MC4WP Plugin | Active v4.10.9 | No forms deployed |
| Mailchimp API | Connected | Verified connection |
| BuddyBoss Email | Disabled | Was causing spam |
| MemberPress Emails | Active | Transactional only |

### Existing Capture Points
| Location | Type | Status |
|----------|------|--------|
| Registration | None | **NEEDS CHECKBOX** |
| Pink Panthers | Notify button | **BROKEN (P-006)** |
| Homepage | None | Opportunity |
| Archive/Magazine | None | Opportunity |
| ISO Board | Alert banner | Logged-out only |
| Footer | None | Global opportunity |

---

## Implementation Phases

### Phase 1: Foundation & Quick Wins
**Priority:** Critical
**Timeline:** Immediate

#### 1.1 Fix Pink Panthers Notify Button (P-006)
**File:** `page-templates/page-pink-panthers.php`
**Line:** 136

**Current (Broken):**
```php
<button class="pp-notify-btn">notify me</button>
```

**Proposed Fix:**
```php
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="pp-notify-form">
    <?php wp_nonce_field('pp_notify_submit', 'pp_notify_nonce'); ?>
    <input type="hidden" name="action" value="pp_notify_submit">
    <input type="email" name="notify_email" placeholder="your email" class="pp-notify-input" required>
    <button type="submit" class="pp-notify-btn">notify me</button>
</form>
```

**New Handler Required:** `inc/pink-panthers-submissions.php`
- Add `pp_notify_submit` action handler
- Store in options or custom table
- Tag in Mailchimp: `pp-ticket-interest`

#### 1.2 Registration Newsletter Checkbox
**File:** `functions.php` or new `inc/newsletter-registration.php`
**Hook:** `mepr-signup` or `bp_before_registration_submit_buttons`

**Implementation:**
```php
add_action('bp_before_registration_submit_buttons', 'unmask_newsletter_checkbox');
function unmask_newsletter_checkbox() {
    ?>
    <div class="unmask-newsletter-opt-in">
        <label>
            <input type="checkbox" name="newsletter_opt_in" value="1" checked>
            <span>Send me the weekly UNMASK newsletter</span>
        </label>
    </div>
    <?php
}

add_action('user_register', 'unmask_handle_newsletter_opt_in');
function unmask_handle_newsletter_opt_in($user_id) {
    if (!empty($_POST['newsletter_opt_in'])) {
        $user = get_userdata($user_id);
        // Add to Mailchimp via MC4WP API
        if (function_exists('mc4wp_get_api_v3')) {
            $api = mc4wp_get_api_v3();
            $list_id = mc4wp_get_option('general', 'lists', [])[0] ?? '';
            if ($list_id) {
                $api->add_list_member($list_id, [
                    'email_address' => $user->user_email,
                    'status' => 'subscribed',
                    'merge_fields' => [
                        'FNAME' => $user->first_name,
                    ],
                    'tags' => ['registration', 'member']
                ]);
            }
        }
    }
}
```

---

### Phase 2: Newsletter Card Component
**Priority:** High
**Depends on:** Phase 1 completion

#### 2.1 Create Reusable Newsletter Card
**New File:** `template-parts/components/newsletter-card.php`

Uses existing card system modifiers:
- Base: `.card`
- Type: `.card--newsletter` (new)
- Context: `.card--grid`, `.card--compact`, `.card--elevated`
- State: `.card--stranger` (for logged-out users)

**Parameters:**
```php
$args = [
    'context'   => 'homepage',     // homepage|archive|iso|factory|footer
    'variant'   => 'default',      // default|compact|minimal
    'headline'  => '',             // Optional override
    'subhead'   => '',             // Optional override
    'tags'      => [],             // Mailchimp tags to apply
];
```

#### 2.2 Newsletter Card CSS
**New File:** `assets/css/components/newsletter-card.css`

```css
/* Newsletter Card - Extends .card base */
.card--newsletter {
    --card-border-color: var(--primitive-red);
    border-style: dashed;
    text-align: center;
}

.card--newsletter:focus-within {
    border-style: solid;
}

.newsletter-card__headline {
    font-family: var(--font-display);
    font-size: var(--text-lg);
    text-transform: lowercase;
    margin-bottom: var(--space-xs);
}

.newsletter-card__input {
    width: 100%;
    padding: var(--space-sm);
    border: 1px solid var(--border-subtle);
    background: var(--bg-primary);
    font-family: var(--font-mono);
    font-size: var(--text-sm);
}

.newsletter-card__submit {
    width: 100%;
    padding: var(--space-sm);
    background: var(--primitive-red);
    color: var(--text-inverse);
    border: none;
    font-family: var(--font-mono);
    cursor: pointer;
    transition: opacity 0.2s;
}

.newsletter-card__submit:hover {
    opacity: 0.9;
}

/* Compact variant for rails */
.card--newsletter.card--compact {
    min-height: auto;
    padding: var(--space-md);
}

/* Stranger variant - more prominent */
.card--newsletter.card--stranger {
    background: var(--bg-elevated);
    border-width: 2px;
}
```

---

### Phase 3: Contextual Integration
**Priority:** Medium
**Depends on:** Phase 2 completion

#### 3.1 Homepage Rail Integration
**File:** `template-parts/homepage/mobile-layout.php`
**Insert after:** Activity section (~line 227)

```php
<!-- Newsletter Rail -->
<?php if (!is_user_logged_in()) : ?>
<section class="homepage-rail homepage-rail--newsletter">
    <div class="rail-header">
        <span class="rail-label">stay connected</span>
    </div>
    <?php get_template_part('template-parts/components/newsletter-card', null, [
        'context' => 'homepage',
        'variant' => 'default',
        'tags'    => ['homepage', 'stranger']
    ]); ?>
</section>
<?php endif; ?>
```

#### 3.2 Archive/Magazine Integration
**File:** `page-templates/template-archive-magazine.php`
**Insert after:** Submit card (~line 219)

```php
// Insert newsletter card after 8th item for strangers
if ($counter === 8 && !is_user_logged_in()) :
?>
    <article class="archive-newsletter-card">
        <?php get_template_part('template-parts/components/newsletter-card', null, [
            'context' => 'archive',
            'variant' => 'compact',
            'headline' => 'get notified of new records',
            'tags'    => ['archive', 'records']
        ]); ?>
    </article>
<?php
endif;
```

#### 3.3 ISO Board Integration
**File:** `page-templates/template-iso-board.php`
**Replace/augment:** Alert banner for logged-out users (~line 86)

```php
<?php if (!$is_logged_in) : ?>
<div class="iso-alert-banner" id="isoAlertBanner">
    <span class="iso-alert-text"><?php echo esc_html($iso_alert_text); ?></span>
    <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="iso-alert-cta"><?php echo esc_html($iso_alert_cta); ?></a>
</div>
<div class="iso-newsletter-prompt">
    <?php get_template_part('template-parts/components/newsletter-card', null, [
        'context' => 'iso',
        'variant' => 'minimal',
        'headline' => 'get ISO alerts in your inbox',
        'tags'    => ['iso-board', 'stranger']
    ]); ?>
</div>
<?php endif; ?>
```

#### 3.4 Footer Global Capture
**File:** `footer.php` (child theme override)
**Location:** Before closing `</footer>`

```php
<?php if (!is_user_logged_in()) : ?>
<div class="footer-newsletter">
    <?php get_template_part('template-parts/components/newsletter-card', null, [
        'context' => 'footer',
        'variant' => 'compact',
        'headline' => 'join the newsletter',
        'tags'    => ['footer', 'stranger']
    ]); ?>
</div>
<?php endif; ?>
```

---

### Phase 4: Mailchimp Segmentation
**Priority:** Low
**Depends on:** All capture points live

#### Tag Strategy
| Tag | Applied When |
|-----|--------------|
| `registration` | Sign up with checkbox |
| `member` | Registered user |
| `stranger` | Email-only capture |
| `homepage` | Homepage form |
| `archive` | Archive page form |
| `iso-board` | ISO board form |
| `pp-ticket-interest` | Pink Panthers notify |
| `factory` | Factory page form |

#### Segments to Create
1. **All Subscribers** - Everyone
2. **Members Only** - Has `member` tag
3. **Non-Members** - Has `stranger`, not `member`
4. **Event Interest** - Has `pp-ticket-interest` or `factory`
5. **Content Seekers** - Has `archive` or `iso-board`

---

## Template Conflict Analysis

**Audit Completed:** 2026-01-09

### Files to Modify

| File | Modification | Risk | Conflicts | Audit Status |
|------|--------------|------|-----------|--------------|
| `page-pink-panthers.php` | Replace button with form | Low | None - isolated component | CLEAR |
| `functions.php` | Add registration hook | Low | Has existing mepr hooks (412, 457, 495) - EXTEND | CLEAR |
| `mobile-layout.php` | Add newsletter rail | Low | Follows existing rail pattern | CLEAR |
| `template-archive-magazine.php` | Add card insertion | Low | Follows submit card pattern (line 208) | CLEAR |
| `template-iso-board.php` | Add newsletter prompt | Low | Augments existing alert (line 86) | CLEAR |
| `footer.php` | Add footer capture | Low | Child override exists (50 lines) - insert before line 35 | CLEAR |

### Existing Hook Usage (functions.php)

```
Line 412: add_action('mepr-checkout-before-submit', 'unmask_add_signup_fields')
Line 446: add_filter('mepr-validate-signup', 'unmask_validate_signup_fields')
Line 457: add_action('mepr-signup', 'unmask_save_signup_fields')
Line 495: add_action('mepr-signup', 'unmask_sync_to_buddyboss', 20)
```

**Integration approach:** Add newsletter checkbox to existing `unmask_add_signup_fields()` function (line 412-441) and handle subscription in existing `unmask_save_signup_fields()` function (line 457-485).

### CSS Cascade Analysis

Newsletter card CSS will be loaded after:
1. `00-design-system.css` - Tokens
2. `02-components.css` - Base components
3. `unmask-cards.css` - Card system (via `inc/enqueue-cards.php`)

**No conflicts expected** - Newsletter card extends existing `.card` base class.

### Footer Structure (footer.php)

```
Line 31-33: Close BuddyBoss containers
Line 35-42: <footer class="unmask-footer">
Line 44: Close #page
Line 46: wp_footer()
```

**Integration point:** Insert newsletter capture BEFORE line 35 (after containers close, before footer).

### JavaScript Dependencies

Newsletter forms use standard `<form>` submission to `admin-post.php`. No JavaScript required for basic functionality.

Optional enhancement: AJAX submission can be added later without breaking base functionality.

---

## Technical Specifications

### Form Handler Architecture

**New File:** `inc/newsletter-handler.php`

```php
<?php
/**
 * Newsletter Form Handler
 * Processes all newsletter signups and syncs to Mailchimp
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Handle newsletter form submission
 */
add_action('admin_post_unmask_newsletter_subscribe', 'unmask_handle_newsletter');
add_action('admin_post_nopriv_unmask_newsletter_subscribe', 'unmask_handle_newsletter');

function unmask_handle_newsletter() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['newsletter_nonce'] ?? '', 'unmask_newsletter')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $email = sanitize_email($_POST['newsletter_email'] ?? '');
    $context = sanitize_text_field($_POST['newsletter_context'] ?? 'general');
    $tags = array_map('sanitize_text_field', (array)($_POST['newsletter_tags'] ?? []));

    if (!is_email($email)) {
        wp_die('Please enter a valid email address.', 'Invalid Email', ['back_link' => true]);
    }

    // Add to Mailchimp
    $result = unmask_add_to_mailchimp($email, $tags);

    // Redirect with status
    $redirect = add_query_arg(
        'newsletter_status',
        $result ? 'success' : 'error',
        wp_get_referer() ?: home_url()
    );

    wp_safe_redirect($redirect);
    exit;
}

/**
 * Add email to Mailchimp list with tags
 */
function unmask_add_to_mailchimp($email, $tags = []) {
    if (!function_exists('mc4wp_get_api_v3')) {
        error_log('UNMASK Newsletter: MC4WP not available');
        return false;
    }

    try {
        $api = mc4wp_get_api_v3();
        $list_id = mc4wp_get_option('general', 'lists', [])[0] ?? '';

        if (!$list_id) {
            error_log('UNMASK Newsletter: No Mailchimp list configured');
            return false;
        }

        $api->add_list_member($list_id, [
            'email_address' => $email,
            'status' => 'subscribed',
            'tags' => $tags
        ]);

        return true;
    } catch (Exception $e) {
        error_log('UNMASK Newsletter Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Handle Pink Panthers notify specifically
 */
add_action('admin_post_pp_notify_submit', 'unmask_handle_pp_notify');
add_action('admin_post_nopriv_pp_notify_submit', 'unmask_handle_pp_notify');

function unmask_handle_pp_notify() {
    if (!wp_verify_nonce($_POST['pp_notify_nonce'] ?? '', 'pp_notify_submit')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $email = sanitize_email($_POST['notify_email'] ?? '');

    if (!is_email($email)) {
        wp_die('Please enter a valid email address.', 'Invalid Email', ['back_link' => true]);
    }

    // Add to Mailchimp with PP tag
    $result = unmask_add_to_mailchimp($email, ['pp-ticket-interest', 'event-interest']);

    // Store locally for ticket release notification
    $notify_list = get_option('unmask_pp_notify_list', []);
    if (!in_array($email, $notify_list)) {
        $notify_list[] = $email;
        update_option('unmask_pp_notify_list', $notify_list);
    }

    $redirect = add_query_arg(
        'pp_notify',
        $result ? 'success' : 'error',
        wp_get_referer() ?: home_url('/pink-panthers/')
    );

    wp_safe_redirect($redirect);
    exit;
}
```

### Enqueue File

**New File:** `inc/enqueue-newsletter.php`

```php
<?php
/**
 * Enqueue Newsletter Assets
 */

if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', 'unmask_enqueue_newsletter_assets');

function unmask_enqueue_newsletter_assets() {
    // Only load on pages with newsletter forms
    if (!is_user_logged_in()) {
        wp_enqueue_style(
            'unmask-newsletter-card',
            get_stylesheet_directory_uri() . '/assets/css/components/newsletter-card.css',
            ['unmask-cards'],
            filemtime(get_stylesheet_directory() . '/assets/css/components/newsletter-card.css')
        );
    }
}
```

---

## Error Log

### Pre-Implementation Issues

| ID | Date | Issue | Status | Resolution |
|----|------|-------|--------|------------|
| E-001 | 2026-01-09 | Pink Panthers notify button non-functional | **Resolved** | Form + handler + CSS deployed |
| E-002 | 2026-01-09 | No registration newsletter opt-in | **Resolved** | Checkbox + handler + CSS deployed |
| E-003 | 2026-01-09 | MC4WP active but no forms deployed | Open | Phase 2+ |
| E-004 | 2026-01-09 | BuddyBoss notifications spam | Resolved | Disabled by user |

### Implementation Issues

| ID | Date | Issue | Status | Resolution |
|----|------|-------|--------|------------|
| I-001 | 2026-01-09 | Pink Panthers notify form deployed | Resolved | Added form, handler, CSS, success toast |
| I-002 | 2026-01-09 | Registration checkbox deployed | Resolved | Added to mepr hooks, CSS styles |
| I-003 | 2026-01-09 | Phase 2: Newsletter card component | Resolved | Created template, CSS, handler, enqueue; deployed to staging |
| I-004 | 2026-01-09 | Phase 3: Homepage integration | Resolved | Added newsletter section to mobile-layout.php |
| I-005 | 2026-01-09 | Phase 3: Archive integration | Resolved | Added newsletter card after 8th item for non-logged-in users |
| I-006 | 2026-01-09 | Phase 3: ISO Board integration | Resolved | Added newsletter prompt below alert banner for non-logged-in users |
| I-007 | 2026-01-09 | Phase 3: Footer integration | Resolved | Added footer-newsletter section before footer for non-logged-in users |

*Issues will be logged here during implementation*

---

## Rollback Plan

### Phase 1 Rollback
```bash
# Restore Pink Panthers template
git checkout HEAD -- page-templates/page-pink-panthers.php

# Remove registration hook
# Comment out or remove from functions.php:
# require_once 'inc/newsletter-registration.php';
```

### Phase 2 Rollback
```bash
# Remove newsletter card component
rm template-parts/components/newsletter-card.php
rm assets/css/components/newsletter-card.css

# Remove from functions.php:
# require_once 'inc/enqueue-newsletter.php';
# require_once 'inc/newsletter-handler.php';
```

### Phase 3 Rollback
```bash
# Restore modified templates
git checkout HEAD -- template-parts/homepage/mobile-layout.php
git checkout HEAD -- page-templates/template-archive-magazine.php
git checkout HEAD -- page-templates/template-iso-board.php
git checkout HEAD -- footer.php
```

---

## Mailchimp MCP Notes

### Available Options Researched
1. **CData MCP Server** - Requires JDBC driver, OAuth, Maven build
2. **Bryan G Smith's MCP** - Node.js based, simpler setup
3. **Composio** - Cloud-based, managed OAuth

### Recommendation
For this project, direct MC4WP API integration is sufficient. MCP setup can be added later for automation of:
- Campaign creation
- Segment management
- Analytics retrieval

---

## Checklist

### Phase 1
- [x] Fix Pink Panthers notify button
- [x] Add form handler for PP notify
- [x] Add registration checkbox
- [x] Add registration handler
- [x] Test both flows (HTTP 200, forms render)
- [x] Deploy to staging

### Phase 2
- [x] Create newsletter-card.php template
- [x] Create newsletter-card.css styles
- [x] Create newsletter-handler.php
- [x] Create enqueue-newsletter.php
- [x] Add requires to functions.php
- [x] Test card component (HTTP 200, renders correctly)
- [x] Deploy to staging

### Phase 3
- [x] Integrate homepage rail (added to mobile-layout.php)
- [x] Integrate archive page (after 8th item for strangers)
- [x] Integrate ISO board (below alert banner for strangers)
- [x] Integrate footer (footer-newsletter section for strangers)
- [x] Test all integration points (HTTP 200, cards render)
- [x] Deploy to staging

### Phase 4
- [ ] Create Mailchimp segments
- [ ] Test tag application
- [ ] Verify subscriber flow
- [ ] Document segment usage

---

*Document maintained by Claude Code. Last updated: 2026-01-09 (Phase 3 complete)*
