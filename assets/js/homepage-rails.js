/**
 * Homepage Rails - Carousel Touch Fix
 *
 * Ensures horizontal swipe works on mobile carousels by:
 * 1. Removing any event listeners that might block touch
 * 2. Re-enabling native scroll behavior
 * 3. Preventing BuddyBoss/plugin JavaScript from interfering
 *
 * @package UNMASK
 * @since 1.0.0
 */
(function() {
    'use strict';

    /**
     * Fix carousel elements by cloning (removes event listeners)
     * and ensuring proper scroll styles
     */
    function fixCarousels() {
        var carousels = document.querySelectorAll('.unmask-mobile__carousel');

        carousels.forEach(function(carousel) {
            // Skip if already fixed
            if (carousel.getAttribute('data-carousel-fixed') === 'true') {
                return;
            }

            // Clone and replace to remove ALL event listeners attached by other scripts
            var clone = carousel.cloneNode(true);
            if (carousel.parentNode) {
                carousel.parentNode.replaceChild(clone, carousel);
            }

            // Force proper inline styles (overrides everything)
            clone.style.setProperty('display', 'flex', 'important');
            clone.style.setProperty('flex-wrap', 'nowrap', 'important');
            clone.style.setProperty('overflow-x', 'scroll', 'important');
            clone.style.setProperty('overflow-y', 'hidden', 'important');
            clone.style.setProperty('-webkit-overflow-scrolling', 'touch', 'important');
            clone.style.setProperty('scroll-snap-type', 'x mandatory', 'important');
            clone.style.setProperty('touch-action', 'pan-x pan-y', 'important');
            clone.style.setProperty('-ms-touch-action', 'pan-x pan-y', 'important');
            clone.style.setProperty('scrollbar-width', 'none', 'important');
            clone.style.setProperty('-ms-overflow-style', 'none', 'important');
            clone.style.setProperty('pointer-events', 'auto', 'important');

            // Mark as fixed to prevent re-processing
            clone.setAttribute('data-carousel-fixed', 'true');

            // Also fix child elements
            var children = clone.children;
            for (var i = 0; i < children.length; i++) {
                children[i].style.setProperty('flex-shrink', '0', 'important');
                children[i].style.setProperty('scroll-snap-align', 'start', 'important');
                children[i].style.setProperty('pointer-events', 'auto', 'important');
            }

            console.log('[UNMASK] Carousel fixed:', clone.closest('section')?.querySelector('.unmask-mobile__section-title')?.textContent || 'unknown');
        });
    }

    /**
     * Prevent other scripts from attaching problematic event listeners
     * by running our fix after them
     */
    function initFixes() {
        // Run immediately
        fixCarousels();

        // Run again after DOMContentLoaded (catches early scripts)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fixCarousels);
        }

        // Run after window load (catches late scripts)
        window.addEventListener('load', function() {
            fixCarousels();
            // Run again after a delay to catch async scripts
            setTimeout(fixCarousels, 500);
            setTimeout(fixCarousels, 1500);
            setTimeout(fixCarousels, 3000);
        });
    }

    // Start
    initFixes();

})();
