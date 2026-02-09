<?php
/**
 * Enable Drone Dashboard Access
 *
 * One-time script to enable drone access for core users.
 * Run this via WPCode snippet or similar.
 *
 * Users to enable:
 * - ID 1: Admin (ja.anomie@gmail.com)
 * - ID 15: Pony Drone (ponyhound@icloud.com)
 * - ID 43: Test Drone (drone-test@unmask.local)
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Only run if hm_enable_drone_dashboard function exists
if (!function_exists('hm_enable_drone_dashboard')) {
    echo "ERROR: hm_enable_drone_dashboard() function not found. Make sure hive-mistress-state.php is loaded.\n";
    return;
}

// Users to enable
$drone_users = array(
    1  => 'Admin (THE_DRONE_JA)',
    15 => 'Pony Drone (DRONE-001)',
    43 => 'Test Drone (V-025)'
);

echo "ENABLING DRONE DASHBOARD ACCESS\n";
echo "================================\n\n";

foreach ($drone_users as $user_id => $label) {
    $result = hm_enable_drone_dashboard($user_id);

    if ($result) {
        echo "✓ Enabled for {$label} (ID: {$user_id})\n";

        // Verify it was set
        $is_enabled = get_user_meta($user_id, 'hm_drone_enabled', true);
        echo "  → Meta value: " . ($is_enabled ? "TRUE" : "FALSE") . "\n";

        // Check if state was initialized
        $state = get_user_meta($user_id, 'hm_drone_state', true);
        echo "  → State initialized: " . (!empty($state) ? "YES" : "NO") . "\n";
    } else {
        echo "✗ Failed to enable for {$label} (ID: {$user_id})\n";
    }
    echo "\n";
}

echo "COMPLETE\n";
echo "========\n";
echo "All three users should now have access to the Drone Conditioning Console.\n";
echo "Visit: " . home_url('/drone-console/') . "\n";
