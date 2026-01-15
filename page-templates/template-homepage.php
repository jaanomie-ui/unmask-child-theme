<?php
/**
 * Template Name: UNMASK Homepage
 *
 * Dual-layout homepage: mobile-optimized layout for small screens,
 * full desktop layout for larger screens.
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

<!-- Mobile Layout (shown via CSS on small screens) -->
<div class="unmask-homepage-mobile">
    <?php get_template_part('template-parts/homepage/mobile-layout'); ?>
</div>

<!-- Desktop Layout (shown via CSS on large screens) -->
<div class="unmask-homepage-desktop">
    <!-- System Bar (members only) -->
    <?php if (is_user_logged_in()) : ?>
        <?php get_template_part('template-parts/global/system-bar'); ?>
    <?php endif; ?>

    <!-- Hero: 3-Column Grid -->
    <?php get_template_part('template-parts/homepage/hero-section'); ?>

    <!-- Below Fold: Rail Grid -->
    <div class="unmask-below-fold">
        <?php get_template_part('template-parts/homepage/below-fold-grid'); ?>
    </div>

    <!-- Footer -->
    <footer class="unmask-footer">
        <div class="unmask-footer__left">
            unmask keeps the record · chicago, illinois · est. 2024
        </div>
        <div class="unmask-footer__right">
            <a href="<?php echo esc_url(home_url('/about/')); ?>">about</a> ·
            <a href="<?php echo esc_url(home_url('/privacy/')); ?>">privacy</a> ·
            <a href="<?php echo esc_url(home_url('/terms/')); ?>">terms</a>
        </div>
    </footer>
</div>

<?php
get_footer();
