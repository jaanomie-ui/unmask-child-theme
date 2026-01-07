<?php
/**
 * Collapsing Header
 *
 * Reduces header height on scroll for more content space on mobile.
 * 60px → 44px when user scrolls past threshold.
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue collapsing header styles
 */
function unmask_collapsing_header_styles() {
    ?>
    <style id="unmask-collapsing-header">
    /* ==========================================================================
       COLLAPSING HEADER
       Reduces header height on scroll for mobile devices
       ========================================================================== */

    /* Default header state */
    .site-header,
    #masthead,
    .bb-mobile-header {
        transition: height 0.25s ease-out,
                    padding 0.25s ease-out,
                    transform 0.25s ease-out !important;
    }

    /* Logo transitions */
    .site-header .site-branding img,
    .bb-mobile-header .site-branding img {
        transition: height 0.25s ease-out, width 0.25s ease-out !important;
    }

    /* Collapsed state - triggered by .header-collapsed class on body */
    @media (max-width: 768px) {
        /* Collapsed header height */
        body.header-collapsed .site-header,
        body.header-collapsed #masthead,
        body.header-collapsed .bb-mobile-header {
            height: 44px !important;
            min-height: 44px !important;
        }

        /* Adjust header inner padding */
        body.header-collapsed .site-header .container,
        body.header-collapsed .bb-mobile-header .bb-mobile-header-container {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }

        /* Shrink logo */
        body.header-collapsed .site-header .site-branding img,
        body.header-collapsed .bb-mobile-header .site-branding img {
            max-height: 28px !important;
        }

        /* Hide tagline if present */
        body.header-collapsed .site-description {
            display: none !important;
        }

        /* Adjust content offset */
        body.header-collapsed #content,
        body.header-collapsed .site-content,
        body.header-collapsed #buddypress {
            margin-top: 44px !important;
        }

        /* BuddyBoss specific - adjust main content top margin */
        body.header-collapsed.sticky-header .site-content {
            padding-top: 44px !important;
        }
    }

    /* Hidden state - header slides up completely (optional, aggressive) */
    body.header-hidden .site-header,
    body.header-hidden #masthead,
    body.header-hidden .bb-mobile-header {
        transform: translateY(-100%) !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'unmask_collapsing_header_styles', 100);

/**
 * Enqueue collapsing header JavaScript
 */
function unmask_collapsing_header_script() {
    // Only on frontend
    if (is_admin()) {
        return;
    }
    ?>
    <script id="unmask-collapsing-header-js">
    (function() {
        'use strict';

        var lastScrollY = 0;
        var ticking = false;
        var body = document.body;

        // Configuration
        var COLLAPSE_THRESHOLD = 50;  // Pixels scrolled before collapsing
        var HIDE_THRESHOLD = 200;     // Pixels scrolled before hiding (disabled by default)
        var ENABLE_HIDE = false;      // Set to true to enable full header hide

        function updateHeader() {
            var currentScrollY = window.scrollY || window.pageYOffset;

            // Collapse header after threshold
            if (currentScrollY > COLLAPSE_THRESHOLD) {
                body.classList.add('header-collapsed');
            } else {
                body.classList.remove('header-collapsed');
            }

            // Optional: Hide header completely on long scroll down
            if (ENABLE_HIDE) {
                var scrollingDown = currentScrollY > lastScrollY;
                var scrolledFar = currentScrollY > HIDE_THRESHOLD;

                if (scrollingDown && scrolledFar) {
                    body.classList.add('header-hidden');
                } else {
                    body.classList.remove('header-hidden');
                }
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        function onScroll() {
            if (!ticking) {
                requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }

        // Only enable on mobile viewport
        function init() {
            if (window.innerWidth <= 768) {
                window.addEventListener('scroll', onScroll, { passive: true });
                // Initial check
                updateHeader();
            }
        }

        // Handle resize
        var resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (window.innerWidth <= 768) {
                    window.addEventListener('scroll', onScroll, { passive: true });
                } else {
                    window.removeEventListener('scroll', onScroll);
                    body.classList.remove('header-collapsed', 'header-hidden');
                }
            }, 100);
        });

        // Initialize
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
    <?php
}
add_action('wp_footer', 'unmask_collapsing_header_script', 50);
