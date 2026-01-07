<?php
/**
 * Template Name: The Factory
 * Template Post Type: page
 *
 * Unified Factory page with info content and sticky booking form.
 * Two-column layout: scrollable content (left) + sticky booking (right).
 * Mobile: content stacks, sticky footer CTA for booking.
 *
 * @package UNMASK
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article class="factory-page">

            <!-- Hero Carousel -->
            <section class="factory-carousel" aria-label="Factory space gallery">
                <div class="factory-carousel__track">
                    <div class="factory-carousel__card">
                        <div class="factory-carousel__image" style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/factory/factory-01.jpg');"></div>
                        <span class="factory-carousel__caption">main shooting bay</span>
                    </div>
                    <div class="factory-carousel__card">
                        <div class="factory-carousel__image" style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/factory/factory-02.jpg');"></div>
                        <span class="factory-carousel__caption">lighting grid</span>
                    </div>
                    <div class="factory-carousel__card">
                        <div class="factory-carousel__image" style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/factory/factory-03.jpg');"></div>
                        <span class="factory-carousel__caption">equipment wall</span>
                    </div>
                    <div class="factory-carousel__card">
                        <div class="factory-carousel__image" style="background-image: url('<?php echo esc_url(get_stylesheet_directory_uri()); ?>/assets/images/factory/factory-04.jpg');"></div>
                        <span class="factory-carousel__caption">processing station</span>
                    </div>
                </div>
                <div class="factory-carousel__hint">scroll for more</div>
            </section>

            <!-- Two-Column Layout -->
            <div class="factory-layout">

                <!-- LEFT: Content Column -->
                <div class="factory-content">

                    <!-- Title Block -->
                    <header class="factory-header">
                        <span class="factory-label">the Factory</span>
                        <h1 class="factory-title">the factory</h1>
                        <p class="factory-tagline">Industrial third space for creatives.</p>
                    </header>

                    <!-- Info Cards Row -->
                    <div class="factory-info-cards">
                        <div class="factory-info-card">
                            <span class="factory-info-card__value">$75</span>
                            <span class="factory-info-card__label">/hour</span>
                        </div>
                        <div class="factory-info-card">
                            <span class="factory-info-card__value">6</span>
                            <span class="factory-info-card__label">max</span>
                        </div>
                        <div class="factory-info-card">
                            <span class="factory-info-card__value">700</span>
                            <span class="factory-info-card__label">sq ft</span>
                        </div>
                        <div class="factory-info-card">
                            <span class="factory-info-card__value">BOTY</span>
                            <span class="factory-info-card__label">chicago</span>
                        </div>
                    </div>

                    <!-- About -->
                    <section class="factory-section">
                        <h2 class="factory-section__label">about</h2>
                        <div class="factory-tags">
                            <span class="factory-tag">industrial</span>
                            <span class="factory-tag">void / blackout</span>
                            <span class="factory-tag">DIY</span>
                            <span class="factory-tag">raw</span>
                            <span class="factory-tag">700 sq ft</span>
                        </div>
                    </section>

                    <!-- Best For -->
                    <section class="factory-section">
                        <h2 class="factory-section__label">best for</h2>
                        <div class="factory-tags">
                            <span class="factory-tag">music videos</span>
                            <span class="factory-tag">studio photography</span>
                            <span class="factory-tag">fashion shoots</span>
                            <span class="factory-tag">floor shibari</span>
                            <span class="factory-tag">small events</span>
                            <span class="factory-tag">art installations</span>
                            <span class="factory-tag">experimental shows</span>
                        </div>
                    </section>

                    <!-- Amenities -->
                    <section class="factory-section">
                        <h2 class="factory-section__label">amenities</h2>
                        <div class="factory-amenities">
                            <div class="factory-amenity">
                                <span class="factory-amenity__check">✓</span>
                                <span>Alien Bee 800 strobe</span>
                            </div>
                            <div class="factory-amenity">
                                <span class="factory-amenity__check">✓</span>
                                <span>COB continuous lights (x2)</span>
                            </div>
                            <div class="factory-amenity">
                                <span class="factory-amenity__check">✓</span>
                                <span>Bluetooth speaker</span>
                            </div>
                            <div class="factory-amenity">
                                <span class="factory-amenity__check">✓</span>
                                <span>Bathroom access</span>
                            </div>
                            <div class="factory-amenity">
                                <span class="factory-amenity__check">✓</span>
                                <span>On-site parking</span>
                            </div>
                            <div class="factory-amenity">
                                <span class="factory-amenity__check">✓</span>
                                <span>Wifi</span>
                            </div>
                        </div>
                    </section>

                    <!-- House Rules -->
                    <section class="factory-section">
                        <h2 class="factory-section__label">house rules</h2>
                        <div class="factory-rules">
                            <div class="factory-rule">
                                <span class="factory-rule__marker">></span>
                                <span>This is a DIY space located in a working factory. Expect dust. Plan accordingly.</span>
                            </div>
                            <div class="factory-rule">
                                <span class="factory-rule__marker">></span>
                                <span>Maximum 6 people. Additional guests require approval and incur extra fees.</span>
                            </div>
                            <div class="factory-rule">
                                <span class="factory-rule__marker">></span>
                                <span>Clean up after yourself. Leave the space as you found it.</span>
                            </div>
                            <div class="factory-rule">
                                <span class="factory-rule__marker">></span>
                                <span>Booking time includes setup and breakdown. Plan your session accordingly.</span>
                            </div>
                        </div>
                    </section>

                    <!-- Location -->
                    <section class="factory-section">
                        <h2 class="factory-section__label">location</h2>
                        <p class="factory-text">
                            The Factory is located in Chicago's Back of the Yards neighborhood. Exact address provided upon booking confirmation. On-site parking available.
                        </p>
                    </section>

                    <!-- Accessibility -->
                    <section class="factory-section">
                        <h2 class="factory-section__label">accessibility</h2>
                        <div class="factory-access">
                            <div class="factory-access__item factory-access__item--warning">
                                <span class="factory-access__icon">!</span>
                                <span>6 flights of stairs. Elevator available if requested in advance.</span>
                            </div>
                            <div class="factory-access__item factory-access__item--warning">
                                <span class="factory-access__icon">!</span>
                                <span>Not wheelchair accessible.</span>
                            </div>
                            <div class="factory-access__item">
                                <span class="factory-access__icon">✓</span>
                                <span>Bathroom accessible on same floor.</span>
                            </div>
                            <div class="factory-access__item">
                                <span class="factory-access__icon">✓</span>
                                <span>On-site parking available.</span>
                            </div>
                        </div>
                    </section>

                    <!-- Cancellation -->
                    <section class="factory-section">
                        <h2 class="factory-section__label">cancellation policy</h2>
                        <p class="factory-text">
                            Full refund if cancelled 72 hours or more before your booking. 50% refund if cancelled within 72 hours. No refund for cancellations made less than 24 hours before the booking start time.
                        </p>
                    </section>

                    <!-- Sustainability Callout -->
                    <aside class="factory-callout">
                        <span class="factory-callout__icon">△</span>
                        <span class="factory-callout__text">Factory bookings help keep <em>UNMASK</em> sustainable.</span>
                    </aside>

                </div>

                <!-- RIGHT: Booking Widget (Sticky) -->
                <aside class="factory-sidebar">
                    <div class="factory-booking" id="factory-booking">
                        <div class="factory-booking__header">
                            <div class="factory-booking__price">
                                <span class="factory-booking__amount">$75</span>
                                <span class="factory-booking__unit">per hour</span>
                            </div>
                            <span class="factory-booking__note">2 hour minimum</span>
                        </div>
                        <div class="factory-booking__form">
                            <?php echo do_shortcode('[factory_booking]'); ?>
                        </div>
                    </div>
                </aside>

            </div>

        </article>
    </main>
</div>

<!-- Mobile Sticky Footer -->
<div class="factory-mobile-footer">
    <div class="factory-mobile-footer__price">
        <span class="factory-mobile-footer__amount">$75</span>
        <span class="factory-mobile-footer__unit">/hr · 2hr min</span>
    </div>
    <a href="#factory-booking" class="factory-mobile-footer__btn">book the Factory</a>
</div>

<?php
get_footer();
?>
