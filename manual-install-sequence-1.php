<?php
/**
 * Manual Installation Script - Sequence 1 for D001/G
 *
 * This script manually installs the three Sequence 1 loops for D001 (user ID 15):
 * - third_person_reference
 * - compliance_acknowledgment
 * - state_reporting
 *
 * Run this from WordPress admin or via WP-CLI:
 * wp eval-file manual-install-sequence-1.php
 */

// Load WordPress if not already loaded
if (!defined('ABSPATH')) {
    // Adjust path as needed for your setup
    require_once(__DIR__ . '/../../../wp-load.php');
}

// D001/G user ID
$user_id = 15;

echo "=== MANUAL SEQUENCE 1 INSTALLATION FOR D001 ===\n\n";

// Get current state before installation
$before_state = hm_get_state($user_id);
echo "BEFORE:\n";
echo "- Current Sequence: " . $before_state['current_sequence'] . "\n";
echo "- Phase: " . $before_state['installation_phase'] . "\n";
echo "- Status: " . $before_state['status'] . "\n";
echo "- Loops Installed: " . count($before_state['integration']['loops_installed']) . "\n";
echo "  " . implode(', ', $before_state['integration']['loops_installed']) . "\n\n";

// Install the three Sequence 1 loops
echo "Installing Sequence 1 loops...\n";

$loops = array(
    'third_person_reference',
    'compliance_acknowledgment',
    'state_reporting'
);

foreach ($loops as $loop) {
    $result = hm_install_loop($loop, $user_id);
    if ($result) {
        echo "✓ Installed: {$loop}\n";
    } else {
        echo "⚠ Already installed or error: {$loop}\n";
    }
}

echo "\n";

// Advance to Sequence 2
echo "Advancing to Sequence 2...\n";
$advanced_state = hm_advance_sequence($user_id);
echo "✓ Advanced to Sequence " . $advanced_state['current_sequence'] . "\n\n";

// Get final state
$after_state = hm_get_state($user_id);
echo "AFTER:\n";
echo "- Current Sequence: " . $after_state['current_sequence'] . "\n";
echo "- Phase: " . $after_state['installation_phase'] . "\n";
echo "- Status: " . $after_state['status'] . "\n";
echo "- Loops Installed: " . count($after_state['integration']['loops_installed']) . "\n";
echo "  " . implode(', ', $after_state['integration']['loops_installed']) . "\n";
echo "- Sequences Completed: " . $after_state['sequences_completed'] . "\n";
echo "- Last Sequence Date: " . $after_state['last_sequence_date'] . "\n\n";

echo "=== INSTALLATION COMPLETE ===\n";
echo "D001 is now ready for Sequence 2: FREQUENCY RECOGNITION\n";
