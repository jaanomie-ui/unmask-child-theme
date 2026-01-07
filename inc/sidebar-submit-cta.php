<?php
/**
 * Sidebar Submit CTA
 *
 * Adds a prominent "Submit" call-to-action button to the BuddyBoss sidebar.
 * Encourages content submission to the magazine.
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

/**
 * Add Submit CTA to BuddyPanel sidebar
 * Hooks into wp_footer to inject via JavaScript
 */
function unmask_sidebar_submit_cta() {
    // Only for logged-in users
    if (!is_user_logged_in()) {
        return;
    }

    $submit_url = home_url('/submit/'); // Adjust URL as needed
    ?>
    <script>
    (function() {
        'use strict';

        function addSubmitCTA() {
            // Find the BuddyPanel menu
            var menus = document.querySelectorAll('.buddypanel-menu, .bb-mobile-panel-body ul.bb-panel-menu');

            menus.forEach(function(menu) {
                // Check if CTA already exists
                if (menu.querySelector('.unmask-submit-cta')) {
                    return;
                }

                // Create CTA element
                var cta = document.createElement('li');
                cta.className = 'unmask-submit-cta';
                cta.innerHTML = '<a href="<?php echo esc_url($submit_url); ?>">' +
                    '<span class="unmask-submit-cta__icon">' +
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                    '<path d="M12 5v14M5 12h14"/>' +
                    '</svg>' +
                    '</span>' +
                    '<span class="unmask-submit-cta__label">submit</span>' +
                    '</a>';

                // Insert at the beginning of the menu (after any header)
                var firstItem = menu.querySelector('li');
                if (firstItem) {
                    menu.insertBefore(cta, firstItem);
                } else {
                    menu.appendChild(cta);
                }
            });
        }

        // Run on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', addSubmitCTA);
        } else {
            addSubmitCTA();
        }

        // Also run after a short delay (for dynamically loaded panels)
        setTimeout(addSubmitCTA, 500);
        setTimeout(addSubmitCTA, 1500);
    })();
    </script>
    <?php
}
add_action('wp_footer', 'unmask_sidebar_submit_cta', 99);

/**
 * Enqueue Submit CTA styles
 */
function unmask_sidebar_submit_cta_styles() {
    ?>
    <style id="unmask-submit-cta-styles">
    /* Submit CTA Button in Sidebar */
    .unmask-submit-cta {
        margin: 16px 16px 8px !important;
        padding: 0 !important;
    }

    .unmask-submit-cta a {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        padding: 12px 20px !important;
        background: var(--primitive-red, #c41e3a) !important;
        color: white !important;
        border-radius: 4px !important;
        text-decoration: none !important;
        font-family: var(--font-ui) !important;
        font-size: var(--type-sm, 14px) !important;
        font-weight: 500 !important;
        text-transform: lowercase !important;
        letter-spacing: 0.02em !important;
        transition: background 0.15s ease-out, transform 0.1s ease-out !important;
    }

    .unmask-submit-cta a:hover {
        background: #a01830 !important;
        color: white !important;
        transform: translateY(-1px) !important;
    }

    .unmask-submit-cta a:active {
        transform: translateY(0) !important;
    }

    .unmask-submit-cta__icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .unmask-submit-cta__icon svg {
        width: 16px;
        height: 16px;
    }

    .unmask-submit-cta__label {
        line-height: 1;
    }

    /* Mobile panel adjustments */
    .bb-mobile-panel-inner .unmask-submit-cta {
        margin: 12px !important;
    }

    .bb-mobile-panel-inner .unmask-submit-cta a {
        padding: 14px 24px !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'unmask_sidebar_submit_cta_styles', 100);
