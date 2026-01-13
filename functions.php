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

// A/B Testing - Cookie-based variant assignment (runs early for cookie handling)
require_once get_stylesheet_directory() . '/inc/ab-testing.php';

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

// Profile/Dossier page styles
require_once get_stylesheet_directory() . '/inc/enqueue-profile.php';

// ISO Board page styles and AJAX handlers
require_once get_stylesheet_directory() . '/inc/enqueue-iso-board.php';

// ISO Submit Form styles, scripts, and AJAX handler
require_once get_stylesheet_directory() . '/inc/enqueue-iso-form.php';

// Submit Hub page styles
require_once get_stylesheet_directory() . '/inc/enqueue-submit.php';

// Iso Post page styles
require_once get_stylesheet_directory() . '/inc/enqueue-iso-post.php';

// Homepage grid layout and cards
require_once get_stylesheet_directory() . '/inc/enqueue-homepage-grid.php';

// Dossier - Profile view (clean terminal aesthetic)
require_once get_stylesheet_directory() . '/inc/dossier/functions.php';
require_once get_stylesheet_directory() . '/inc/dossier/queries.php';
require_once get_stylesheet_directory() . '/inc/enqueue-dossier-view.php';

// Dossier v4 - Notebook system (per-field visibility)
require_once get_stylesheet_directory() . '/inc/dossier/notebook.php';
require_once get_stylesheet_directory() . '/inc/enqueue-notebook.php';

// Profile Edit - Split panel + bottom sheet interface
require_once get_stylesheet_directory() . '/inc/enqueue-profile-edit.php';

// Widgets
require_once get_stylesheet_directory() . '/inc/widgets/class-visitor-grid-widget.php';

// Mobile Bottom Navigation
require_once get_stylesheet_directory() . '/inc/enqueue-bottom-nav.php';

// Sidebar Submit CTA
require_once get_stylesheet_directory() . '/inc/sidebar-submit-cta.php';

// Collapsing Header (mobile)
require_once get_stylesheet_directory() . '/inc/collapsing-header.php';

// Performance Optimizations (dequeue unused assets)
require_once get_stylesheet_directory() . '/inc/performance-optimizations.php';

// Pink Panthers Submissions (CPT + form handlers)
require_once get_stylesheet_directory() . '/inc/pink-panthers-submissions.php';

// Newsletter System (card component + form handler)
require_once get_stylesheet_directory() . '/inc/newsletter-handler.php';
require_once get_stylesheet_directory() . '/inc/enqueue-newsletter.php';

// Onboarding System (4-screen flow, profile completion, hooks)
require_once get_stylesheet_directory() . '/inc/enqueue-onboarding.php';

// Analytics - GA4 behavioral tracking via GTM
require_once get_stylesheet_directory() . '/inc/enqueue-analytics.php';

// Analytics Dashboard - Terminal-style metrics cockpit
require_once get_stylesheet_directory() . '/inc/enqueue-dashboard-analytics.php';

// Analytics REST API - Endpoint for n8n to push GA4 data
require_once get_stylesheet_directory() . '/inc/analytics-data-endpoint.php';

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

/* ==========================================================================
   MEMBERPRESS CUSTOM FIELDS
   ========================================================================== */

/**
 * Add custom fields to MemberPress signup form
 * Fields: scene_name, pronouns (function is handled separately in template)
 */
add_action('mepr-checkout-before-submit', 'unmask_add_signup_fields');
function unmask_add_signup_fields($membership) {
    ?>
    <div class="form-group mp_form_row">
        <label class="form-label" for="scene_name">scene name</label>
        <input type="text"
               id="scene_name"
               name="scene_name"
               class="input mepr-form-input"
               placeholder="enter your scene name"
               value="<?php echo esc_attr($_POST['scene_name'] ?? ''); ?>">
        <p class="mepr_field_description" style="font-size: var(--type-xs); color: var(--text-muted); margin-top: 4px;">
            This will be your display name throughout the system.
        </p>
    </div>

    <div class="form-group mp_form_row">
        <label class="form-label" for="unmask_pronouns">pronouns</label>
        <select id="unmask_pronouns" name="unmask_pronouns" class="select mepr-form-input">
            <option value="">select...</option>
            <option value="they/them" <?php selected($_POST['unmask_pronouns'] ?? '', 'they/them'); ?>>they/them</option>
            <option value="he/him" <?php selected($_POST['unmask_pronouns'] ?? '', 'he/him'); ?>>he/him</option>
            <option value="she/her" <?php selected($_POST['unmask_pronouns'] ?? '', 'she/her'); ?>>she/her</option>
            <option value="he/they" <?php selected($_POST['unmask_pronouns'] ?? '', 'he/they'); ?>>he/they</option>
            <option value="she/they" <?php selected($_POST['unmask_pronouns'] ?? '', 'she/they'); ?>>she/they</option>
            <option value="ask" <?php selected($_POST['unmask_pronouns'] ?? '', 'ask'); ?>>ask me</option>
        </select>
    </div>

    <div class="form-group mp_form_row unmask-newsletter-opt-in">
        <label class="unmask-checkbox-label">
            <input type="checkbox"
                   name="newsletter_opt_in"
                   value="1"
                   class="unmask-checkbox"
                   <?php checked(!empty($_POST['newsletter_opt_in']) || !isset($_POST['newsletter_opt_in'])); ?>>
            <span class="unmask-checkbox-text">send me the weekly UNMASK newsletter</span>
        </label>
    </div>
    <?php
}

/**
 * Validate custom fields on signup
 */
add_filter('mepr-validate-signup', 'unmask_validate_signup_fields');
function unmask_validate_signup_fields($errors) {
    // Scene name is optional but if provided, sanitize it
    // Function selection is optional

    return $errors;
}

/**
 * Save custom fields after MemberPress signup
 */
add_action('mepr-signup', 'unmask_save_signup_fields');
function unmask_save_signup_fields($txn) {
    $user_id = $txn->user_id;

    // Save function(s) - from the custom checkbox grid
    if (isset($_POST['unmask_function']) && is_array($_POST['unmask_function'])) {
        $functions = array_map('sanitize_text_field', $_POST['unmask_function']);
        update_user_meta($user_id, 'unmask_function', $functions);
    }

    // Save scene name
    if (!empty($_POST['scene_name'])) {
        $scene_name = sanitize_text_field($_POST['scene_name']);
        update_user_meta($user_id, 'scene_name', $scene_name);

        // Also update display name and nickname
        wp_update_user(array(
            'ID'           => $user_id,
            'display_name' => $scene_name,
            'nickname'     => $scene_name
        ));
    }

    // Save pronouns
    if (!empty($_POST['unmask_pronouns'])) {
        $pronouns = sanitize_text_field($_POST['unmask_pronouns']);
        update_user_meta($user_id, 'unmask_pronouns', $pronouns);
    }

    // Handle newsletter opt-in
    if (!empty($_POST['newsletter_opt_in'])) {
        update_user_meta($user_id, 'newsletter_opt_in', '1');

        // Add to Mailchimp via MC4WP if available
        $user = get_userdata($user_id);
        if ($user && function_exists('mc4wp_get_api_v3')) {
            try {
                $api = mc4wp_get_api_v3();
                $lists = mc4wp_get_option('general', 'lists', array());
                $list_id = !empty($lists) ? $lists[0] : '';

                if ($list_id) {
                    $merge_fields = array();
                    if (!empty($user->first_name)) {
                        $merge_fields['FNAME'] = $user->first_name;
                    }
                    if (!empty($scene_name)) {
                        $merge_fields['FNAME'] = $scene_name;
                    }

                    $api->add_list_member($list_id, array(
                        'email_address' => $user->user_email,
                        'status' => 'subscribed',
                        'merge_fields' => $merge_fields,
                        'tags' => array('registration', 'member')
                    ));
                }
            } catch (Exception $e) {
                error_log('UNMASK Registration Newsletter Error: ' . $e->getMessage());
            }
        }
    }
}

/* ==========================================================================
   MEMBERPRESS TO BUDDYBOSS SYNC

   XProfile field names (from qvq_bp_xprofile_fields):
   - id 1:  "Name" (textbox)
   - id 2:  "Last Name" (textbox)
   - id 3:  "Scene Name" (textbox)
   - id 84: "ID" (number) - auto-generated designation
   - id 85: "Pronouns" (textbox)

   See: docs/session-log-onboarding-debug.md for full field list
   ========================================================================== */

/**
 * Sync MemberPress registration data to BuddyBoss xprofile fields
 * Runs after mepr-signup with priority 20 to ensure WP user data is saved first
 */
add_action('mepr-signup', 'unmask_sync_to_buddyboss', 20);
function unmask_sync_to_buddyboss($txn) {
    $user_id = $txn->user_id;

    // Bail if xprofile functions not available
    if (!function_exists('xprofile_set_field_data')) {
        error_log('UNMASK Sync: xprofile_set_field_data not available for user ' . $user_id);
        return;
    }

    // Get WordPress user data
    $user = get_userdata($user_id);
    if (!$user) {
        error_log('UNMASK Sync: Could not get user data for user ' . $user_id);
        return;
    }

    // Get custom meta values saved during registration
    $scene_name = get_user_meta($user_id, 'scene_name', true);
    $pronouns   = get_user_meta($user_id, 'unmask_pronouns', true);

    // 1. Sync first name to xprofile "Name" (field id: 1)
    if (!empty($user->first_name)) {
        xprofile_set_field_data('Name', $user_id, $user->first_name);
        error_log('UNMASK Sync: Set Name = ' . $user->first_name . ' for user ' . $user_id);
    }

    // 2. Sync last name to xprofile "Last Name" (field id: 2)
    if (!empty($user->last_name)) {
        xprofile_set_field_data('Last Name', $user_id, $user->last_name);
        error_log('UNMASK Sync: Set Last Name = ' . $user->last_name . ' for user ' . $user_id);
    }

    // 3. Sync scene name to xprofile "Scene Name" (field id: 3)
    if (!empty($scene_name)) {
        xprofile_set_field_data('Scene Name', $user_id, $scene_name);
        error_log('UNMASK Sync: Set Scene Name = ' . $scene_name . ' for user ' . $user_id);
    }

    // 4. Sync pronouns to xprofile "Pronouns" (field id: 85)
    if (!empty($pronouns)) {
        xprofile_set_field_data('Pronouns', $user_id, $pronouns);
        error_log('UNMASK Sync: Set Pronouns = ' . $pronouns . ' for user ' . $user_id);
    }

    // 5. Auto-generate and sync designation to xprofile "ID" (field id: 84)
    // Format: just the number (e.g., "047") - the V-/D- prefix is added in display
    $designation_num = str_pad($user_id, 3, '0', STR_PAD_LEFT);
    xprofile_set_field_data('ID', $user_id, $designation_num);
    // Also store in user_meta for unmask_get_designation() function
    update_user_meta($user_id, 'unmask_designation_number', $designation_num);
    error_log('UNMASK Sync: Set ID = ' . $designation_num . ' for user ' . $user_id);

    error_log('UNMASK Sync: Completed sync for user ' . $user_id);
}

/**
 * Make the "ID" xprofile field read-only
 * Users should not be able to change their designation number
 * Field ID 84 = "ID" (the designation number like 047)
 */
add_filter('bp_xprofile_is_field_edit_allowed', 'unmask_make_id_field_readonly', 10, 2);
function unmask_make_id_field_readonly($allow, $field) {
    // Field ID 84 is the "ID" field (designation number)
    if (isset($field->id) && (int) $field->id === 84) {
        return false;
    }
    return $allow;
}

/**
 * Also add CSS to visually disable the ID field in case filter doesn't fully hide it
 */
add_action('bp_before_profile_edit_content', 'unmask_id_field_readonly_css');
function unmask_id_field_readonly_css() {
    ?>
    <style>
        /* Make ID field appear read-only */
        #field_84,
        .editfield.field_84 input,
        .editfield.field_84 textarea {
            pointer-events: none;
            opacity: 0.6;
            background: var(--bg-inset, #0a0a0a);
            cursor: not-allowed;
        }
        .editfield.field_84::after {
            content: "auto-assigned";
            font-size: 10px;
            color: var(--text-muted, #666);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-left: 8px;
        }
    </style>
    <?php
}

/* ==========================================================================
   REGISTRATION REDIRECT
   ========================================================================== */

/**
 * Redirect to welcome page after successful MemberPress registration
 */
add_filter('mepr-signup-redirect-url', 'unmask_registration_redirect', 10, 2);
function unmask_registration_redirect($url, $txn) {
    return home_url('/welcome/');
}

/**
 * Fix Sign Up button URL in BuddyBoss header
 * Points to MemberPress registration page instead of default
 */
add_filter('wp_registration_url', 'unmask_custom_registration_url');
function unmask_custom_registration_url($url) {
    return home_url('/register-visitor/');
}

/**
 * Fix BuddyBoss header Sign Up button via JavaScript (fallback)
 * BuddyBoss doesn't always respect wp_registration_url filter
 */
add_action('wp_footer', 'unmask_fix_signup_button_url');
function unmask_fix_signup_button_url() {
    if (is_user_logged_in()) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var signupBtn = document.querySelector('.bb-header-buttons .button.signup, .bb-header-buttons a.signup');
        if (signupBtn) {
            signupBtn.href = '<?php echo esc_url(home_url('/register-visitor/')); ?>';
        }
    });
    </script>
    <?php
}

/* ==========================================================================
   DOSSIER/PROFILE HELPER FUNCTIONS
   ========================================================================== */

/**
 * Get user's member type (Drone or Visitor) from MemberPress membership
 *
 * @param int $user_id User ID (defaults to displayed user)
 * @return string 'drone' or 'visitor'
 */
function unmask_get_member_type($user_id = null) {
    if (!$user_id) {
        $user_id = function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id();
    }

    if (!$user_id) {
        return 'visitor';
    }

    // Check if user has active MemberPress subscription
    if (class_exists('MeprUser') && class_exists('MeprProduct')) {
        try {
            $mepr_user = new MeprUser($user_id);
            $active_memberships = $mepr_user->active_product_subscriptions();

            if (!empty($active_memberships)) {
                foreach ($active_memberships as $product_id) {
                    $product = new MeprProduct($product_id);
                    $title = strtolower($product->post_title);

                    if (strpos($title, 'drone') !== false) {
                        return 'drone';
                    }
                }
            }
        } catch (Exception $e) {
            // MemberPress not fully loaded, fall through to visitor
        }
    }

    return 'visitor';
}

/**
 * Get user's designation (D-001, V-047 format)
 *
 * @param int $user_id User ID (defaults to displayed user)
 * @return string Formatted designation
 */
function unmask_get_designation($user_id = null) {
    if (!$user_id) {
        $user_id = function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id();
    }

    if (!$user_id) {
        return 'V-000';
    }

    $member_type = unmask_get_member_type($user_id);
    $prefix = ($member_type === 'drone') ? 'D' : 'V';

    // Get the designation number from user meta or generate from ID
    $designation_num = get_user_meta($user_id, 'unmask_designation_number', true);

    if (!$designation_num) {
        // Generate designation number based on user ID for consistency
        // This ensures the same user always gets the same number
        $designation_num = str_pad($user_id, 3, '0', STR_PAD_LEFT);
        update_user_meta($user_id, 'unmask_designation_number', $designation_num);
    }

    return $prefix . '-' . $designation_num;
}

/**
 * Alias for unmask_get_designation() for backwards compatibility
 * Some templates reference unmask_get_user_designation()
 *
 * @param int $user_id User ID (defaults to displayed user)
 * @return string Formatted designation
 */
if (!function_exists('unmask_get_user_designation')) {
    function unmask_get_user_designation($user_id = null) {
        return unmask_get_designation($user_id);
    }
}

/**
 * Get designation with visibility-based color class
 *
 * Returns the appropriate CSS class for coloring designations based on
 * the viewer's relationship to the displayed user:
 * - green: Viewer is connected (interlinked/friends)
 * - yellow: Viewer is logged in but not connected
 * - red: Viewer is public (logged out)
 * - gray: Profile is hidden from viewer
 *
 * @param int $displayed_user_id User being viewed
 * @param int $viewer_id         Current viewer (0 for logged-out)
 * @return string CSS class: 'designation--green', 'designation--yellow', 'designation--red', or 'designation--gray'
 */
function unmask_get_designation_color_class($displayed_user_id, $viewer_id = null) {
    if ($viewer_id === null) {
        $viewer_id = get_current_user_id();
    }

    // Viewer is same as displayed user
    if ($viewer_id > 0 && $displayed_user_id === $viewer_id) {
        return 'designation--green';
    }

    // Viewer is logged out
    if ($viewer_id <= 0) {
        return 'designation--red';
    }

    // Check if connected (friends)
    if (function_exists('friends_check_friendship') && friends_check_friendship($displayed_user_id, $viewer_id)) {
        return 'designation--green';
    }

    // Logged in but not connected
    return 'designation--yellow';
}

/**
 * Get user's bio from xprofile
 *
 * @param int $user_id User ID (defaults to displayed user)
 * @return string Bio text
 */
function unmask_get_user_bio($user_id = null) {
    if (!$user_id) {
        $user_id = function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id();
    }

    if (!$user_id) {
        return '';
    }

    if (function_exists('xprofile_get_field_data')) {
        $bio = xprofile_get_field_data('Bio', $user_id);
        if ($bio) {
            return $bio;
        }
    }

    // Fallback to user description
    $user = get_userdata($user_id);
    return $user ? $user->description : '';
}

/**
 * Get user's availability flags from xprofile
 *
 * @param int $user_id User ID (defaults to displayed user)
 * @return array Availability flags with status
 */
function unmask_get_availability_flags($user_id = null) {
    if (!$user_id) {
        $user_id = function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id();
    }

    $flags = array();

    if (!$user_id) {
        return $flags;
    }

    if (function_exists('xprofile_get_field_data')) {
        // Check each availability field
        $available_photographed = xprofile_get_field_data('Available to be photographed', $user_id);
        $available_photograph = xprofile_get_field_data('Available to photograph', $user_id);
        $seeking_collaborators = xprofile_get_field_data('Seeking collaborators', $user_id);
        $open_bookings = xprofile_get_field_data('Open to bookings', $user_id);

        if ($available_photographed && strtolower($available_photographed) === 'yes') {
            $flags[] = array('label' => 'available to be photographed', 'active' => true);
        }
        if ($available_photograph && strtolower($available_photograph) === 'yes') {
            $flags[] = array('label' => 'available to photograph', 'active' => true);
        }
        if ($seeking_collaborators && strtolower($seeking_collaborators) === 'yes') {
            $flags[] = array('label' => 'seeking collaborators', 'active' => true);
        }
        if ($open_bookings && strtolower($open_bookings) === 'yes') {
            $flags[] = array('label' => 'open to bookings', 'active' => true);
        }
    }

    return $flags;
}

/**
 * Get user's pronouns from xprofile
 *
 * @param int $user_id User ID (defaults to displayed user)
 * @return string Pronouns
 */
function unmask_get_user_pronouns($user_id = null) {
    if (!$user_id) {
        $user_id = function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id();
    }

    if (!$user_id) {
        return '';
    }

    if (function_exists('xprofile_get_field_data')) {
        $pronouns = xprofile_get_field_data('Pronouns', $user_id);
        if ($pronouns) {
            return $pronouns;
        }
    }

    return get_user_meta($user_id, 'unmask_pronouns', true) ?: '';
}

/**
 * Get user's location from xprofile
 *
 * @param int $user_id User ID (defaults to displayed user)
 * @return string Location
 */
function unmask_get_user_location($user_id = null) {
    if (!$user_id) {
        $user_id = function_exists('bp_displayed_user_id') ? bp_displayed_user_id() : get_current_user_id();
    }

    if (!$user_id) {
        return '';
    }

    if (function_exists('xprofile_get_field_data')) {
        $city = xprofile_get_field_data('City', $user_id);
        if ($city) {
            return $city;
        }
    }

    return '';
}

/* ==========================================================================
   DOSSIER HEADER MODIFICATIONS — Phase 2
   ========================================================================== */

/**
 * Get user's designation from anomie_handle meta
 *
 * @param int $user_id User ID
 * @return string Designation (e.g., "V-002") or empty string
 */
function unmask_get_anomie_handle( $user_id = null ) {
    if ( ! $user_id ) {
        $user_id = function_exists( 'bp_displayed_user_id' ) ? bp_displayed_user_id() : get_current_user_id();
    }
    if ( ! $user_id ) {
        return '';
    }
    return get_user_meta( $user_id, 'anomie_handle', true );
}

/**
 * Replace member type badge with designation
 */
add_filter( 'bp_member_type_name_string', 'unmask_designation_badge', 10, 3 );
function unmask_designation_badge( $string, $member_type, $user_id ) {
    if ( empty( $user_id ) ) {
        $user_id = function_exists( 'bp_displayed_user_id' ) ? bp_displayed_user_id() : 0;
    }
    if ( ! $user_id ) {
        return $string;
    }

    $designation = unmask_get_anomie_handle( $user_id );
    if ( empty( $designation ) ) {
        return $string;
    }

    return '<span class="unmask-badge">' . esc_html( $designation ) . '</span>';
}

/**
 * Add bio and availability flags after member header meta
 * Uses bp_before_member_header_meta action hook
 */
// DISABLED - profile customization
// add_action('bp_before_member_header_meta', 'unmask_add_header_bio_availability');
function unmask_add_header_bio_availability() {
    // Bail if not on a member page
    if (!function_exists('bp_is_user') || !bp_is_user()) {
        return;
    }

    $user_id = bp_displayed_user_id();
    if (!$user_id) {
        return;
    }

    // Get data from xprofile
    $bio = '';
    $pronouns = '';
    $location = '';
    $availability_flags = array();

    if (function_exists('xprofile_get_field_data')) {
        $bio = xprofile_get_field_data('Bio', $user_id);
        $pronouns = xprofile_get_field_data('Pronouns', $user_id);
        $location = xprofile_get_field_data('City', $user_id);

        // Note: Availability flags (Available to be photographed, etc.) are temporarily
        // disabled as the field names need to be verified in BuddyBoss settings.
        // TODO: Verify exact field names and re-enable availability flags
    }

    // Output pronouns and location
    if ($pronouns || $location) {
        echo '<div class="unmask-meta-extra">';
        if ($pronouns) {
            echo '<span class="unmask-pronouns-header">' . esc_html(strtolower($pronouns)) . '</span>';
        }
        if ($pronouns && $location) {
            echo '<span class="separator"> &bull; </span>';
        }
        if ($location) {
            echo '<span class="unmask-location-header">' . esc_html($location) . '</span>';
        }
        echo '</div>';
    }

    // Output bio
    if ($bio) {
        echo '<div class="unmask-bio">' . esc_html($bio) . '</div>';
    }

    // Output availability flags
    if (!empty($availability_flags)) {
        echo '<div class="unmask-availability-flags">';
        foreach ($availability_flags as $flag) {
            $flag_class = 'unmask-availability-flag unmask-availability-flag--' . ($flag['active'] ? 'active' : 'inactive');
            echo '<span class="' . esc_attr($flag_class) . '">' . esc_html($flag['label']) . '</span>';
        }
        echo '</div>';
    }
}

/* ==========================================================================
   PINK PANTHERS
   ========================================================================== */

/**
 * Pink Panthers - Check if on Pink Panthers page
 */
function unmask_is_pink_panthers_page() {
    if (is_page_template('page-templates/page-pink-panthers.php')) {
        return true;
    }
    if (is_page('pink-panthers')) {
        return true;
    }
    return false;
}

/**
 * Pink Panthers - Enqueue Styles
 */
function unmask_pink_panthers_styles() {
    if (unmask_is_pink_panthers_page()) {
        wp_enqueue_style(
            'pink-panthers-styles',
            get_stylesheet_directory_uri() . '/assets/css/pink-panthers.css',
            array(),
            filemtime(get_stylesheet_directory() . '/assets/css/pink-panthers.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'unmask_pink_panthers_styles', 20);

/**
 * Pink Panthers - Hide default page title (we have custom hero)
 */
function unmask_pink_panthers_hide_title() {
    if (!unmask_is_pink_panthers_page()) {
        return;
    }
    ?>
    <style id="pink-panthers-title-hide">
        .page-template-page-pink-panthers .entry-header { display: none; }
    </style>
    <?php
}
add_action('wp_head', 'unmask_pink_panthers_hide_title', 20);

/**
 * Pink Panthers - Handle Performer Form Submission
 */
function unmask_handle_performer_submission() {
    if (!isset($_POST['pp_nonce']) || !wp_verify_nonce($_POST['pp_nonce'], 'pp_performer_submit')) {
        return;
    }

    $performer_data = array(
        'name' => sanitize_text_field($_POST['performer_name']),
        'act_type' => sanitize_text_field($_POST['act_type']),
        'link' => sanitize_url($_POST['performer_link']),
        'description' => sanitize_textarea_field($_POST['act_description']),
        'submitted' => current_time('mysql'),
        'status' => 'pending'
    );

    // Email notification to admin
    $to = get_option('admin_email');
    $subject = 'Pink Panthers: New Performer Submission - ' . $performer_data['name'];
    $message = "New performer submission:\n\n";
    $message .= "Name: {$performer_data['name']}\n";
    $message .= "Act Type: {$performer_data['act_type']}\n";
    $message .= "Link: {$performer_data['link']}\n";
    $message .= "Description: {$performer_data['description']}\n";

    wp_mail($to, $subject, $message);

    // Redirect with success message
    wp_redirect(add_query_arg('submitted', '1', wp_get_referer()));
    exit;
}
add_action('admin_post_nopriv_pp_performer_submit', 'unmask_handle_performer_submission');
add_action('admin_post_pp_performer_submit', 'unmask_handle_performer_submission');

/**
 * Pink Panthers - Admin Activity Post Handler
 */
function unmask_pp_post_update() {
    if (!current_user_can('administrator')) {
        wp_die('Unauthorized');
    }

    if (!isset($_POST['pp_update_content']) || empty($_POST['pp_update_content'])) {
        return;
    }

    $content = sanitize_textarea_field($_POST['pp_update_content']);

    // Post to BuddyBoss activity
    if (function_exists('bp_activity_add')) {
        bp_activity_add(array(
            'user_id' => get_current_user_id(),
            'content' => $content,
            'component' => 'activity',
            'type' => 'activity_update',
            'primary_link' => home_url('/pink-panthers/')
        ));
    }

    wp_redirect(home_url('/pink-panthers/'));
    exit;
}
add_action('admin_post_pp_post_update', 'unmask_pp_post_update');

/* ==========================================================================
   LOGIN PAGE CUSTOMIZATION
   ========================================================================== */

/**
 * Enqueue custom login page styles
 * Uses UNMASK design tokens for consistent branding
 *
 * Priority 999 ensures we load AFTER BuddyBoss login styles
 */
add_action('login_enqueue_scripts', 'unmask_login_styles', 999);
function unmask_login_styles() {
    $theme_version = wp_get_theme()->get('Version');
    $css_dir = get_stylesheet_directory_uri() . '/assets/css/';

    // Dependencies - load after BuddyBoss login if present
    $deps = array();
    if (wp_style_is('buddyboss-theme-login', 'registered') || wp_style_is('buddyboss-theme-login', 'enqueued')) {
        $deps[] = 'buddyboss-theme-login';
    }

    // Enqueue design system tokens first (for CSS variables)
    wp_enqueue_style(
        'unmask-design-system-login',
        $css_dir . '00-design-system.css',
        $deps,
        $theme_version
    );

    // Enqueue login page styles
    wp_enqueue_style(
        'unmask-login',
        $css_dir . 'pages/login.css',
        array('unmask-design-system-login'),
        $theme_version
    );
}

/**
 * Change login page logo URL to site home
 */
add_filter('login_headerurl', 'unmask_login_logo_url');
function unmask_login_logo_url() {
    return home_url();
}

/**
 * Change login page logo title to site name
 */
add_filter('login_headertext', 'unmask_login_logo_title');
function unmask_login_logo_title() {
    return get_bloginfo('name');
}

/**
 * Add inline styles to override any plugin inline styles
 * This runs in the <head> after all stylesheets are loaded
 */
add_action('login_head', 'unmask_login_inline_styles', 999);
function unmask_login_inline_styles() {
    ?>
    <style id="unmask-login-override">
        /* Force consistent font weight and family on ALL elements */
        body.login,
        body.login *,
        body.login *::before,
        body.login *::after {
            font-family: "Berkeley mono", "Berkeley Mono", "SF Mono", monospace !important;
            font-weight: 400 !important;
            font-style: normal !important;
        }

        /* Override any inline background styles */
        body.login {
            background: #181818 !important;
        }

        /* Remove form box styling */
        body.login form,
        body.login #loginform,
        body.login #registerform {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        /* Make login container wider */
        body.login #login {
            width: 100% !important;
            max-width: 400px !important;
        }

        /* Full-width inputs */
        body.login input[type="text"],
        body.login input[type="password"],
        body.login input[type="email"] {
            width: 100% !important;
            font-size: 16px !important;
            padding: 12px 16px !important;
            background: #181818 !important;
            border: 1px solid #2e2e2e !important;
            border-radius: 0 !important;
            color: #c2c2c2 !important;
            min-height: 48px !important;
            box-sizing: border-box !important;
        }

        /* Full-width button */
        body.login .button-primary,
        body.login #wp-submit {
            width: 100% !important;
            font-size: 12px !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
            padding: 14px 24px !important;
            background: #6b2627 !important;
            border: 1px solid #6b2627 !important;
            border-radius: 0 !important;
            color: #c2c2c2 !important;
            min-height: 48px !important;
            text-shadow: none !important;
            box-shadow: none !important;
        }

        body.login .button-primary:hover,
        body.login #wp-submit:hover {
            background: #a13d3e !important;
            border-color: #a13d3e !important;
        }

        /* Consistent label styling */
        body.login label {
            font-size: 12px !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
            color: #909090 !important;
        }

        /* Links */
        body.login a {
            font-size: 12px !important;
            color: #6b2627 !important;
        }

        body.login a:hover {
            color: #a13d3e !important;
        }
    </style>
    <?php
}

/* ==========================================================================
   ACF HELPER FUNCTIONS
   ========================================================================== */

/**
 * Get ACF field value with fallback
 * Works whether ACF is installed or not
 *
 * @param string $field_name ACF field name
 * @param mixed  $fallback   Fallback value if field is empty or ACF not installed
 * @param mixed  $post_id    Post ID, 'option' for options page, or null for current post
 * @return mixed Field value or fallback
 */
function unmask_get_field($field_name, $fallback = '', $post_id = null) {
    // If ACF isn't installed, return fallback
    if (!function_exists('get_field')) {
        return $fallback;
    }

    // Get the field value
    $value = get_field($field_name, $post_id);

    // Return fallback if empty
    if (empty($value) && $value !== 0 && $value !== '0') {
        return $fallback;
    }

    return $value;
}

/**
 * Echo ACF field value with fallback (escaped)
 *
 * @param string $field_name ACF field name
 * @param mixed  $fallback   Fallback value
 * @param mixed  $post_id    Post ID or 'option'
 */
function unmask_the_field($field_name, $fallback = '', $post_id = null) {
    echo esc_html(unmask_get_field($field_name, $fallback, $post_id));
}

/**
 * Get ACF image field with fallback
 * Returns image URL from ACF image field (handles array or URL return format)
 *
 * @param string $field_name ACF field name
 * @param string $fallback   Fallback image URL
 * @param string $size       Image size (thumbnail, medium, large, full)
 * @param mixed  $post_id    Post ID or 'option'
 * @return string Image URL
 */
function unmask_get_image_field($field_name, $fallback = '', $size = 'full', $post_id = null) {
    if (!function_exists('get_field')) {
        return $fallback;
    }

    $image = get_field($field_name, $post_id);

    if (empty($image)) {
        return $fallback;
    }

    // Handle array return format
    if (is_array($image)) {
        if (isset($image['sizes'][$size])) {
            return $image['sizes'][$size];
        }
        return isset($image['url']) ? $image['url'] : $fallback;
    }

    // Handle URL return format
    if (is_string($image)) {
        return $image;
    }

    // Handle ID return format
    if (is_numeric($image)) {
        $url = wp_get_attachment_image_url($image, $size);
        return $url ? $url : $fallback;
    }

    return $fallback;
}

/* ==========================================================================
   GUIDED PROFILE SETUP AJAX HANDLERS
   ========================================================================== */

/**
 * AJAX handler: Save profile data from guided setup flow
 * Saves Scene Name, Pronouns, Practice, and Availability to BuddyBoss xprofile
 */
add_action('wp_ajax_unmask_save_profile_setup', 'unmask_save_profile_setup_ajax');
function unmask_save_profile_setup_ajax() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unmask_profile_setup')) {
        wp_send_json_error('Invalid security token');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    $user_id = get_current_user_id();

    // Check if xprofile functions exist
    if (!function_exists('xprofile_set_field_data')) {
        wp_send_json_error('BuddyBoss not active');
        return;
    }

    // Sanitize inputs
    $scene_name = isset($_POST['scene_name']) ? sanitize_text_field($_POST['scene_name']) : '';
    $pronouns = isset($_POST['pronouns']) ? sanitize_text_field($_POST['pronouns']) : '';
    $practice = isset($_POST['practice']) ? json_decode(stripslashes($_POST['practice']), true) : array();
    $available_photographed = isset($_POST['available_photographed']) && $_POST['available_photographed'] === '1';
    $available_photograph = isset($_POST['available_photograph']) && $_POST['available_photograph'] === '1';
    $open_bookings = isset($_POST['open_bookings']) && $_POST['open_bookings'] === '1';

    // Save to xprofile fields
    $errors = array();

    // Scene Name (field 3) - required
    if (!empty($scene_name)) {
        $result = xprofile_set_field_data('Scene Name', $user_id, $scene_name);
        if (!$result) {
            $errors[] = 'Failed to save scene name';
        }
    } else {
        wp_send_json_error('Scene name is required');
        return;
    }

    // Pronouns (field 85) - optional
    if (!empty($pronouns)) {
        xprofile_set_field_data('Pronouns', $user_id, $pronouns);
    }

    // Practice (field 21) - multiselect, expects array or serialized
    if (!empty($practice) && is_array($practice)) {
        xprofile_set_field_data('What do you practice?', $user_id, $practice);
    }

    // Available to be photographed (field 104) - checkbox
    xprofile_set_field_data('Available to be photographed', $user_id, $available_photographed ? 'Yes' : '');

    // Available to photograph (field 108) - checkbox
    xprofile_set_field_data('Available to photograph', $user_id, $available_photograph ? 'Yes' : '');

    // Open to bookings (field 111) - checkbox
    xprofile_set_field_data('Open to bookings', $user_id, $open_bookings ? 'Yes' : '');

    // Mark onboarding as complete
    update_user_meta($user_id, 'unmask_onboarding_complete', 1);
    update_user_meta($user_id, 'unmask_onboarding_step', 'complete');

    // Log for debugging
    error_log('UNMASK Profile Setup: Saved for user ' . $user_id . ' - Scene Name: ' . $scene_name);

    wp_send_json_success(array(
        'message' => 'Profile saved successfully',
        'redirect' => home_url('/'),
    ));
}

/**
 * AJAX handler: Mark onboarding as complete (for skip action)
 */
add_action('wp_ajax_unmask_complete_onboarding', 'unmask_complete_onboarding_ajax');
function unmask_complete_onboarding_ajax() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'unmask_profile_setup')) {
        wp_send_json_error('Invalid security token');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    $user_id = get_current_user_id();
    $skipped = isset($_POST['skipped']) && $_POST['skipped'] === '1';

    // Mark onboarding as complete (or skipped)
    update_user_meta($user_id, 'unmask_onboarding_complete', 1);
    update_user_meta($user_id, 'unmask_onboarding_step', $skipped ? 'skipped' : 'complete');

    error_log('UNMASK Onboarding: ' . ($skipped ? 'Skipped' : 'Completed') . ' for user ' . $user_id);

    wp_send_json_success(array(
        'message' => 'Onboarding ' . ($skipped ? 'skipped' : 'completed'),
        'redirect' => home_url('/'),
    ));
}

/**
 * Get practice options for profile setup
 * Returns array of options from the xprofile multiselectbox field
 */
function unmask_get_practice_options() {
    $options = array();

    if (!function_exists('xprofile_get_field')) {
        // Fallback options if BuddyBoss not available
        return array(
            'Photography',
            'Modeling',
            'Styling',
            'Makeup',
            'Hair',
            'Creative Direction',
            'Set Design',
            'Casting',
            'Production',
        );
    }

    // Get field 21 (What do you practice?)
    $field = xprofile_get_field(21);
    if ($field && !empty($field->type_obj) && method_exists($field->type_obj, 'get_children')) {
        $children = $field->get_children();
        foreach ($children as $child) {
            $options[] = $child->name;
        }
    }

    // Fallback if no options found
    if (empty($options)) {
        $options = array(
            'Photography',
            'Modeling',
            'Styling',
            'Makeup',
            'Hair',
            'Creative Direction',
            'Set Design',
            'Casting',
            'Production',
        );
    }

    return $options;
}

/* ==========================================================================
   CUSTOM SNIPPETS GO BELOW
   ========================================================================== */

/**
 * Temporarily redirect Members directory until BuddyBoss search fields are configured
 * Remove this once directory is set up in WP Admin → BuddyBoss → Settings → Profiles
 */
add_action('template_redirect', function() {
    if (function_exists('bp_is_members_directory') && bp_is_members_directory()) {
        wp_redirect(home_url('/'), 302);
        exit;
    }
});
