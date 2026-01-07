<?php
/**
 * Template Name: Pink Panthers
 * Description: Landing page for Pink Panthers performance night
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article class="pp-page">

            <!-- HERO - 3x3 GRID -->
            <section class="pp-hero">
                <!-- Row 1 -->
                <div class="pp-hero__cell pp-hero__cell--1x1">
                    <p class="pp-hero__presents">UNMASK presents</p>
                </div>
                <div class="pp-hero__cell pp-hero__cell--1x2"></div>
                <div class="pp-hero__cell pp-hero__cell--1x3">
                    <p class="pp-hero__edition">\001</p>
                </div>

                <!-- Row 2 -->
                <div class="pp-hero__cell pp-hero__cell--2x1-2">
                    <h1 class="pp-hero__title">Pink Panthers</h1>
                </div>
                <div class="pp-hero__cell pp-hero__cell--2x3"></div>

                <!-- Row 3 -->
                <div class="pp-hero__cell pp-hero__cell--3x1">
                    <div class="pp-hero__meta">
                        <p class="pp-hero__meta-item">march 1, 2025</p>
                        <p class="pp-hero__meta-item">9pm</p>
                        <p class="pp-hero__meta-item">the Factory</p>
                    </div>
                </div>
                <div class="pp-hero__cell pp-hero__cell--3x2">
                    <p class="pp-hero__tagline">ALL DREAMS COME TRUE AT THE PINK PANTHERS NIGHTCLUB.</p>
                </div>
                <div class="pp-hero__cell pp-hero__cell--3x3"></div>
            </section>

            <!-- THE FORMAT -->
            <section class="pp-section">
                <p class="pp-section__label">the format</p>
                <h2 class="pp-format__headline">Six performers. Thirty voters. One winner.</h2>
                <div class="pp-format__body">
                    <p>No rules. 5-15 minute slots. Mixed disciplines. The audience votes. $100 cash to the winner. Photos become the archive.</p>
                </div>
            </section>

            <!-- THE CAST -->
            <section class="pp-section" id="cast">
                <p class="pp-section__label">the cast</p>

                <div class="pp-cast-grid">

                    <!-- PERFORMER CARD -->
                    <div class="pp-cast-card">
                        <div class="pp-cast-card__header">
                            <p class="pp-cast-card__role">the performer</p>
                        </div>
                        <div class="pp-cast-card__body">
                            <p class="pp-cast-card__count">6</p>
                            <p class="pp-cast-card__label">slots</p>
                            <p class="pp-cast-card__detail">Compete for $100. Get documented.</p>
                        </div>
                        <div class="pp-cast-card__footer">
                            <form>
                                <div class="pp-form-group">
                                    <label class="pp-form-label">act type</label>
                                    <select class="pp-form-select">
                                        <option value="" disabled selected>select one</option>
                                        <option value="drag">drag</option>
                                        <option value="burlesque">burlesque</option>
                                        <option value="spoken-word">spoken word</option>
                                        <option value="music">live music</option>
                                        <option value="noise">noise</option>
                                        <option value="dance">dance</option>
                                        <option value="performance-art">performance art</option>
                                        <option value="other">other</option>
                                    </select>
                                </div>
                                <div class="pp-form-group">
                                    <label class="pp-form-label">describe your act</label>
                                    <textarea class="pp-form-textarea" placeholder="what should we expect"></textarea>
                                </div>
                                <button type="submit" class="pp-form-submit">submit act</button>
                                <p class="pp-form-note">Response does not guarantee a slot.</p>
                            </form>
                        </div>
                    </div>

                    <!-- VOTER CARD -->
                    <div class="pp-cast-card">
                        <div class="pp-cast-card__header">
                            <p class="pp-cast-card__role">the voter</p>
                        </div>
                        <div class="pp-cast-card__body">
                            <p class="pp-cast-card__count">30</p>
                            <p class="pp-cast-card__label">seats</p>
                            <p class="pp-cast-card__detail">$15 ticket. You decide who wins.</p>
                        </div>
                        <div class="pp-cast-card__footer">
                            <p class="pp-cast-card__status">Tickets not yet on sale.</p>
                            <button class="pp-notify-btn">notify me</button>
                        </div>
                    </div>

                    <!-- HOUSE CARD -->
                    <div class="pp-cast-card">
                        <div class="pp-cast-card__header">
                            <p class="pp-cast-card__role">the house</p>
                        </div>
                        <div class="pp-cast-card__body">
                            <p class="pp-cast-card__count">?</p>
                            <p class="pp-cast-card__label">roles</p>
                            <p class="pp-cast-card__detail">Door. Bar. Photo. Sound. MC.</p>
                        </div>
                        <div class="pp-cast-card__footer">
                            <form>
                                <div class="pp-form-group">
                                    <label class="pp-form-label">role interest</label>
                                    <select class="pp-form-select">
                                        <option value="" disabled selected>select one</option>
                                        <option value="door">door</option>
                                        <option value="bar">bar</option>
                                        <option value="photo">photography</option>
                                        <option value="video">video</option>
                                        <option value="sound">sound</option>
                                        <option value="mc">MC</option>
                                        <option value="other">other</option>
                                    </select>
                                </div>
                                <button type="submit" class="pp-form-submit">volunteer</button>
                            </form>
                        </div>
                    </div>

                </div>
            </section>

            <!-- THE DETAILS -->
            <section class="pp-section">
                <p class="pp-section__label">the details</p>

                <div class="pp-details-grid">
                    <div class="pp-detail-item">
                        <p class="pp-detail-label">date</p>
                        <p class="pp-detail-value">march 1, 2025</p>
                    </div>
                    <div class="pp-detail-item">
                        <p class="pp-detail-label">doors</p>
                        <p class="pp-detail-value">9pm</p>
                    </div>
                    <div class="pp-detail-item">
                        <p class="pp-detail-label">location</p>
                        <p class="pp-detail-value">the Factory</p>
                    </div>
                    <div class="pp-detail-item">
                        <p class="pp-detail-label">tickets</p>
                        <p class="pp-detail-value pp-detail-value--muted">$15 (coming soon)</p>
                    </div>
                    <div class="pp-detail-item">
                        <p class="pp-detail-label">capacity</p>
                        <p class="pp-detail-value">30</p>
                    </div>
                    <div class="pp-detail-item">
                        <p class="pp-detail-label">prize</p>
                        <p class="pp-detail-value">$100 cash</p>
                    </div>
                </div>
            </section>

        </article>
    </main>
</div>

<?php get_footer(); ?>
