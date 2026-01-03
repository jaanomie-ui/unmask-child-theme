<?php
/**
 * Template Name: The Factory — Book
 * Template Post Type: page
 *
 * Factory booking page with rates, info, and booking form.
 * Uses BuddyBoss header/sidebar for consistency.
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

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article class="factory-book-page">

            <!-- Header -->
            <header class="factory-book-header">
                <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="factory-book-back">
                    <span class="factory-book-back__arrow">←</span>
                    <span class="factory-book-back__text">the Factory</span>
                </a>
                <h1 class="factory-book-title">book a session</h1>
                <p class="factory-book-location">back of the yards, chicago</p>
            </header>

            <!-- Two-Column Layout -->
            <div class="factory-book-layout">

                <!-- LEFT: Context Column (rates, includes, policy) -->
                <aside class="factory-book-context">

                    <!-- Rates -->
                    <section class="factory-book-section">
                        <h2 class="factory-book-section__title">Rates</h2>

                        <div class="factory-book-rate factory-book-rate--active">
                            <span class="factory-book-rate__price">$75</span>
                            <span class="factory-book-rate__unit">/hour</span>
                            <span class="factory-book-rate__label">standard</span>
                        </div>

                        <div class="factory-book-rate factory-book-rate--disabled">
                            <span class="factory-book-rate__badge">coming soon</span>
                            <span class="factory-book-rate__price">$60</span>
                            <span class="factory-book-rate__unit">/hour</span>
                            <span class="factory-book-rate__label">Drone member rate</span>
                        </div>
                    </section>

                </aside>

                <!-- RIGHT: Booking Form Column -->
                <div class="factory-book-form">
                    <?php echo do_shortcode('[factory_booking]'); ?>
                </div>

            </div>

        </article>
    </main>
</div>

<?php
get_footer();
