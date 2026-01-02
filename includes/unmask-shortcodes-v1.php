<?php
/**
 * UNMASK Shortcodes v1
 *
 * Component shortcodes for use in WordPress content.
 * Each shortcode calls a corresponding template part.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================================================
   HELPER FUNCTIONS
   ========================================================================== */

/**
 * Get user designation (D-XXX for Disruptors, V-XXX for Visionaries)
 *
 * @param int $user_id WordPress user ID
 * @return string Formatted designation or empty string
 */
function unmask_get_user_designation($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return '';
    }

    // Check membership level (MemberPress integration)
    $membership_type = get_user_meta($user_id, 'mepr_membership_type', true);

    // Default prefix based on membership
    $prefix = 'V'; // Visionary default
    if ($membership_type === 'disruptor' || $membership_type === 'Disruptor') {
        $prefix = 'D';
    }

    // Format: D-001 or V-001 (padded to 3 digits)
    return sprintf('%s-%03d', $prefix, $user_id);
}

/**
 * Parse shortcode boolean attributes
 *
 * @param mixed $value Attribute value
 * @return bool
 */
function unmask_parse_bool($value) {
    if (is_bool($value)) {
        return $value;
    }
    return in_array(strtolower($value), array('true', '1', 'yes', 'on'), true);
}

/* ==========================================================================
   SHORTCODE: [unmask_button]
   ========================================================================== */

/**
 * Button shortcode
 *
 * Usage: [unmask_button text="Click me" url="#" variant="primary" size="md"]
 *
 * @param array $atts Shortcode attributes
 * @return string Button HTML
 */
function unmask_button_shortcode($atts) {
    $atts = shortcode_atts(array(
        'text'     => 'Button',
        'url'      => '#',
        'variant'  => 'primary',  // primary, secondary, ghost, danger
        'size'     => 'md',       // sm, md, lg
        'icon'     => '',         // Icon class or name
        'disabled' => 'false',
        'class'    => '',
        'target'   => '',
    ), $atts, 'unmask_button');

    $atts['disabled'] = unmask_parse_bool($atts['disabled']);

    ob_start();
    get_template_part('template-parts/components/unmask-button', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_button', 'unmask_button_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_badge]
   ========================================================================== */

/**
 * Badge shortcode
 *
 * Usage: [unmask_badge text="New" variant="success" size="sm"]
 *
 * @param array $atts Shortcode attributes
 * @return string Badge HTML
 */
function unmask_badge_shortcode($atts) {
    $atts = shortcode_atts(array(
        'text'    => '',
        'variant' => 'default',  // default, success, warning, error, info
        'size'    => 'md',       // sm, md, lg
        'icon'    => '',
        'class'   => '',
    ), $atts, 'unmask_badge');

    ob_start();
    get_template_part('template-parts/components/unmask-badge', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_badge', 'unmask_badge_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_avatar]
   ========================================================================== */

/**
 * Avatar shortcode
 *
 * Usage: [unmask_avatar user_id="1" size="md" show_status="true"]
 *
 * @param array $atts Shortcode attributes
 * @return string Avatar HTML
 */
function unmask_avatar_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id'     => get_current_user_id(),
        'size'        => 'md',      // xs, sm, md, lg, xl
        'show_status' => 'false',
        'status'      => 'offline', // online, offline, away, busy
        'linked'      => 'true',
        'class'       => '',
    ), $atts, 'unmask_avatar');

    $atts['show_status'] = unmask_parse_bool($atts['show_status']);
    $atts['linked'] = unmask_parse_bool($atts['linked']);
    $atts['user_id'] = intval($atts['user_id']);

    ob_start();
    get_template_part('template-parts/components/unmask-avatar', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_avatar', 'unmask_avatar_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_card]
   ========================================================================== */

/**
 * Card (fullbleed) shortcode
 *
 * Usage: [unmask_card title="Card Title" image="url" url="#"]Content here[/unmask_card]
 *
 * @param array $atts Shortcode attributes
 * @param string $content Enclosed content
 * @return string Card HTML
 */
function unmask_card_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'title'    => '',
        'subtitle' => '',
        'image'    => '',
        'url'      => '',
        'variant'  => 'default',  // default, elevated, outline
        'class'    => '',
    ), $atts, 'unmask_card');

    $atts['content'] = do_shortcode($content);

    ob_start();
    get_template_part('template-parts/components/unmask-card-fullbleed', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_card', 'unmask_card_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_iso_card]
   ========================================================================== */

/**
 * ISO Card shortcode (member card with designation)
 *
 * Usage: [unmask_iso_card user_id="1" show_designation="true"]
 *
 * @param array $atts Shortcode attributes
 * @return string ISO Card HTML
 */
function unmask_iso_card_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id'          => get_current_user_id(),
        'show_designation' => 'true',
        'show_avatar'      => 'true',
        'show_name'        => 'true',
        'variant'          => 'default',  // default, compact, expanded
        'class'            => '',
    ), $atts, 'unmask_iso_card');

    $atts['show_designation'] = unmask_parse_bool($atts['show_designation']);
    $atts['show_avatar'] = unmask_parse_bool($atts['show_avatar']);
    $atts['show_name'] = unmask_parse_bool($atts['show_name']);
    $atts['user_id'] = intval($atts['user_id']);

    // Get user data
    $user = get_userdata($atts['user_id']);
    if ($user) {
        $atts['display_name'] = $user->display_name;
        $atts['designation'] = unmask_get_user_designation($atts['user_id']);
    }

    ob_start();
    get_template_part('template-parts/components/unmask-iso-card', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_iso_card', 'unmask_iso_card_shortcode');
