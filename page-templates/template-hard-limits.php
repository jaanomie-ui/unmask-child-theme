<?php
/**
 * Template Name: Hard Limits Checklist
 * Description: Traffic-light BDSM/kink preferences form for drones
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="hard-limits-page">
    <?php get_template_part('template-parts/forms/hard-limits-form'); ?>
</main>

<?php get_footer(); ?>
