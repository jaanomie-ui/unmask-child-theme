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
 *
 * ACF Fields (non-repeating):
 * - factory_label: Text (default: "the Factory")
 * - factory_title: Text (default: "the factory")
 * - factory_tagline: Text (default: "Industrial third space for creation.")
 * - factory_price: Number (default: 40)
 * - factory_capacity: Number (default: 6)
 * - factory_sqft: Number (default: 700)
 * - factory_award: Text (default: "BOTY")
 * - factory_award_location: Text (default: "chicago")
 * - factory_min_hours: Number (default: 2)
 * - factory_location_text: Textarea
 * - factory_cancellation_policy: Textarea
 * - factory_sustainability_callout: Text
 * - factory_carousel_hint: Text (default: "scroll for more")
 * - factory_booking_shortcode: Text (default: "[factory_booking]")
 *
 * ACF Fields (repeaters - need Pro):
 * - factory_carousel_slides, factory_about_tags, factory_best_for_tags,
 *   factory_amenities, factory_house_rules, factory_accessibility_items
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get ACF field values with fallbacks
$factory_label = unmask_get_field('factory_label', 'the Factory');
$factory_title = unmask_get_field('factory_title', 'the factory');
$factory_tagline = unmask_get_field('factory_tagline', 'Industrial third space for creation.');
$factory_price = unmask_get_field('factory_price', 40);
$factory_capacity = unmask_get_field('factory_capacity', 6);
$factory_sqft = unmask_get_field('factory_sqft', 700);
$factory_award = unmask_get_field('factory_award', 'BOTY');
$factory_award_location = unmask_get_field('factory_award_location', 'chicago');
$factory_min_hours = unmask_get_field('factory_min_hours', 2);
$factory_location_text = unmask_get_field('factory_location_text', 'The Factory is located in Chicago\'s Back of the Yards neighborhood. Exact address provided upon booking confirmation. On-site parking available.');
$factory_cancellation_policy = unmask_get_field('factory_cancellation_policy', 'Full refund if cancelled 72 hours or more before your booking. 50% refund if cancelled within 72 hours. No refund for cancellations made less than 24 hours before the booking start time.');
$factory_sustainability_callout = unmask_get_field('factory_sustainability_callout', 'Factory bookings help keep <em>UNMASK</em> sustainable.');
$factory_carousel_hint = unmask_get_field('factory_carousel_hint', 'scroll for more');
$factory_booking_shortcode = unmask_get_field('factory_booking_shortcode', '[factory_booking]');

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
                <div class="factory-carousel__hint"><?php echo esc_html($factory_carousel_hint); ?></div>
            </section>

            <!-- Two-Column Layout -->
            <div class="factory-layout">

                <!-- LEFT: Content Column -->
                <div class="factory-content">

                    <!-- Title Block -->
                    <header class="factory-header">
                        <span class="factory-label"><?php echo esc_html($factory_label); ?></span>
                        <h1 class="factory-title"><?php echo esc_html($factory_title); ?></h1>
                        <p class="factory-tagline"><?php echo esc_html($factory_tagline); ?></p>
                    </header>

                    <!-- Info Cards Row -->
                    <div class="factory-info-cards">
                        <div class="factory-info-card">
                            <span class="factory-info-card__value">$<?php echo esc_html($factory_price); ?></span>
                            <span class="factory-info-card__label">/hour</span>
                        </div>
                        <div class="factory-info-card">
                            <span class="factory-info-card__value"><?php echo esc_html($factory_capacity); ?></span>
                            <span class="factory-info-card__label">max</span>
                        </div>
                        <div class="factory-info-card">
                            <span class="factory-info-card__value"><?php echo esc_html($factory_sqft); ?></span>
                            <span class="factory-info-card__label">sq ft</span>
                        </div>
                        <div class="factory-info-card">
                            <span class="factory-info-card__value"><?php echo esc_html($factory_award); ?></span>
                            <span class="factory-info-card__label"><?php echo esc_html($factory_award_location); ?></span>
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
                            <?php echo esc_html($factory_location_text); ?>
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
                            <?php echo esc_html($factory_cancellation_policy); ?>
                        </p>
                    </section>

                    <!-- Sustainability Callout -->
                    <aside class="factory-callout">
                        <span class="factory-callout__icon">△</span>
                        <span class="factory-callout__text"><?php echo wp_kses_post($factory_sustainability_callout); ?></span>
                    </aside>

                </div>

                <!-- RIGHT: Booking Widget (Sticky) -->
                <aside class="factory-sidebar">
                    <div class="factory-booking" id="factory-booking">
                        <div class="factory-booking__header">
                            <div class="factory-booking__price">
                                <span class="factory-booking__amount">$<?php echo esc_html($factory_price); ?></span>
                                <span class="factory-booking__unit">per hour</span>
                            </div>
                            <span class="factory-booking__note"><?php echo esc_html($factory_min_hours); ?> hour minimum</span>
                        </div>
                        <div class="factory-booking__form">
                            <?php echo do_shortcode($factory_booking_shortcode); ?>
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
        <span class="factory-mobile-footer__amount">$<?php echo esc_html($factory_price); ?></span>
        <span class="factory-mobile-footer__unit">/hr · <?php echo esc_html($factory_min_hours); ?>hr min</span>
    </div>
    <a href="#factory-booking" class="factory-mobile-footer__btn">book <?php echo esc_html($factory_label); ?></a>
</div>

<?php
get_footer();
?>
