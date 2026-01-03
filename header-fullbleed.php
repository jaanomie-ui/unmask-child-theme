<?php
/**
 * Full-Bleed Header
 *
 * Minimal header for full-bleed pages (homepage hero, single records).
 * Bypasses BuddyBoss container structure entirely.
 *
 * Usage: get_header('fullbleed');
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if this is a registration/welcome page (these have their own header)
$is_registration_page = is_page_template('page-templates/page-register-visitor.php') ||
                        is_page_template('page-templates/page-welcome.php');
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class($is_registration_page ? 'unmask-fullbleed unmask-registration-mode' : 'unmask-fullbleed'); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

    <?php
    // Skip BuddyBoss header on registration/welcome pages (they have their own system-bar)
    if (!$is_registration_page && function_exists('buddyboss_theme_header')) {
        buddyboss_theme_header();
    }
    ?>

    <main id="main" class="site-main unmask-main--fullbleed">
