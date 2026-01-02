<?php
/**
 * Template Name: UNMASK Homepage
 *
 * Swiss grid homepage template with 3-column hero.
 * Switches between desktop and mobile layouts using wp_is_mobile().
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

<div class="unmask-homepage">

    <?php if (wp_is_mobile()) : ?>

        <!-- MOBILE LAYOUT -->
        <?php get_template_part('template-parts/homepage/mobile-layout'); ?>

    <?php else : ?>

        <!-- DESKTOP LAYOUT -->
        <div class="unmask-desktop">

            <!-- System Bar (fixed) -->
            <?php get_template_part('template-parts/global/system-bar'); ?>

            <!-- Hero: 3-Column Grid -->
            <?php get_template_part('template-parts/homepage/hero-section'); ?>

            <!-- Below Fold -->
            <div class="unmask-below-fold">
                <div class="unmask-container">

                    <!-- Records Grid -->
                    <?php get_template_part('template-parts/homepage/records-section'); ?>

                    <!-- Directory Grid -->
                    <?php get_template_part('template-parts/homepage/directory-section'); ?>

                    <!-- Activity Feed -->
                    <?php get_template_part('template-parts/homepage/activity-section'); ?>

                </div>
            </div>

            <!-- Footer -->
            <footer class="unmask-footer">
                <div class="unmask-footer__left">
                    unmask is a queer documentation project · chicago, illinois · est. 2024
                </div>
                <div class="unmask-footer__right">
                    <a href="<?php echo esc_url(home_url('/about/')); ?>">about</a> ·
                    <a href="<?php echo esc_url(home_url('/privacy/')); ?>">privacy</a> ·
                    <a href="<?php echo esc_url(home_url('/terms/')); ?>">terms</a>
                </div>
            </footer>

        </div>

    <?php endif; ?>

</div>

<?php
get_footer();
