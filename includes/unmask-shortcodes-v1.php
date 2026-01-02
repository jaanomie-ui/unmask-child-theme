<?php
/**
 * UNMASK Shortcodes v1
 *
 * Component shortcodes for use in Gutenberg blocks when PHP isn't possible.
 * Prefer using get_template_part() directly in PHP templates.
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
 * Usage: [unmask_button text="Click me" type="primary" href="/page/"]
 *
 * @param array $atts Shortcode attributes
 * @return string Button HTML
 */
function unmask_button_shortcode($atts) {
    $atts = shortcode_atts(array(
        'text'     => 'Button',
        'type'     => 'primary',  // primary, secondary, ghost, danger
        'size'     => 'md',       // sm, md, lg
        'href'     => '#',
        'target'   => '',
        'icon'     => '',
        'disabled' => 'false',
        'class'    => '',
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
 * Usage: [unmask_badge text="D-047" type="drone" dot="true"]
 *
 * @param array $atts Shortcode attributes
 * @return string Badge HTML
 */
function unmask_badge_shortcode($atts) {
    $atts = shortcode_atts(array(
        'text'  => '',
        'type'  => 'default',  // published, draft, expired, active, drone, visitor, schedule, available
        'size'  => 'md',       // sm, md, lg
        'dot'   => 'false',
        'class' => '',
    ), $atts, 'unmask_badge');

    $atts['dot'] = unmask_parse_bool($atts['dot']);

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
 * Usage: [unmask_avatar user_id="1" size="lg" online="true"]
 *
 * @param array $atts Shortcode attributes
 * @return string Avatar HTML
 */
function unmask_avatar_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id' => get_current_user_id(),
        'size'    => 'md',      // xs, sm, md, lg, xl
        'online'  => 'false',
        'square'  => 'false',
        'href'    => '',
        'class'   => '',
    ), $atts, 'unmask_avatar');

    $atts['online'] = unmask_parse_bool($atts['online']);
    $atts['square'] = unmask_parse_bool($atts['square']);
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
 * Usage: [unmask_card post_id="123"]
 * Or:    [unmask_card subject="Title" image="url" href="/page/"]
 *
 * @param array $atts Shortcode attributes
 * @return string Card HTML
 */
function unmask_card_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id'    => 0,
        'image'      => '',
        'file_id'    => '',
        'type_badge' => '',
        'subject'    => '',
        'desc'       => '',
        'href'       => '',
        'class'      => '',
    ), $atts, 'unmask_card');

    $atts['post_id'] = intval($atts['post_id']);

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
