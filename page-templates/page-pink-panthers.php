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

        <?php if (isset($_GET['pp_submitted'])): ?>
            <div class="pp-success-toast">
                <div class="pp-success-toast__icon">✓</div>
                <div class="pp-success-toast__content">
                    <?php if ($_GET['pp_submitted'] === 'performer'): ?>
                        <p class="pp-success-toast__title">act submitted</p>
                        <p class="pp-success-toast__text">we'll be in touch if you're selected for the lineup.</p>
                    <?php elseif ($_GET['pp_submitted'] === 'volunteer'): ?>
                        <p class="pp-success-toast__title">thanks for volunteering</p>
                        <p class="pp-success-toast__text">we'll reach out with next steps closer to the event.</p>
                    <?php elseif ($_GET['pp_submitted'] === 'notify'): ?>
                        <p class="pp-success-toast__title">you're on the list</p>
                        <p class="pp-success-toast__text">we'll email you when tickets go on sale.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

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
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <?php wp_nonce_field('pp_performer_submit', 'pp_performer_nonce'); ?>
                                <input type="hidden" name="action" value="pp_performer_submit">

                                <div class="pp-form-group">
                                    <label class="pp-form-label" for="performer_name">name / stage name</label>
                                    <input type="text" name="performer_name" id="performer_name" class="pp-form-input" placeholder="what do you go by">
                                </div>
                                <div class="pp-form-group">
                                    <label class="pp-form-label" for="act_type">act type</label>
                                    <select name="act_type" id="act_type" class="pp-form-select" required>
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
                                    <label class="pp-form-label" for="act_description">describe your act</label>
                                    <textarea name="act_description" id="act_description" class="pp-form-textarea" placeholder="what should we expect"></textarea>
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
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="pp-notify-form">
                                <?php wp_nonce_field('pp_notify_submit', 'pp_notify_nonce'); ?>
                                <input type="hidden" name="action" value="pp_notify_submit">
                                <input type="email" name="notify_email" class="pp-notify-input" placeholder="your email" required <?php echo is_user_logged_in() ? 'value="' . esc_attr(wp_get_current_user()->user_email) . '"' : ''; ?>>
                                <button type="submit" class="pp-notify-btn">notify me</button>
                            </form>
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
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <?php wp_nonce_field('pp_volunteer_submit', 'pp_volunteer_nonce'); ?>
                                <input type="hidden" name="action" value="pp_volunteer_submit">

                                <div class="pp-form-group">
                                    <label class="pp-form-label" for="role_interest">role interest</label>
                                    <select name="role_interest" id="role_interest" class="pp-form-select" required>
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
