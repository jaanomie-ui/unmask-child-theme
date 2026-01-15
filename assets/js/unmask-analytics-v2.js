/**
 * UNMASK Analytics v2 - Lean Tracking
 *
 * Focused analytics for 6 priority metrics:
 * 1. Unique Visitors (GA4 native)
 * 2. Bounce Rate (GA4 automatic)
 * 3. Pages per Session (GA4 automatic)
 * 4. Registration Rate (custom event)
 * 5. New Registrations (count of above)
 * 6. Profile Completion (custom event)
 *
 * Plus A/B variant tracking on all events.
 *
 * @package UNMASK
 * @since 2.0.0
 */

(function() {
    'use strict';

    // ==========================================================================
    // CONFIGURATION
    // ==========================================================================

    const CONFIG = {
        debug: false, // Set true to log events to console
        ctaSelectors: [
            '.unmask-hero__cta',
            '.widget-sq--submit a',
            '.widget-sq--panthers a',
            '.rail__link',
            '.factory-card',
            '[data-track-cta]'
        ]
    };

    // ==========================================================================
    // UTILITIES
    // ==========================================================================

    /**
     * Push event to dataLayer
     */
    function pushEvent(eventName, params = {}) {
        window.dataLayer = window.dataLayer || [];

        // Always include A/B variants if available
        const variants = getVariants();
        if (variants) {
            params.ab_variants = variants;
        }

        // Include page context
        params.page_type = getPageType();
        params.user_status = document.body.classList.contains('logged-in') ? 'member' : 'visitor';

        const eventData = {
            event: eventName,
            ...params
        };

        window.dataLayer.push(eventData);

        if (CONFIG.debug) {
            console.log('[UNMASK Analytics]', eventName, params);
        }
    }

    /**
     * Get current A/B variants from dataLayer
     */
    function getVariants() {
        if (!window.dataLayer) return null;

        for (let i = window.dataLayer.length - 1; i >= 0; i--) {
            if (window.dataLayer[i].ab_variants) {
                return window.dataLayer[i].ab_variants;
            }
        }
        return null;
    }

    /**
     * Detect page type from body classes
     */
    function getPageType() {
        const body = document.body;

        if (body.classList.contains('home')) return 'homepage';
        if (body.classList.contains('single-post')) return 'article';
        if (body.classList.contains('single-iso')) return 'iso_listing';
        if (body.classList.contains('bp-user')) return 'profile';
        if (body.classList.contains('page-template-template-iso-board')) return 'iso_board';
        if (body.classList.contains('page-template-template-archive-magazine')) return 'archive';
        if (body.classList.contains('page-template-template-homepage')) return 'homepage';
        if (body.classList.contains('page-template-page-submit')) return 'submit';
        if (body.classList.contains('page-template-page-welcome')) return 'onboarding';

        return 'other';
    }

    // ==========================================================================
    // CTA TRACKING
    // ==========================================================================

    /**
     * Track CTA clicks for A/B measurement
     */
    function trackCTAClicks() {
        CONFIG.ctaSelectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(el => {
                el.addEventListener('click', function(e) {
                    const ctaText = this.textContent.trim().substring(0, 50);
                    const ctaHref = this.href || this.closest('a')?.href || '';
                    const ctaId = this.dataset.trackCta || ctaText.toLowerCase().replace(/\s+/g, '_');

                    pushEvent('cta_click', {
                        cta_id: ctaId,
                        cta_text: ctaText,
                        cta_destination: ctaHref
                    });
                });
            });
        });
    }

    // ==========================================================================
    // RAIL/CARD INTERACTIONS
    // ==========================================================================

    /**
     * Track rail scrolling (horizontal navigation)
     */
    function trackRailInteractions() {
        const rails = document.querySelectorAll('.rail__track');

        rails.forEach(rail => {
            let hasScrolled = false;

            rail.addEventListener('scroll', function() {
                if (!hasScrolled) {
                    hasScrolled = true;
                    const railLabel = this.closest('.rail')?.querySelector('.rail__label')?.textContent || 'unknown';

                    pushEvent('rail_scroll', {
                        rail_name: railLabel.toLowerCase().replace(/\s+/g, '_')
                    });
                }
            }, { passive: true });
        });
    }

    /**
     * Track card clicks
     */
    function trackCardClicks() {
        document.querySelectorAll('.card, .factory-card').forEach(card => {
            card.addEventListener('click', function() {
                const cardTitle = this.querySelector('.card__title, .factory-card__title')?.textContent || '';
                const cardType = this.classList.contains('card--record') ? 'record' :
                               this.classList.contains('factory-card') ? 'factory' : 'other';

                pushEvent('card_click', {
                    card_type: cardType,
                    card_title: cardTitle.trim().substring(0, 50)
                });
            });
        });
    }

    // ==========================================================================
    // ENGAGEMENT TRACKING
    // ==========================================================================

    /**
     * Track scroll depth milestones
     */
    function trackScrollDepth() {
        const milestones = [25, 50, 75, 90];
        const reached = new Set();

        function checkScroll() {
            const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = Math.round((window.scrollY / scrollHeight) * 100);

            milestones.forEach(milestone => {
                if (scrollPercent >= milestone && !reached.has(milestone)) {
                    reached.add(milestone);
                    pushEvent('scroll_depth', {
                        depth_percent: milestone
                    });
                }
            });
        }

        window.addEventListener('scroll', checkScroll, { passive: true });
    }

    /**
     * Track time on page (30s, 60s, 120s milestones)
     */
    function trackTimeOnPage() {
        const milestones = [30, 60, 120];
        let elapsed = 0;

        const timer = setInterval(() => {
            elapsed += 10;

            milestones.forEach((milestone, index) => {
                if (elapsed === milestone) {
                    pushEvent('time_on_page', {
                        time_seconds: milestone
                    });
                }
            });

            if (elapsed >= 120) {
                clearInterval(timer);
            }
        }, 10000);
    }

    // ==========================================================================
    // CUSTOM EVENT HELPERS (called from PHP/forms)
    // ==========================================================================

    /**
     * Global function to track registration
     */
    window.unmaskTrackRegistration = function(userId, source) {
        pushEvent('registration_complete', {
            user_id: userId,
            registration_source: source || 'direct'
        });
    };

    /**
     * Global function to track profile completion
     */
    window.unmaskTrackProfileComplete = function(userId, completionPercent) {
        pushEvent('profile_complete', {
            user_id: userId,
            completion_percent: completionPercent
        });
    };

    /**
     * Global function to track email signup
     */
    window.unmaskTrackEmailSignup = function(context) {
        pushEvent('email_signup', {
            signup_context: context || 'unknown'
        });
    };

    // ==========================================================================
    // INITIALIZATION
    // ==========================================================================

    function init() {
        // Wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setup);
        } else {
            setup();
        }
    }

    function setup() {
        trackCTAClicks();
        trackRailInteractions();
        trackCardClicks();
        trackScrollDepth();
        trackTimeOnPage();

        if (CONFIG.debug) {
            console.log('[UNMASK Analytics] v2 initialized');
        }
    }

    init();

})();
