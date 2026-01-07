<?php
/**
 * ISO Detail Modal Component
 *
 * Modal/overlay for viewing full ISO listing details.
 * Content is populated via JavaScript.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_logged_in = is_user_logged_in();
?>

<div class="iso-detail-overlay" id="isoDetailOverlay" onclick="unmaskIsoCloseDetail(event)">
    <div class="iso-detail" onclick="event.stopPropagation()">

        <div class="iso-detail-header">
            <div class="iso-detail-poster">
                <div class="iso-detail-avatar">
                    <img src="" alt="" id="isoDetailAvatar">
                </div>
                <div class="iso-detail-poster-info">
                    <div class="iso-detail-designation" id="isoDetailDesignation"></div>
                    <div class="iso-detail-posted" id="isoDetailPosted"></div>
                </div>
            </div>
            <button class="iso-detail-close" onclick="unmaskIsoCloseDetail()" aria-label="Close">&times;</button>
        </div>

        <div class="iso-detail-body">
            <div class="iso-detail-badges" id="isoDetailBadges"></div>

            <h2 class="iso-detail-title" id="isoDetailTitle"></h2>

            <div class="iso-detail-description" id="isoDetailDescription"></div>

            <div class="iso-detail-meta">
                <div class="iso-detail-meta-item">
                    <span class="iso-detail-meta-label">location</span>
                    <span class="iso-detail-meta-value" id="isoDetailLocation"></span>
                </div>
                <div class="iso-detail-meta-item">
                    <span class="iso-detail-meta-label">expires</span>
                    <span class="iso-detail-meta-value" id="isoDetailExpires"></span>
                </div>
                <div class="iso-detail-meta-item">
                    <span class="iso-detail-meta-label">category</span>
                    <span class="iso-detail-meta-value" id="isoDetailCategory"></span>
                </div>
                <div class="iso-detail-meta-item">
                    <span class="iso-detail-meta-label">studio</span>
                    <span class="iso-detail-meta-value" id="isoDetailFactory"></span>
                </div>
            </div>

            <div class="iso-detail-actions">
                <?php if ($is_logged_in) : ?>
                    <a href="#" class="iso-detail-respond-btn" id="isoDetailRespondBtn">[send message]</a>
                    <p class="iso-detail-hint">responding opens a private message thread</p>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="iso-detail-respond-btn">[sign in to respond]</a>
                    <p class="iso-detail-hint">you must be signed in to respond to listings</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
