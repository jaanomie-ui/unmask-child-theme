<?php
/**
 * ISO Factory Promo Card Component
 *
 * Static promotional card for The Factory studio space.
 * Displays within the ISO grid at a random position.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<article class="iso-listing-card iso-promo-card">
    <div class="iso-promo-content">
        <span class="iso-promo-label">the factory</span>
        <h3 class="iso-promo-title">CONNECT THROUGH ARTISTIC PRACTICE</h3>
        <p class="iso-promo-description">studio space in Back of the Yards for shoots, sessions, and creative work. $75/hr (Drones: $60/hr)</p>
        <a href="<?php echo esc_url(home_url('/the-factory/')); ?>" class="iso-promo-cta">[learn more]</a>
    </div>
</article>
