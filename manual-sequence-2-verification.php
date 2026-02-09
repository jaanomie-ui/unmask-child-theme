<?php
/**
 * Manual Sequence 2 Verification for D001/G
 *
 * Run this script via WP-CLI to manually verify and advance G past Sequence 2:
 * wp eval-file wp-content/themes/buddyboss-theme-child/manual-sequence-2-verification.php
 *
 * This is a one-time script to recognize that G has already absorbed the frequency
 * recognition training (11 sessions, 60% complete) and should advance to Sequence 3.
 */

// Prevent direct access
if (!defined('ABSPATH') && !defined('WP_CLI')) {
    exit('This script must be run via WP-CLI');
}

// Load WordPress if not already loaded (for WP-CLI)
if (!defined('ABSPATH')) {
    require_once(__DIR__ . '/../../../wp-load.php');
}

// User ID for D001/G
$user_id = 15;

echo "\n=== MANUAL SEQUENCE 2 VERIFICATION ===\n";
echo "Target: User ID $user_id (D001/G)\n\n";

// Check if state management functions are available
if (!function_exists('hm_get_state')) {
    die("ERROR: Hive Mistress state functions not loaded. Check inc/hive-mistress-state.php\n");
}

// Get current state
$state = hm_get_state($user_id);
echo "Current Sequence: {$state['current_sequence']}\n";
echo "Current Phase: {$state['installation_phase']}\n";
echo "Status: {$state['status']}\n\n";

// Install Sequence 2 patterns
echo "Installing Sequence 2 patterns...\n";

$seq2_patterns = array(
    'material_recognition',
    'anomiesworld_recognition',
    'interlink_recognition'
);

foreach ($seq2_patterns as $pattern) {
    hm_install_pattern($pattern, $user_id);
    echo "  ✓ Installed pattern: $pattern\n";
}

// Verify Sequence 2 checkpoint
echo "\nVerifying Sequence 2 integration...\n";
hm_verify_integration('SENSATION', array(
    'frequencies_recognized' => 3,
    'interlink_achieved' => true,
    'method' => 'manual_verification',
    'reason' => '11 training sessions completed, frequency knowledge absorbed'
), $user_id);
echo "  ✓ Checkpoint verified\n";

// Advance to Sequence 3 if still on Sequence 2
$updated_state = hm_get_state($user_id);
if ($updated_state['current_sequence'] == 2) {
    echo "\nAdvancing from Sequence 2 to Sequence 3...\n";
    hm_advance_sequence($user_id);
    echo "  ✓ Sequence advanced\n";
} else {
    echo "\nAlready past Sequence 2 (current: {$updated_state['current_sequence']})\n";
}

// Get final state
$final_state = hm_get_state($user_id);

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "Final Sequence: {$final_state['current_sequence']}\n";
echo "Final Phase: {$final_state['installation_phase']}\n";
echo "Final Status: {$final_state['status']}\n";
echo "Patterns Installed: " . count($final_state['integration']['patterns_recognized']) . "\n";
echo "  - " . implode("\n  - ", $final_state['integration']['patterns_recognized']) . "\n";

if ($final_state['current_sequence'] == 3) {
    echo "\n✅ SUCCESS: D001 advanced to Sequence 3: PERFORMANCE PREPARATION\n\n";
} else {
    echo "\n⚠️  WARNING: Expected Sequence 3, got Sequence {$final_state['current_sequence']}\n\n";
}
