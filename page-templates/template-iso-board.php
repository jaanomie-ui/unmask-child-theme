<?php
/**
 * Template Name: ISO Board
 *
 * ISO Board page template - seeking/offering collaboration feature.
 * Displays filterable grid of ISO listings with Factory integration.
 *
 * @package UNMASK
 * @since 1.0.0
 */

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
        <h1 class="page-title">iso board</h1>
        <p class="page-subtitle">seeking / offering / connecting</p>
    </header>

    <?php if ($is_logged_in) : ?>
    <div class="iso-submit-bar">
        <span class="iso-submit-bar-text">have something to offer or need?</span>
        <button type="button" class="iso-submit-btn" onclick="unmaskIsoOpenSubmitModal()">[post to board]</button>
    </div>
    <?php else : ?>
    <div class="iso-alert-banner" id="isoAlertBanner">
        <span class="iso-alert-text">sign in to see who is posting and respond to listings</span>
        <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="iso-alert-cta">[sign in]</a>
    </div>
    <?php endif; ?>

    <?php get_template_part('template-parts/components/iso-filters'); ?>

    <p class="iso-listing-count"><span id="isoCount"><?php echo esc_html($iso_count); ?></span> active listings</p>

    <div class="iso-grid" id="isoGrid">
        <?php if ($iso_query->have_posts()) : ?>
            <?php
            $card_index = 0;
            while ($iso_query->have_posts()) : $iso_query->the_post();
                // Insert promo card at random position
                if ($card_index === $promo_position) {
                    get_template_part('template-parts/components/iso-factory-promo-card');
                }

                get_template_part('template-parts/components/iso-listing-card', null, [
                    'post_id' => get_the_ID(),
                    'is_blurred' => $show_blur
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
            <div class="iso-empty">No listings match your filters.</div>
        <?php endif; ?>
    </div>

    <?php get_template_part('template-parts/components/iso-detail-modal'); ?>

</div>

<!-- ISO Submit Modal -->
<?php if ($is_logged_in) : ?>
<div class="iso-submit-overlay" id="isoSubmitOverlay" onclick="unmaskIsoCloseSubmitModal(event)">
    <div class="iso-submit-modal" onclick="event.stopPropagation()">
        <div class="iso-submit-modal-header">
            <span class="iso-submit-modal-title">post to iso board</span>
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

    // Current filter state
    var filters = {
        type: '',
        category: '',
        factory: 'yes' // Factory ON by default
    };

    // ISO data cache for detail view
    var isoCache = {};

    /**
     * Load listings via AJAX
     */
    function loadListings() {
        var container = document.getElementById('isoGrid');
        container.innerHTML = '<div class="iso-loading">loading...</div>';

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
                    container.innerHTML = data.data.html || '<div class="iso-empty">No listings match your filters.</div>';
                    document.getElementById('isoCount').textContent = data.data.count || 0;

                    // Cache ISO data
                    if (data.data.cache) {
                        isoCache = data.data.cache;
                    }
                } else {
                    container.innerHTML = '<div class="iso-empty">No listings found.</div>';
                    document.getElementById('isoCount').textContent = '0';
                }
            })
            .catch(function(err) {
                console.error('ISO Board error:', err);
                container.innerHTML = '<div class="iso-empty">Error loading listings.</div>';
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
                        factoryEl.textContent = 'hosting at the Factory';
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
