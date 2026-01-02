# UNMASK Component Usage Guide

> Single source of truth for UNMASK component usage.
> Last updated: 2025-01-02

---

## Philosophy

- **No page builders.** Pages are PHP templates.
- **Components are template parts** called via `get_template_part()`
- **Shortcodes exist ONLY** for Gutenberg blocks when PHP isn't possible
- **All styling** comes from our CSS design system—no inline styles

---

## Available Components

### Button

**PHP (preferred):**
```php
get_template_part('template-parts/components/unmask-button', null, [
    'text'   => 'reserve studio',
    'type'   => 'primary',   // primary|secondary|ghost|danger
    'size'   => 'md',        // sm|md|lg
    'href'   => '/factory/book/',
    'target' => '_blank',    // optional
    'icon'   => '',          // optional icon class
]);
```

**Gutenberg shortcode** (only when PHP isn't possible):
```
[unmask_button text="reserve studio" type="primary" href="/factory/book/"]
```

---

### Badge

**PHP:**
```php
get_template_part('template-parts/components/unmask-badge', null, [
    'text' => 'D-047',
    'type' => 'drone',       // published|draft|expired|active|drone|visitor|schedule|available
    'size' => 'md',          // sm|md|lg
    'dot'  => true,          // show status dot
]);
```

**Gutenberg shortcode:**
```
[unmask_badge text="D-047" type="drone"]
```

---

### Avatar

**PHP:**
```php
get_template_part('template-parts/components/unmask-avatar', null, [
    'user_id' => get_current_user_id(),
    'size'    => 'lg',       // xs|sm|md|lg|xl
    'online'  => true,       // show online status
    'square'  => false,      // square avatar (default: round)
    'href'    => bp_core_get_user_domain($user_id), // optional link
]);
```

**Gutenberg shortcode:**
```
[unmask_avatar user_id="1" size="lg" online="true"]
```

---

### Card (Full Bleed Record Card)

**PHP with post_id** (auto-fetches image, title, excerpt, file_id):
```php
get_template_part('template-parts/components/unmask-card-fullbleed', null, [
    'post_id' => get_the_ID(),
]);
```

**PHP with manual data:**
```php
get_template_part('template-parts/components/unmask-card-fullbleed', null, [
    'image'      => $image_url,
    'file_id'    => 'UM-024',
    'type_badge' => 'interview',   // interview|editorial|session|archive
    'subject'    => 'Magnus',
    'desc'       => 'On becoming...',
    'href'       => '/record/magnus/',
]);
```

**Gutenberg shortcode:**
```
[unmask_card post_id="123"]
```

---

### ISO Card

**PHP:**
```php
get_template_part('template-parts/components/unmask-iso-card', null, [
    'user_id'          => get_current_user_id(),
    'show_designation' => true,    // show D-XXX or V-XXX
    'show_avatar'      => true,
    'show_name'        => true,
    'variant'          => 'default', // default|compact|expanded
]);
```

**Gutenberg shortcode:**
```
[unmask_iso_card user_id="1" show_designation="true"]
```

---

## Building Page Templates

### File Locations

Page templates go in the **theme root**:
```
page-homepage.php
page-factory.php
page-iso-board.php
page-dashboard.php
page-magazine.php
```

Page partials go in **template-parts/pages/**:
```
template-parts/pages/hero.php
template-parts/pages/records-grid.php
template-parts/pages/member-directory.php
```

### Example Page Template

```php
<?php
/**
 * Template Name: Homepage
 */
get_header();
?>

<main class="site-main">
    <section class="unmask-region hero">
        <div class="unmask-container">
            <div class="unmask-stack--lg">
                <h1>Welcome to UNMASK</h1>
                <?php get_template_part('template-parts/components/unmask-button', null, [
                    'text' => 'enter the archive',
                    'type' => 'primary',
                    'href' => '/magazine/',
                ]); ?>
            </div>
        </div>
    </section>

    <section class="unmask-region records-grid">
        <div class="unmask-container">
            <div class="unmask-grid--md">
                <?php
                $records = new WP_Query([
                    'post_type'      => 'record',
                    'posts_per_page' => 6
                ]);

                while ($records->have_posts()) : $records->the_post();
                    get_template_part('template-parts/components/unmask-card-fullbleed', null, [
                        'post_id' => get_the_ID()
                    ]);
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
```

---

## CSS Classes Reference

All components use classes from our design system. Files load in this order:

| File | Purpose |
|------|---------|
| `00-design-system.css` | Design tokens (colors, spacing, typography) |
| `unmask-layout-system.css` | Layout primitives (stack, cluster, grid, etc.) |
| `unmask-components.css` | Component styles (buttons, cards, badges, etc.) |
| `unmask-buddyboss-bridge-v1.css` | BuddyBoss integration overrides |

### Common Layout Classes

```css
/* Containers */
.unmask-container          /* max-width: 1200px, centered */
.unmask-container--narrow  /* max-width: 680px */
.unmask-container--wide    /* max-width: 1400px */

/* Vertical spacing */
.unmask-stack              /* flex column with gap */
.unmask-stack--sm          /* tight spacing */
.unmask-stack--lg          /* loose spacing */

/* Horizontal grouping */
.unmask-cluster            /* flex row with wrap */
.unmask-cluster--between   /* space-between */

/* Grid */
.unmask-grid               /* auto-fit responsive grid */
.unmask-grid--2            /* 2 columns */
.unmask-grid--3            /* 3 columns */

/* Sections */
.unmask-region             /* vertical padding for sections */
.unmask-region--lg         /* extra padding */
```

---

## Helper Functions

### Get User Designation

```php
// Returns "D-047" for Disruptors, "V-012" for Visionaries
$designation = unmask_get_user_designation($user_id);
```

---

## DO NOT USE

| Forbidden | Reason |
|-----------|--------|
| Elementor | We don't use page builders |
| Any page builder | PHP templates only |
| Inline styles | All styles in CSS files |
| Components outside design system | Consistency |
| Direct CSS in templates | Use existing classes |

---

## File Structure

```
unmask-child-theme/
├── assets/css/
│   ├── 00-design-system.css
│   ├── unmask-layout-system.css
│   ├── unmask-components.css
│   └── unmask-buddyboss-bridge-v1.css
├── includes/
│   └── unmask-shortcodes-v1.php
├── template-parts/
│   ├── components/
│   │   ├── unmask-button.php
│   │   ├── unmask-badge.php
│   │   ├── unmask-avatar.php
│   │   ├── unmask-card-fullbleed.php
│   │   └── unmask-iso-card.php
│   └── pages/
├── docs/
│   └── COMPONENT-USAGE-GUIDE.md
├── page-*.php (page templates)
├── functions.php
└── style.css
```
