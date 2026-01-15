<?php
/**
 * UNMASK A/B Testing Infrastructure
 *
 * Lightweight cookie-based A/B testing for element-level experiments.
 * Variants are assigned on first visit and persist for 30 days.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Active experiments configuration
 *
 * Format: 'test_id' => [
 *     'variants' => ['A', 'B'],     // Available variants
 *     'weights'  => [50, 50],       // Traffic split percentages
 *     'active'   => true,           // Is test running?
 * ]
 */
function unmask_get_experiments() {
    return [
        'homepage_v2' => [
            'variants' => ['A', 'B'],
            'weights'  => [50, 50],
            'active'   => true,
        ],
        'cta_copy' => [
            'variants' => ['A', 'B'],
            'weights'  => [50, 50],
            'active'   => false,
        ],
    ];
}

/**
 * Get variant for a specific test
 *
 * If no variant assigned, randomly assigns one based on weights.
 * Stores assignment in cookie for 30 days.
 *
 * @param string $test_id The experiment identifier
 * @return string|null The variant ('A', 'B', etc.) or null if test inactive
 */
function unmask_get_variant($test_id) {
    $experiments = unmask_get_experiments();

    // Check if test exists and is active
    if (!isset($experiments[$test_id]) || !$experiments[$test_id]['active']) {
        return null;
    }

    $config = $experiments[$test_id];
    $cookie_name = 'unmask_ab_' . $test_id;

    // Check for existing assignment
    if (isset($_COOKIE[$cookie_name])) {
        $variant = sanitize_text_field($_COOKIE[$cookie_name]);
        // Validate variant is still valid
        if (in_array($variant, $config['variants'])) {
            return $variant;
        }
    }

    // Assign new variant based on weights
    $variant = unmask_weighted_random($config['variants'], $config['weights']);

    // Set cookie (30 days)
    if (!headers_sent()) {
        setcookie(
            $cookie_name,
            $variant,
            time() + (30 * DAY_IN_SECONDS),
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        $_COOKIE[$cookie_name] = $variant; // Make available immediately
    }

    return $variant;
}

/**
 * Check if user is in a specific variant
 *
 * @param string $test_id The experiment identifier
 * @param string $variant The variant to check ('A', 'B', etc.)
 * @return bool True if user is in the specified variant
 */
function unmask_is_variant($test_id, $variant) {
    return unmask_get_variant($test_id) === $variant;
}

/**
 * Get all active variants for current user
 *
 * Returns array of test_id => variant for all active tests.
 * Useful for passing to analytics/dataLayer.
 *
 * @return array Associative array of test assignments
 */
function unmask_get_all_variants() {
    $experiments = unmask_get_experiments();
    $variants = [];

    foreach ($experiments as $test_id => $config) {
        if ($config['active']) {
            $variant = unmask_get_variant($test_id);
            if ($variant) {
                $variants[$test_id] = $variant;
            }
        }
    }

    return $variants;
}

/**
 * Weighted random selection
 *
 * @param array $items Items to choose from
 * @param array $weights Corresponding weights (must sum to 100)
 * @return mixed Selected item
 */
function unmask_weighted_random($items, $weights) {
    $rand = mt_rand(1, 100);
    $cumulative = 0;

    foreach ($items as $index => $item) {
        $cumulative += $weights[$index];
        if ($rand <= $cumulative) {
            return $item;
        }
    }

    // Fallback to first item
    return $items[0];
}

/**
 * Output variants to dataLayer for GA4 tracking
 *
 * Called via wp_head hook to ensure variants are available
 * before any analytics scripts fire.
 */
function unmask_output_variants_to_datalayer() {
    $variants = unmask_get_all_variants();

    if (empty($variants)) {
        return;
    }

    ?>
    <script>
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        'event': 'ab_variants_loaded',
        'ab_variants': <?php echo wp_json_encode($variants); ?>
    });
    </script>
    <?php
}
add_action('wp_head', 'unmask_output_variants_to_datalayer', 5);

/**
 * Force a specific variant (for testing/preview)
 *
 * Usage: Add ?ab_force=test_id:variant to URL
 * Example: ?ab_force=homepage_v2:B
 */
function unmask_handle_forced_variants() {
    if (!isset($_GET['ab_force'])) {
        return;
    }

    $force = sanitize_text_field($_GET['ab_force']);
    $parts = explode(':', $force);

    if (count($parts) !== 2) {
        return;
    }

    list($test_id, $variant) = $parts;
    $experiments = unmask_get_experiments();

    // Validate test and variant
    if (!isset($experiments[$test_id])) {
        return;
    }

    if (!in_array($variant, $experiments[$test_id]['variants'])) {
        return;
    }

    // Set cookie
    $cookie_name = 'unmask_ab_' . $test_id;
    if (!headers_sent()) {
        setcookie(
            $cookie_name,
            $variant,
            time() + (30 * DAY_IN_SECONDS),
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        $_COOKIE[$cookie_name] = $variant;
    }
}
add_action('init', 'unmask_handle_forced_variants', 1);
