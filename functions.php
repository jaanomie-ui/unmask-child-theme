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

/* ==========================================================================
   REGISTRATION PAGE STYLES
   ========================================================================== */

/**
 * Enqueue registration page styles
 */
add_action('wp_enqueue_scripts', 'unmask_registration_styles');
function unmask_registration_styles() {
    // Check if we're on registration or welcome page templates
    if (is_page_template('page-templates/page-register-visitor.php') ||
        is_page_template('page-templates/page-welcome.php')) {

        wp_enqueue_style(
            'unmask-registration',
            get_stylesheet_directory_uri() . '/assets/css/unmask-registration.css',
            array('unmask-00-design-system'),
            wp_get_theme()->get('Version')
        );
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