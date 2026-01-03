<?php
/**
 * UNMASK Child Theme Functions
 * 
 * All custom PHP snippets consolidated here
 */

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

    // BuddyBoss bridge - integrates components with BuddyBoss
    wp_enqueue_style(
        'unmask-buddyboss-bridge',
        $css_dir . 'unmask-buddyboss-bridge-v1.css',
        array('unmask-components'),
        $theme_version
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

// Factory page styles
require_once get_stylesheet_directory() . '/inc/enqueue-factory.php';

// Factory Booking page styles
require_once get_stylesheet_directory() . '/inc/enqueue-factory-book.php';

/* ==========================================================================
   REGISTRATION PAGE STYLES
   ========================================================================== */

/**
 * Helper function to check if current page uses registration/welcome templates
 */
function unmask_is_registration_page() {
    // Multiple detection methods for reliability

    // Method 1: Check page template slug
    $template_slug = get_page_template_slug();
    if ($template_slug === 'page-templates/page-register-visitor.php' ||
        $template_slug === 'page-templates/page-welcome.php') {
        return true;
    }

    // Method 2: Check is_page_template (backup)
    if (is_page_template('page-templates/page-register-visitor.php') ||
        is_page_template('page-templates/page-welcome.php')) {
        return true;
    }

    // Method 3: Check body class (most reliable at body_class filter time)
    // This works because WordPress adds template classes before our filter
    global $post;
    if ($post && is_page()) {
        $stored_template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($stored_template === 'page-templates/page-register-visitor.php' ||
            $stored_template === 'page-templates/page-welcome.php') {
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
        wp_get_theme()->get('Version')
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
}

/* ==========================================================================
   MEMBERPRESS TO BUDDYBOSS SYNC
   ========================================================================== */

/**
 * Sync MemberPress custom fields to BuddyBoss xprofile on registration
 * Runs after mepr-signup with priority 20 to ensure fields are saved first
 */
add_action('mepr-signup', 'unmask_sync_to_buddyboss', 20);
function unmask_sync_to_buddyboss($txn) {
    $user_id = $txn->user_id;

    // Get saved meta values
    $functions   = get_user_meta($user_id, 'unmask_function', true);
    $scene_name  = get_user_meta($user_id, 'scene_name', true);
    $pronouns    = get_user_meta($user_id, 'unmask_pronouns', true);

    // Sync to BuddyBoss xprofile fields
    // Note: Field names must match exactly what's configured in BuddyBoss
    if (!empty($functions) && function_exists('xprofile_set_field_data')) {
        // For multi-select, pass as array or comma-separated depending on field type
        xprofile_set_field_data('Function', $user_id, $functions);
    }

    if (!empty($scene_name) && function_exists('xprofile_set_field_data')) {
        xprofile_set_field_data('Scene Name', $user_id, $scene_name);
    }

    if (!empty($pronouns) && function_exists('xprofile_set_field_data')) {
        xprofile_set_field_data('Pronouns', $user_id, $pronouns);
    }
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

/* ==========================================================================
   CUSTOM SNIPPETS GO BELOW
   ========================================================================== */