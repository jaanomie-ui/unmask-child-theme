<?php
/**
 * UNMASK Analytics Data Endpoint
 *
 * REST API endpoint for n8n to push GA4 data to WordPress.
 * Data stored in wp_options for dashboard consumption.
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register REST API routes
 */
function unmask_register_analytics_routes() {
    register_rest_route('unmask/v1', '/analytics', [
        [
            'methods'  => 'POST',
            'callback' => 'unmask_update_analytics_data',
            'permission_callback' => 'unmask_analytics_permission_check',
        ],
        [
            'methods'  => 'GET',
            'callback' => 'unmask_get_analytics_data',
            'permission_callback' => 'unmask_analytics_permission_check',
        ],
    ]);
}
add_action('rest_api_init', 'unmask_register_analytics_routes');

/**
 * Permission check - requires API key in header
 */
function unmask_analytics_permission_check($request) {
    $api_key = $request->get_header('X-Analytics-Key');
    $stored_key = get_option('unmask_analytics_api_key');

    // If no key set, deny access
    if (empty($stored_key)) {
        return new WP_Error(
            'no_api_key_configured',
            'Analytics API key not configured',
            ['status' => 500]
        );
    }

    if ($api_key !== $stored_key) {
        return new WP_Error(
            'invalid_api_key',
            'Invalid API key',
            ['status' => 401]
        );
    }

    return true;
}

/**
 * Update analytics data (POST)
 *
 * Expected payload:
 * {
 *   "visitors": 1247,
 *   "bounce_rate": 42.3,
 *   "pages_per_session": 2.4,
 *   "registrations": 23,
 *   "registration_rate": 1.8,
 *   "registrations_change": 12,
 *   "profile_completions": 15,
 *   "profile_completion_rate": 67,
 *   "profile_change": 8,
 *   "trend_visitors": [100, 120, 150, 130, 180, 200, 220],
 *   "trend_registrations": [2, 3, 3, 4, 5, 5, 6],
 *   "ab_tests": [
 *     {"name": "homepage_v2", "status": "winning", "result": "B winning (+23% bounce reduction)"},
 *     {"name": "cta_copy", "status": "inconclusive", "result": "need 847 more sessions"}
 *   ]
 * }
 */
function unmask_update_analytics_data($request) {
    $data = $request->get_json_params();

    if (empty($data)) {
        return new WP_Error(
            'empty_data',
            'No data provided',
            ['status' => 400]
        );
    }

    // Sanitize data
    $sanitized = unmask_sanitize_analytics_data($data);

    // Store in options
    update_option('unmask_analytics_data', $sanitized);
    update_option('unmask_analytics_updated', current_time('Y-m-d H:i:s'));

    return [
        'success' => true,
        'message' => 'Analytics data updated',
        'updated_at' => get_option('unmask_analytics_updated'),
    ];
}

/**
 * Get current analytics data (GET)
 */
function unmask_get_analytics_data($request) {
    return [
        'data' => get_option('unmask_analytics_data', []),
        'updated_at' => get_option('unmask_analytics_updated', 'never'),
    ];
}

/**
 * Sanitize analytics data
 */
function unmask_sanitize_analytics_data($data) {
    $sanitized = [];

    // Numeric fields
    $numeric_fields = [
        'visitors', 'bounce_rate', 'pages_per_session',
        'registrations', 'registration_rate', 'registrations_change',
        'profile_completions', 'profile_completion_rate', 'profile_change'
    ];

    foreach ($numeric_fields as $field) {
        if (isset($data[$field])) {
            $sanitized[$field] = floatval($data[$field]);
        }
    }

    // Array fields (trend data)
    if (isset($data['trend_visitors']) && is_array($data['trend_visitors'])) {
        $sanitized['trend_visitors'] = array_map('intval', $data['trend_visitors']);
    }

    if (isset($data['trend_registrations']) && is_array($data['trend_registrations'])) {
        $sanitized['trend_registrations'] = array_map('intval', $data['trend_registrations']);
    }

    // A/B test data
    if (isset($data['ab_tests']) && is_array($data['ab_tests'])) {
        $sanitized['ab_tests'] = [];
        foreach ($data['ab_tests'] as $test) {
            $sanitized['ab_tests'][] = [
                'name' => sanitize_text_field($test['name'] ?? ''),
                'status' => sanitize_text_field($test['status'] ?? 'inconclusive'),
                'result' => sanitize_text_field($test['result'] ?? ''),
            ];
        }
    }

    return $sanitized;
}

/**
 * Admin page to set API key
 */
function unmask_analytics_admin_menu() {
    add_submenu_page(
        'options-general.php',
        'Analytics API',
        'Analytics API',
        'manage_options',
        'unmask-analytics-api',
        'unmask_analytics_api_page'
    );
}
add_action('admin_menu', 'unmask_analytics_admin_menu');

/**
 * Admin page content
 */
function unmask_analytics_api_page() {
    // Handle form submission
    if (isset($_POST['unmask_analytics_api_key']) && check_admin_referer('unmask_analytics_api_nonce')) {
        update_option('unmask_analytics_api_key', sanitize_text_field($_POST['unmask_analytics_api_key']));
        echo '<div class="notice notice-success"><p>API key updated.</p></div>';
    }

    $current_key = get_option('unmask_analytics_api_key', '');
    $endpoint = rest_url('unmask/v1/analytics');
    ?>
    <div class="wrap">
        <h1>Analytics API Configuration</h1>
        <p>Configure the API key used by n8n to push analytics data.</p>

        <form method="post">
            <?php wp_nonce_field('unmask_analytics_api_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="unmask_analytics_api_key">API Key</label></th>
                    <td>
                        <input type="text"
                               name="unmask_analytics_api_key"
                               id="unmask_analytics_api_key"
                               value="<?php echo esc_attr($current_key); ?>"
                               class="regular-text"
                        >
                        <p class="description">
                            n8n should send this key in the <code>X-Analytics-Key</code> header.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>Endpoint URL</th>
                    <td>
                        <code><?php echo esc_html($endpoint); ?></code>
                        <p class="description">POST to update data, GET to retrieve current data.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save API Key'); ?>
        </form>

        <h2>Generate New API Key</h2>
        <p>
            <button type="button" class="button" onclick="document.getElementById('unmask_analytics_api_key').value = '<?php echo esc_js(wp_generate_password(32, false)); ?>';">
                Generate Random Key
            </button>
        </p>
    </div>
    <?php
}
