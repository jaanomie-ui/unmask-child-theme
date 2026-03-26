<?php
/**
 * UNMASK Child Theme Functions
 *
 * All custom PHP snippets consolidated here
 */

// ============================================================================
// GOOGLE TAG MANAGER
// ============================================================================

/**
 * Google Tag Manager - Head Script
 * Must be as high in <head> as possible
 * Container ID: GTM-5NJKFDSX
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

// ============================================================================
// STYLES & SCRIPTS
// ============================================================================

// Enqueue parent theme styles and CSS partials
add_action('wp_enqueue_scripts', 'unmask_enqueue_styles');
function unmask_enqueue_styles() {
    $theme_version = wp_get_theme()->get('Version');
    $css_dir = get_stylesheet_directory_uri() . '/assets/css/';

    // Parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Child theme main stylesheet (for WordPress theme detection)
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'), $theme_version);

    // CSS Partials - loaded in cascade order
    $css_partials = array(
        '00-design-system' => 'Design system tokens',
        '01-base'          => 'Base styles and resets',
        '02-components'    => 'UI components',
        '03-buddyboss'     => 'BuddyBoss overrides',
        '04-memberpress'   => 'MemberPress overrides',
        '05-plugins'       => 'Plugin overrides',
        '06-pages'         => 'Page-specific styles',
        '07-dashboard'     => 'Dashboard styles',
        '08-utilities'     => 'Utility classes',
    );

    $prev_handle = 'child-style';

    foreach ($css_partials as $file => $description) {
        $handle = 'unmask-' . $file;
        wp_enqueue_style(
            $handle,
            $css_dir . $file . '.css',
            array($prev_handle),
            $theme_version
        );
        $prev_handle = $handle;
    }

    // Layout system - loaded after design system, before other partials use it
    wp_enqueue_style(
        'unmask-layout-system',
        $css_dir . 'unmask-layout-system.css',
        array('unmask-00-design-system'),
        $theme_version
    );

    // Component library - loaded after layout system
    wp_enqueue_style(
        'unmask-components',
        $css_dir . 'unmask-components.css',
        array('unmask-layout-system'),
        $theme_version
    );

    // Unified ISO card component - shared across homepage and ISO board
    wp_enqueue_style(
        'unmask-iso-card-unified',
        $css_dir . 'components/iso-card-unified.css',
        array('unmask-components'),
        $theme_version
    );

    // BuddyBoss bridge - integrates components with BuddyBoss
    wp_enqueue_style(
        'unmask-buddyboss-bridge',
        $css_dir . 'unmask-buddyboss-bridge-v1.css',
        array('unmask-components'),
        $theme_version
    );

    // Visual fixes - screenshot review overrides (loads last for cascade priority)
    wp_enqueue_style(
        'unmask-visual-fixes',
        $css_dir . 'visual-fixes.css',
        array('unmask-buddyboss-bridge'),
        $theme_version
    );

    // Visitor Grid Widget styles
    wp_enqueue_style(
        'unmask-visitor-grid-widget',
        $css_dir . 'widgets/visitor-grid.css',
        array('unmask-00-design-system'),
        $theme_version
    );

    // Toast notification component (global - used for error/success messages)
    wp_enqueue_style(
        'unmask-toast',
        $css_dir . 'components/toast.css',
        array('unmask-00-design-system'),
        $theme_version
    );
    wp_enqueue_script(
        'unmask-toast',
        get_stylesheet_directory_uri() . '/assets/js/unmask-toast.js',
        array(),
        $theme_version,
        true
    );

    // Homepage styles - only on homepage template
    if (is_page_template('page-templates/template-homepage.php')) {
        wp_enqueue_style(
            'unmask-homepage',
            $css_dir . 'unmask-homepage.css',
            array('unmask-00-design-system'),
            $theme_version
        );
    }

    // Single record styles - only on single posts
    if (is_single() && get_post_type() === 'post') {
        wp_enqueue_style(
            'unmask-single-record',
            $css_dir . 'unmask-single-record.css',
            array('unmask-00-design-system'),
            $theme_version
        );

        // Gallery JavaScript
        wp_enqueue_script(
            'unmask-gallery',
            get_stylesheet_directory_uri() . '/assets/js/unmask-gallery.js',
            array(),
            $theme_version,
            true
        );
    }
}

/* ==========================================================================
   INCLUDES
   ========================================================================== */

// Shortcodes v1 - Component shortcodes with template parts
require_once get_stylesheet_directory() . '/includes/unmask-shortcodes-v1.php';

// Unified Card System - global card architecture (loads before page-specific CSS)
require_once get_stylesheet_directory() . '/inc/enqueue-cards.php';

// Factory page styles (unified - includes booking form)
require_once get_stylesheet_directory() . '/inc/enqueue-factory.php';

// Magazine Archive page styles and scripts
require_once get_stylesheet_directory() . '/inc/enqueue-archive-magazine.php';

// Tag Archive page styles (reuses archive-magazine base)
require_once get_stylesheet_directory() . '/inc/enqueue-tag-archive.php';

// Single Post styles (article typography)
require_once get_stylesheet_directory() . '/inc/enqueue-single-post.php';

// Session Log styles (Berkeley Mono terminal aesthetic for session logs)
require_once get_stylesheet_directory() . '/inc/enqueue-session-log.php';

// Session Log template filter - Apply single-session-log.php to posts in Session Logs category
add_filter('template_include', 'unmask_session_log_template', 99);
function unmask_session_log_template($template) {
    if (is_single() && in_category(130)) { // Session Logs category ID
        $new_template = locate_template(['single-session-log.php']);
        if ($new_template) {
            return $new_template;
        }
    }
    return $template;
}

// Profile/Dossier page styles
require_once get_stylesheet_directory() . '/inc/enqueue-profile.php';

// ISO Board → moved to hoa-core/_deprecated/iso-board/ (not loaded — WC-11)

// Submit Hub page styles
require_once get_stylesheet_directory() . '/inc/enqueue-submit.php';

// ISO Post → moved to hoa-core/_deprecated/iso-board/ (not loaded — WC-11)

// Homepage grid layout and cards
require_once get_stylesheet_directory() . '/inc/enqueue-homepage-grid.php';

// Dossier → moved to hoa-core/includes/dossier/ (WC-10)

// Profile Edit - Split panel + bottom sheet interface
require_once get_stylesheet_directory() . '/inc/enqueue-profile-edit.php';

// Widgets
require_once get_stylesheet_directory() . '/inc/widgets/class-visitor-grid-widget.php';

// Mobile Bottom Navigation
require_once get_stylesheet_directory() . '/inc/enqueue-bottom-nav.php';

// Collapsing Header (mobile)
require_once get_stylesheet_directory() . '/inc/collapsing-header.php';

// Performance Optimizations (dequeue unused assets)
require_once get_stylesheet_directory() . '/inc/performance-optimizations.php';

// Pink Panthers Submissions → moved to hoa-core/includes/pink-panthers/ (WC-05)

// Onboarding System → moved to hoa-core/includes/membership/ (WC-08)

// Drone Dashboard - Training console for House of Anomie drones
require_once get_stylesheet_directory() . '/inc/enqueue-drone-dashboard.php';

// HOA Calendar → moved to hoa-core/includes/hoa-calendar/ (WC-11)

// Transmissions Archive - Session logs archive for drones
require_once get_stylesheet_directory() . '/inc/enqueue-transmissions-archive.php';

// Training Guide - Drone conditioning sequences and protocols reference
require_once get_stylesheet_directory() . '/inc/enqueue-training-guide.php';

// Hive Mistress State Management - Drone conditioning state, tolerances, and logging
require_once get_stylesheet_directory() . '/inc/hive-mistress-state.php';

// Measurement Algorithms - Objective metrics for loop/pattern/state verification
require_once get_stylesheet_directory() . '/inc/measurement-algorithms.php';

// Rehearsal Coordination → moved to hoa-core/includes/rehearsal/ (WC-06)

// Hive Mistress Prompts - System voice, lore context, and AI prompt construction
// V7: OS/Shell alignment protocol (third-person only, ERROR CODE 87, Drones' Creed, choice architecture)
require_once get_stylesheet_directory() . '/inc/hive-mistress-prompts-v7.php';

// V6: Performance training focus (based on Session Log 002 - 4-part ritual)
require_once get_stylesheet_directory() . '/inc/hive-mistress-prompts-v6.php';

// Hive Mistress Canon Mode - d001-only Canon-building layer [hm_canon_dashboard] shortcode
// Additive layer — original HM conditioning system is unchanged for all other users
require_once get_stylesheet_directory() . '/inc/hive-mistress-canon.php';

// Hive Mistress Chat Shortcode → moved to hoa-core/includes/hive-mistress/ (WC-09 relay)

// Hive Mistress RAG System - Semantic lore search using OpenAI + Supabase pgvector
require_once get_stylesheet_directory() . '/inc/hive-mistress-rag.php';

// Hive Mistress Chunker - Document processing, metadata extraction, and lore uploads
require_once get_stylesheet_directory() . '/inc/hive-mistress-chunker.php';

// Hive Mistress Profile Form - [hive_mistress_profile] shortcode for gear/safeword/availability
require_once get_stylesheet_directory() . '/inc/hive-mistress-profile-form.php';

// Hive Mistress Backfill Script - Generate summaries for existing sessions (WP-CLI + admin)
require_once get_stylesheet_directory() . '/inc/hive-mistress-backfill.php';

// Hive Mistress Chat - AI chatbot interface styles (page ID 2496)
require_once get_stylesheet_directory() . '/inc/enqueue-hive-mistress-chat.php';

// Hard Limits Checklist - Traffic light BDSM preferences form for drones
require_once get_stylesheet_directory() . '/inc/enqueue-hard-limits.php';

// Hard Limits Admin Viewer - WordPress admin page to view all filed limits
require_once get_stylesheet_directory() . '/inc/admin-hard-limits-viewer.php';

// Rehearsal Submissions DB → moved to hoa-core/includes/rehearsal/ (WC-06)

// Installation Dashboard - Admin page to track drone installation progress
require_once get_stylesheet_directory() . '/inc/admin-installation-dashboard.php';

// Session Auditor - Admin tool for measuring session progress (MUST load after Installation Dashboard)
require_once get_stylesheet_directory() . '/inc/admin-session-auditor.php';

// Training Guide - Shows all 16 loop completion criteria
require_once get_stylesheet_directory() . '/inc/admin-training-guide.php';

// require_once get_stylesheet_directory() . '/inc/hm-invite-system.php'; // FILE MISSING — commented out to restore site

/* ==========================================================================
   REGISTRATION PAGE STYLES
   ========================================================================== */

/**
 * Helper function to check if current page uses registration/welcome templates
 */
function unmask_is_registration_page() {
    // All onboarding/registration templates
    $onboarding_templates = array(
        'page-templates/page-register-visitor.php',
        'page-templates/page-welcome.php',
        'page-templates/page-welcome-orientation.php',
        'page-templates/page-welcome-start.php',
        'page-templates/page-welcome-complete.php',
    );

    // Method 1: Check page template slug
    $template_slug = get_page_template_slug();
    if (in_array($template_slug, $onboarding_templates, true)) {
        return true;
    }

    // Method 2: Check is_page_template (backup)
    foreach ($onboarding_templates as $template) {
        if (is_page_template($template)) {
            return true;
        }
    }

    // Method 3: Check body class (most reliable at body_class filter time)
    // This works because WordPress adds template classes before our filter
    global $post;
    if ($post && is_page()) {
        $stored_template = get_post_meta($post->ID, '_wp_page_template', true);
        if (in_array($stored_template, $onboarding_templates, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Filter body classes on registration/welcome pages
 *
 * WHY THIS IS NEEDED:
 * BuddyBoss adds layout classes (has-sidebar, page-sidebar, sidebar-right,
 * bb-buddypanel, etc.) to body that trigger parent theme layout rules.
 * Even though header-fullbleed.php skips the BuddyBoss header, these classes
 * still get added and cause layout conflicts.
 *
 * By removing these classes at the source, we eliminate the CSS battles
 * entirely - no need for !important overrides.
 */
add_filter('body_class', 'unmask_filter_registration_body_classes', PHP_INT_MAX);
function unmask_filter_registration_body_classes($classes) {
    // Only filter on registration/welcome pages
    if (!unmask_is_registration_page()) {
        return $classes;
    }

    // Classes to REMOVE - these trigger BuddyBoss layout rules
    $remove_classes = array(
        // Sidebar classes - prevent sidebar layout rules
        'has-sidebar',
        'page-sidebar',
        'sidebar-right',
        'sidebar-left',
        'blog-sidebar',
        'search-sidebar',
        'activity-sidebar-left',
        'activity-sidebar-right',
        'members-sidebar',
        'profile-sidebar',
        'groups-sidebar',
        'group-sidebar',
        'forums-sidebar',
        'woo-sidebar',

        // BuddyPanel classes - prevent panel offset rules
        'bb-buddypanel',
        'bb-buddypanel-left',
        'bb-buddypanel-right',
        'buddypanel-logo',

        // Header classes - prevent sticky header rules
        'sticky-header',

        // Other layout classes
        'header-style-1',
        'header-style-2',
        'header-style-3',
        'header-style-4',
        'header-style-5',
    );

    // BuddyBoss adds some classes as concatenated strings like 'has-sidebar blog-sidebar sidebar-right'
    // We need to split these and filter individual classes
    $new_classes = array();
    foreach ($classes as $class_entry) {
        // Split concatenated class strings
        $individual_classes = preg_split('/\s+/', trim($class_entry));
        foreach ($individual_classes as $class) {
            $class = trim($class);
            if (!empty($class) && !in_array($class, $remove_classes, true)) {
                $new_classes[] = $class;
            }
        }
    }

    return array_unique($new_classes);
}

/**
 * Enqueue registration page styles AFTER BuddyBoss theme CSS
 *
 * WHY THIS IS NEEDED:
 * BuddyBoss theme CSS (buddyboss-theme-css, buddyboss-theme-template-css)
 * loads AFTER child theme CSS due to how BuddyBoss enqueues its styles.
 * By setting the dependency to 'buddyboss-theme-template-css', we ensure
 * our registration CSS loads last in the cascade and wins specificity
 * battles without needing !important.
 *
 * Load order becomes:
 * 1. Child theme CSS
 * 2. BuddyBoss theme CSS
 * 3. unmask-registration CSS (LAST - wins cascade!)
 */
add_action('wp_enqueue_scripts', 'unmask_registration_styles', 999);
function unmask_registration_styles() {
    // Check if we're on registration or welcome page templates
    if (!unmask_is_registration_page()) {
        return;
    }

    // Dependency on BuddyBoss theme CSS ensures we load AFTER it
    // If buddyboss-theme-template-css doesn't exist, fall back to just design system
    $deps = array('unmask-00-design-system');
    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    wp_enqueue_style(
        'unmask-registration',
        get_stylesheet_directory_uri() . '/assets/css/unmask-registration.css',
        $deps,
        filemtime(get_stylesheet_directory() . '/assets/css/unmask-registration.css')
    );
}

/**
 * Dequeue unnecessary styles on registration/welcome pages
 * Keeps: Header, BuddyPanel sidebar, Footer essentials
 * Removes: Plugins, features not needed for registration
 */
add_action('wp_enqueue_scripts', 'unmask_dequeue_registration_styles', 999);
function unmask_dequeue_registration_styles() {
    // Only run on registration/welcome pages
    if (!unmask_is_registration_page()) {
        return;
    }

    // Styles to DEQUEUE (not needed for registration)
    $dequeue_styles = array(
        // BuddyBoss social features
        'bb-activity-post-feature-image',
        'bb-polls-style',
        'bb-schedule-posts',
        'bb-access-control',
        'bp-zoom',
        'bp-media-videojs-css',
        'bp-mentions-css',

        // MemberPress (we override these)
        'mp-theme',
        'bb-meprlms-frontend',

        // BuddyBoss MemberPress integration (conflicts with our form styles)
        'buddyboss-theme-memberpress',
        'member-profile-css',

        // Modern Events Calendar
        'mec-frontend-style',
        'mec-general-calendar-style',
        'mec-font-icons',
        'mec-lity-style',
        'mec-select2-style',
        'mec-single-builder',
        'mec-tooltip-style',
        'mec-tooltip-shadow-style',
        'mec-bp-main',

        // WooCommerce
        'woocommerce-general',
        'woocommerce-layout',
        'woocommerce-smallscreen',
        'wc-blocks-style',
        'wc-stripe-blocks-checkout-style',
        'buddyboss-theme-woocommerce',

        // Other plugins
        'factory-booking-frontend',
        'ssa-styles',
        'ssa-upcoming-appointments-card-style',
        'featherlight',
        'flatpickr',

        // Forums (not needed)
        'buddyboss-theme-forums',

        // TutorLMS
        'bb-tutorlms-admin',
    );

    foreach ($dequeue_styles as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }
}

// Membership module (signup fields, BuddyBoss sync, rank resolution, designation, onboarding,
// drone access init, BuddyPanel menu) → moved to hoa-core/includes/membership/ (WC-08)

