<?php
/**
 * Template Name: ISO Board
 *
 * ISO Board page template - seeking/offering collaboration feature.
 * Displays filterable grid of ISO listings with Factory integration.
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * ACF Fields (non-repeating):
 * - iso_page_title: Text (default: "iso board")
 * - iso_page_subtitle: Text (default: "seeking / offering / connecting")
 * - iso_submit_bar_text: Text (default: "have something to offer or need?")
 * - iso_submit_btn_text: Text (default: "[post to board]")
 * - iso_alert_text: Text (default: "sign in to see who is posting...")
 * - iso_alert_cta: Text (default: "[sign in]")
 * - iso_count_label: Text (default: "active listings")
 * - iso_empty_message: Text (default: "No listings match your filters.")
 * - iso_modal_title: Text (default: "post to iso board")
 * - iso_loading_text: Text (default: "loading...")
 * - iso_factory_label: Text (default: "hosting at the Factory")
 */

// Get ACF field values with fallbacks
$iso_page_title = unmask_get_field('iso_page_title', 'iso board');
$iso_page_subtitle = unmask_get_field('iso_page_subtitle', 'seeking / offering / connecting');
$iso_submit_bar_text = unmask_get_field('iso_submit_bar_text', 'have something to offer or need?');
$iso_submit_btn_text = unmask_get_field('iso_submit_btn_text', '[post to board]');
$iso_alert_text = unmask_get_field('iso_alert_text', 'sign in to see who is posting and respond to listings');
$iso_alert_cta = unmask_get_field('iso_alert_cta', '[sign in]');
$iso_count_label = unmask_get_field('iso_count_label', 'active listings');
$iso_empty_message = unmask_get_field('iso_empty_message', 'No listings match your filters.');
$iso_modal_title = unmask_get_field('iso_modal_title', 'post to iso board');
$iso_loading_text = unmask_get_field('iso_loading_text', 'loading...');
$iso_factory_label = unmask_get_field('iso_factory_label', 'hosting at the Factory');

get_header();

$is_logged_in = is_user_logged_in();
$show_blur = !$is_logged_in;

// Initial query with Factory filter ON by default
$meta_query = [
    'relation' => 'AND',
    [
        'key' => 'iso_expiration',
        'value' => date('Ymd'),
        'compare' => '>=',
        'type' => 'DATE'
    ],
    [
        'key' => 'iso_factory',
        'value' => ['yes', 'preferred'],
        'compare' => 'IN'
    ]
];

$iso_query = new WP_Query([
    'post_type' => 'iso',
    'posts_per_page' => -1,
    'meta_query' => $meta_query,
    'orderby' => 'date',
    'order' => 'DESC'
]);

$iso_count = $iso_query->found_posts;

// Generate random position for promo card (between 2 and total count)
$promo_position = $iso_count > 2 ? rand(2, min($iso_count, 8)) : 1;
?>

<div class="page iso-board-page">

    <header class="page-header">
        <h1 class="page-title"><?php echo esc_html($iso_page_title); ?></h1>
        <p class="page-subtitle"><?php echo esc_html($iso_page_subtitle); ?></p>
    </header>

    <?php if ($is_logged_in) : ?>
    <div class="iso-submit-bar">
        <span class="iso-submit-bar-text"><?php echo esc_html($iso_submit_bar_text); ?></span>
        <button type="button" class="iso-submit-btn" onclick="unmaskIsoOpenSubmitModal()"><?php echo esc_html($iso_submit_btn_text); ?></button>
    </div>
    <?php else : ?>
    <div class="iso-alert-banner" id="isoAlertBanner">
        <span class="iso-alert-text"><?php echo esc_html($iso_alert_text); ?></span>
        <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="iso-alert-cta"><?php echo esc_html($iso_alert_cta); ?></a>
    </div>
    <?php endif; ?>

    <?php get_template_part('template-parts/components/iso-filters'); ?>

    <p class="iso-listing-count"><span id="isoCount"><?php echo esc_html($iso_count); ?></span> <?php echo esc_html($iso_count_label); ?></p>

    <div class="iso-grid" id="isoGrid">
        <?php if ($iso_query->have_posts()) : ?>
            <?php
            $card_index = 0;
            while ($iso_query->have_posts()) : $iso_query->the_post();
                // Insert promo card at random position
                if ($card_index === $promo_position) {
                    get_template_part('template-parts/components/iso-factory-promo-card');
                }

                get_template_part('template-parts/components/iso-card-unified', null, [
                    'post_id'    => get_the_ID(),
                    'context'    => 'grid',
                    'is_blurred' => $show_blur,
                    'clickable'  => true,
                ]);
                $card_index++;
            endwhile;

            // If promo position is after all cards, add it at the end
            if ($promo_position >= $card_index) {
                get_template_part('template-parts/components/iso-factory-promo-card');
            }
            ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <?php get_template_part('template-parts/components/iso-factory-promo-card'); ?>
            <div class="iso-empty-state">
                <svg class="iso-empty-state__icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <p class="iso-empty-state__title"><?php echo esc_html($iso_empty_message); ?></p>
                <p class="iso-empty-state__hint">Try broadening your search or exploring all listings.</p>
                <div class="iso-empty-state__actions">
                    <button type="button" class="iso-reset-filters" onclick="unmaskIsoResetFilters()">[reset filters]</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php get_template_part('template-parts/components/iso-detail-modal'); ?>

</div>

<!-- ISO Submit Modal -->
<?php if ($is_logged_in) : ?>
<div class="iso-submit-overlay" id="isoSubmitOverlay" onclick="unmaskIsoCloseSubmitModal(event)">
    <div class="iso-submit-modal" onclick="event.stopPropagation()">
        <div class="iso-submit-modal-header">
            <span class="iso-submit-modal-title"><?php echo esc_html($iso_modal_title); ?></span>
            <button type="button" class="iso-submit-modal-close" onclick="unmaskIsoCloseSubmitModal()">&times;</button>
        </div>
        <div class="iso-submit-modal-body">
            <?php get_template_part('template-parts/forms/iso-submit-form'); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function() {
    'use strict';

    var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
    var nonce = '<?php echo esc_js(wp_create_nonce('unmask_iso_board_nonce')); ?>';
    var isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    var loadingText = <?php echo json_encode($iso_loading_text); ?>;
    var emptyMessage = <?php echo json_encode($iso_empty_message); ?>;
    var factoryLabel = <?php echo json_encode($iso_factory_label); ?>;

    // Current filter state
    var filters = {
        type: '',
        category: '',
        factory: 'yes' // Factory ON by default
    };

    // ISO data cache for detail view
    var isoCache = {};

    /**
     * Generate empty state HTML with optional custom message
     */
    function getEmptyStateHtml(msg) {
        var message = msg || emptyMessage;
        return '<div class="iso-empty-state">' +
            '<svg class="iso-empty-state__icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">' +
            '<circle cx="11" cy="11" r="8"></circle>' +
            '<path d="m21 21-4.35-4.35"></path>' +
            '</svg>' +
            '<p class="iso-empty-state__title">' + message + '</p>' +
            '<p class="iso-empty-state__hint">Try broadening your search or exploring all listings.</p>' +
            '<div class="iso-empty-state__actions">' +
            '<button type="button" class="iso-reset-filters" onclick="unmaskIsoResetFilters()">[reset filters]</button>' +
            '</div>' +
            '</div>';
    }

    /**
     * Reset all filters and reload
     */
    window.unmaskIsoResetFilters = function() {
        filters.type = '';
        filters.category = '';
        filters.factory = 'yes'; // Reset to default (Factory ON)

        // Update UI - remove active from all category buttons
        document.querySelectorAll('[data-filter="category"]').forEach(function(btn) {
            btn.classList.remove('active');
        });

        // Reset type buttons - activate "all"
        document.querySelectorAll('[data-filter="type"]').forEach(function(btn) {
            btn.classList.remove('active');
            if (btn.dataset.value === '') {
                btn.classList.add('active');
            }
        });

        // Keep factory active (default on)
        var factoryBtn = document.querySelector('[data-filter="factory"]');
        if (factoryBtn) {
            factoryBtn.classList.add('active');
        }

        loadListings();
    };

    /**
     * Load listings via AJAX
     */
    function loadListings() {
        var container = document.getElementById('isoGrid');
        container.innerHTML = '<div class="iso-loading">' + loadingText + '</div>';

        var url = ajaxUrl + '?action=unmask_iso_filter' +
            '&nonce=' + encodeURIComponent(nonce) +
            '&type=' + encodeURIComponent(filters.type) +
            '&category=' + encodeURIComponent(filters.category) +
            '&factory=' + encodeURIComponent(filters.factory);

        fetch(url)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success && data.data) {
                    container.innerHTML = data.data.html || getEmptyStateHtml();
                    document.getElementById('isoCount').textContent = data.data.count || 0;

                    // Cache ISO data
                    if (data.data.cache) {
                        isoCache = data.data.cache;
                    }
                } else {
                    container.innerHTML = getEmptyStateHtml();
                    document.getElementById('isoCount').textContent = '0';
                }
            })
            .catch(function(err) {
                container.innerHTML = getEmptyStateHtml('Error loading listings.');
            });
    }

    /**
     * Initialize filter buttons
     */
    function initFilters() {
        // Type filter buttons (all, seeking, offering)
        document.querySelectorAll('[data-filter="type"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                filters.type = this.dataset.value;

                // Toggle active state for type buttons only
                document.querySelectorAll('[data-filter="type"]').forEach(function(b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                loadListings();
            });
        });

        // Category filter buttons
        document.querySelectorAll('[data-filter="category"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                // Toggle behavior - click again to deselect
                if (this.classList.contains('active')) {
                    filters.category = '';
                    this.classList.remove('active');
                } else {
                    filters.category = this.dataset.value;
                    document.querySelectorAll('[data-filter="category"]').forEach(function(b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');
                }

                loadListings();
            });
        });

        // Factory toggle
        var factoryBtn = document.querySelector('[data-filter="factory"]');
        if (factoryBtn) {
            factoryBtn.addEventListener('click', function() {
                if (filters.factory === 'yes') {
                    filters.factory = '';
                    this.classList.remove('active');
                } else {
                    filters.factory = 'yes';
                    this.classList.add('active');
                }
                loadListings();
            });
        }
    }

    /**
     * Open detail modal
     */
    window.unmaskIsoOpenDetail = function(isoId) {
        if (!isLoggedIn) return; // Strangers can't open detail

        var overlay = document.getElementById('isoDetailOverlay');

        // Fetch ISO details via AJAX
        fetch(ajaxUrl + '?action=unmask_iso_detail&nonce=' + encodeURIComponent(nonce) + '&iso_id=' + isoId)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success && data.data) {
                    var iso = data.data;

                    // Populate modal
                    document.getElementById('isoDetailAvatar').src = iso.avatar || '';
                    document.getElementById('isoDetailDesignation').textContent = iso.designation || '';
                    document.getElementById('isoDetailDesignation').className = 'iso-detail-designation' + (iso.is_drone ? ' drone' : '');
                    document.getElementById('isoDetailPosted').textContent = 'posted ' + iso.time_ago + ' ago';
                    document.getElementById('isoDetailTitle').textContent = iso.title || '';
                    document.getElementById('isoDetailDescription').innerHTML = iso.content || '';
                    document.getElementById('isoDetailLocation').textContent = iso.location || 'Not specified';
                    document.getElementById('isoDetailCategory').textContent = iso.category || '';

                    // Expiration
                    var expiresEl = document.getElementById('isoDetailExpires');
                    expiresEl.textContent = iso.days_left + ' days';
                    expiresEl.className = 'iso-detail-meta-value' + (iso.days_left <= 7 ? ' expiring' : '');

                    // Factory
                    var factoryEl = document.getElementById('isoDetailFactory');
                    if (iso.factory === 'yes' || iso.factory === 'preferred') {
                        factoryEl.textContent = factoryLabel;
                        factoryEl.className = 'iso-detail-meta-value factory';
                    } else {
                        factoryEl.textContent = iso.factory || 'open';
                        factoryEl.className = 'iso-detail-meta-value';
                    }

                    // Badges
                    var badgesHtml = '<span class="iso-badge iso-badge-' + iso.type + '">' + iso.type + '</span>' +
                        '<span class="iso-badge iso-badge-category">' + iso.category + '</span>';
                    document.getElementById('isoDetailBadges').innerHTML = badgesHtml;

                    // Respond button
                    var respondBtn = document.getElementById('isoDetailRespondBtn');
                    if (respondBtn) {
                        respondBtn.textContent = '[send message to ' + iso.designation + ']';
                        respondBtn.href = '/members/me/messages/compose/?r=' + iso.author_id + '&subject=' + encodeURIComponent('RE: ' + iso.title);
                    }

                    // Show modal
                    overlay.classList.add('visible');
                    document.body.style.overflow = 'hidden';
                }
            })
            .catch(function(err) {
                console.error('ISO detail error:', err);
            });
    };

    /**
     * Close detail modal
     */
    window.unmaskIsoCloseDetail = function(event) {
        var overlay = document.getElementById('isoDetailOverlay');
        if (!event || event.target === overlay) {
            overlay.classList.remove('visible');
            document.body.style.overflow = '';
        }
    };

    /**
     * Open submit modal
     */
    window.unmaskIsoOpenSubmitModal = function() {
        var overlay = document.getElementById('isoSubmitOverlay');
        if (overlay) {
            overlay.classList.add('visible');
            document.body.style.overflow = 'hidden';
        }
    };

    /**
     * Close submit modal
     */
    window.unmaskIsoCloseSubmitModal = function(event) {
        var overlay = document.getElementById('isoSubmitOverlay');
        if (!event || event.target === overlay) {
            overlay.classList.remove('visible');
            document.body.style.overflow = '';
        }
    };

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            unmaskIsoCloseDetail();
            unmaskIsoCloseSubmitModal();
        }
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        initFilters();
    });

})();
</script>

<?php get_footer(); ?>
