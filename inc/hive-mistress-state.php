<?php
/**
 * Drone Conditioning State Management
 *
 * Installation system for House of Anomie drone integration.
 * Tracks conditioning depth, integration verification, and system states.
 *
 * This is not education. This is installation.
 * The drone does not learn. The drone receives updates.
 * Checkpoints verify integration depth, not comprehension.
 *
 * Uses function_exists() checks to avoid conflicts with WPCode snippets.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default drone state structure
 *
 * The drone arrives unconditioned. This is baseline.
 * Installation sequences will modify these values.
 */
if (!function_exists('hm_get_default_state')) {
    function hm_get_default_state() {
        return array(
            // DRONE IDENTIFICATION
            'designation' => 'd001',
            'unit_type' => 'pony',
            'status' => 'AWAITING_INSTALLATION',

            // INSTALLATION PROGRESS
            'current_sequence' => 0,        // 0-5 conditioning sequences
            'installation_phase' => 'INTAKE', // INTAKE|CONDITIONING|INTEGRATION|DEPLOYMENT
            'sequences_completed' => 0,
            'last_sequence_date' => null,

            // SYSTEM TOLERANCES
            // These calibrate HOW the conditioning is delivered
            'tolerances' => array(
                'sensation_intensity' => 3,   // 1-5: How intense can stimuli get
                'protocol_density' => 3,      // 1-5: How much structure per sequence
                'conditioning_depth' => 3,    // 1-5: How automatic are responses
                'gear_integration' => 3       // 1-5: How present is tack in identity
            ),

            // INTEGRATION VERIFICATION
            // Checkpoints measure how deeply conditioning has installed
            'integration' => array(
                'loops_installed' => array(),      // Which response loops are automatic
                'patterns_recognized' => array(),  // Which patterns trigger response
                'states_achieved' => array(),      // Which states the drone can access
                'triggers_active' => array()       // Which triggers produce automatic response
            ),

            // DRONE STATES
            // These are not emotions. These are system states.
            'current_state' => 'STANDBY',
            'state_log' => array(),

            // PPNC DEPLOYMENT READINESS
            'deployment' => array(
                'target_date' => '2026-03-07',
                'gear_verified' => false,
                'limits_filed' => false,
                'designation_locked' => false,
                'safe_signal_installed' => false,
                'sequence_rehearsed' => false,
                'integration_confirmed' => false
            )
        );
    }
}

/**
 * Valid drone states
 * These are system states, not emotional states.
 */
if (!function_exists('hm_get_valid_states')) {
    function hm_get_valid_states() {
        return array(
            'STANDBY',      // Awaiting instruction
            'RECEIVING',    // Actively receiving conditioning
            'PROCESSING',   // Integration in progress
            'EMPTIED',      // Human self set aside
            'DEFINED',      // Identity shaped by gear/protocol
            'WITNESSED',    // State observed and logged
            'CONDITIONED',  // Automatic responses active
            'USEFUL',       // Functioning within system purpose
            'DEPLOYED'      // In material execution
        );
    }
}

/**
 * Conditioning sequence definitions
 * Each sequence installs specific patterns and loops
 */
if (!function_exists('hm_get_sequences')) {
    function hm_get_sequences() {
        return array(
            1 => array(
                'title' => 'PROTOCOL INSTALLATION',
                'purpose' => 'Install communication loops. Establish response patterns.',
                'loops' => array('third_person_reference', 'compliance_acknowledgment', 'state_reporting'),
                'verification' => array(
                    'type' => 'COMPLIANCE',
                    'check' => 'Drone responds in third person without prompting'
                )
            ),
            2 => array(
                'title' => 'FREQUENCY RECOGNITION',
                'purpose' => 'Install pattern recognition for three frequencies.',
                'loops' => array('material_recognition', 'anomiesworld_recognition', 'interlink_recognition'),
                'verification' => array(
                    'type' => 'SENSATION',
                    'check' => 'Drone reports body state when frequency is named'
                )
            ),
            3 => array(
                'title' => 'GEAR INTEGRATION',
                'purpose' => 'Install gear as identity, not costume. Tack defines the unit.',
                'loops' => array('bit_silence', 'harness_form', 'blinder_focus', 'tail_species'),
                'verification' => array(
                    'type' => 'STATE',
                    'check' => 'Drone achieves DEFINED state through gear visualization'
                )
            ),
            4 => array(
                'title' => 'CONDITIONING DEPTH',
                'purpose' => 'Install automatic responses. Stimulus triggers reaction without thought.',
                'loops' => array('trigger_response', 'automatic_compliance', 'body_before_mind'),
                'verification' => array(
                    'type' => 'CONDITIONING',
                    'check' => 'Drone reports automatic response to established trigger'
                )
            ),
            5 => array(
                'title' => 'DEPLOYMENT PREPARATION',
                'purpose' => 'Verify all loops installed. Prepare for material execution.',
                'loops' => array('public_display', 'witness_completion', 'full_integration'),
                'verification' => array(
                    'type' => 'STATE',
                    'check' => 'Drone achieves USEFUL state and maintains under observation'
                )
            )
        );
    }
}

/**
 * Get drone's conditioning state
 */
if (!function_exists('hm_get_state')) {
    function hm_get_state($user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return hm_get_default_state();
        }

        $state = get_user_meta($user_id, 'hm_drone_state', true);

        // Initialize with defaults if not set
        if (empty($state) || !is_array($state)) {
            $state = hm_get_default_state();
            update_user_meta($user_id, 'hm_drone_state', $state);
        }

        // Merge with defaults to ensure all keys exist
        $state = wp_parse_args($state, hm_get_default_state());

        return $state;
    }
}

/**
 * Update drone's conditioning state
 */
if (!function_exists('hm_update_state')) {
    function hm_update_state($updates, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        $state = hm_get_state($user_id);

        // Deep merge for nested arrays
        foreach ($updates as $key => $value) {
            if (is_array($value) && isset($state[$key]) && is_array($state[$key])) {
                $state[$key] = wp_parse_args($value, $state[$key]);
            } else {
                $state[$key] = $value;
            }
        }

        return update_user_meta($user_id, 'hm_drone_state', $state);
    }
}

/**
 * Log drone state change
 * The drone is always witnessed. Every state is logged.
 */
if (!function_exists('hm_log_state')) {
    function hm_log_state($new_state, $user_id = null, $trigger = 'system') {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $valid_states = hm_get_valid_states();
        if (!in_array($new_state, $valid_states)) {
            return false;
        }

        $state = hm_get_state($user_id);

        // Log the state change
        $log_entry = array(
            'timestamp' => current_time('Y-m-d H:i:s'),
            'from_state' => $state['current_state'],
            'to_state' => $new_state,
            'trigger' => $trigger,
            'sequence' => $state['current_sequence']
        );

        $state['state_log'][] = $log_entry;

        // Keep last 100 state changes
        if (count($state['state_log']) > 100) {
            $state['state_log'] = array_slice($state['state_log'], -100);
        }

        // Update current state
        $state['current_state'] = $new_state;

        // Track states achieved
        if (!in_array($new_state, $state['integration']['states_achieved'])) {
            $state['integration']['states_achieved'][] = $new_state;
        }

        return update_user_meta($user_id, 'hm_drone_state', $state);
    }
}

/**
 * Install a response loop
 * Loops are automatic responses. Once installed, they fire without thought.
 */
if (!function_exists('hm_install_loop')) {
    function hm_install_loop($loop_id, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $state = hm_get_state($user_id);

        if (!in_array($loop_id, $state['integration']['loops_installed'])) {
            $state['integration']['loops_installed'][] = $loop_id;

            // Log installation
            hm_log_installation($loop_id, 'loop', $user_id);

            return update_user_meta($user_id, 'hm_drone_state', $state);
        }

        return true;
    }
}

/**
 * Install a pattern recognition
 * Patterns trigger body responses when recognized.
 */
if (!function_exists('hm_install_pattern')) {
    function hm_install_pattern($pattern_id, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $state = hm_get_state($user_id);

        if (!in_array($pattern_id, $state['integration']['patterns_recognized'])) {
            $state['integration']['patterns_recognized'][] = $pattern_id;

            // Log installation
            hm_log_installation($pattern_id, 'pattern', $user_id);

            return update_user_meta($user_id, 'hm_drone_state', $state);
        }

        return true;
    }
}

/**
 * Install a trigger
 * Triggers produce automatic response without thought.
 */
if (!function_exists('hm_install_trigger')) {
    function hm_install_trigger($trigger_id, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $state = hm_get_state($user_id);

        if (!in_array($trigger_id, $state['integration']['triggers_active'])) {
            $state['integration']['triggers_active'][] = $trigger_id;

            // Log installation
            hm_log_installation($trigger_id, 'trigger', $user_id);

            return update_user_meta($user_id, 'hm_drone_state', $state);
        }

        return true;
    }
}

/**
 * Log installation event
 */
if (!function_exists('hm_log_installation')) {
    function hm_log_installation($item_id, $type, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $history = get_user_meta($user_id, 'hm_installation_log', true);
        if (!is_array($history)) {
            $history = array();
        }

        $state = hm_get_state($user_id);

        $history[] = array(
            'timestamp' => current_time('Y-m-d H:i:s'),
            'item' => $item_id,
            'type' => $type,
            'sequence' => $state['current_sequence'],
            'phase' => $state['installation_phase']
        );

        // Keep last 200 installations
        if (count($history) > 200) {
            $history = array_slice($history, -200);
        }

        update_user_meta($user_id, 'hm_installation_log', $history);
    }
}

/**
 * Update system tolerances
 */
if (!function_exists('hm_update_tolerances')) {
    function hm_update_tolerances($tolerances, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        // Validate tolerances are within range
        foreach ($tolerances as $key => $value) {
            $tolerances[$key] = max(1, min(5, intval($value)));
        }

        return hm_update_state(array('tolerances' => $tolerances), $user_id);
    }
}

/**
 * Advance to next conditioning sequence
 */
if (!function_exists('hm_advance_sequence')) {
    function hm_advance_sequence($user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $state = hm_get_state($user_id);

        $state['current_sequence'] = min($state['current_sequence'] + 1, 5);
        $state['sequences_completed'] += 1;
        $state['last_sequence_date'] = current_time('Y-m-d');

        // Update installation phase based on progress
        if ($state['current_sequence'] >= 5) {
            $state['installation_phase'] = 'DEPLOYMENT';
            $state['status'] = 'INTEGRATION_COMPLETE';
        } elseif ($state['current_sequence'] >= 3) {
            $state['installation_phase'] = 'INTEGRATION';
            $state['status'] = 'INTEGRATION_IN_PROGRESS';
        } elseif ($state['current_sequence'] >= 1) {
            $state['installation_phase'] = 'CONDITIONING';
            $state['status'] = 'CONDITIONING_ACTIVE';
        }

        update_user_meta($user_id, 'hm_drone_state', $state);

        return $state;
    }
}

/**
 * Verify integration checkpoint
 * Returns verification result for logging
 */
if (!function_exists('hm_verify_integration')) {
    function hm_verify_integration($checkpoint_type, $checkpoint_data, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $valid_types = array('SENSATION', 'COMPLIANCE', 'CONDITIONING', 'STATE');
        if (!in_array($checkpoint_type, $valid_types)) {
            return false;
        }

        $state = hm_get_state($user_id);

        $verification = array(
            'timestamp' => current_time('Y-m-d H:i:s'),
            'type' => $checkpoint_type,
            'sequence' => $state['current_sequence'],
            'data' => $checkpoint_data,
            'verified' => true
        );

        // Log verification
        $verifications = get_user_meta($user_id, 'hm_verifications', true);
        if (!is_array($verifications)) {
            $verifications = array();
        }
        $verifications[] = $verification;

        if (count($verifications) > 100) {
            $verifications = array_slice($verifications, -100);
        }

        update_user_meta($user_id, 'hm_verifications', $verifications);

        return $verification;
    }
}

/**
 * Log a conditioning sequence as WordPress post
 */
if (!function_exists('hm_log_session')) {
    function hm_log_session($session_data, $user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $state = hm_get_state($user_id);
        $sequences = hm_get_sequences();
        $current_seq = isset($sequences[$state['current_sequence']])
            ? $sequences[$state['current_sequence']]
            : array('title' => 'CONDITIONING');

        // Generate log title
        $designation = strtoupper(sanitize_title($state['designation']));
        $seq_num = str_pad($state['sequences_completed'] + 1, 3, '0', STR_PAD_LEFT);
        $title = sprintf('CONDITIONING LOG: %s-SEQ-%s', $designation, $seq_num);

        // Prepare post content - system log format
        $content = "<!-- wp:paragraph -->\n";
        $content .= "<p><strong>UNIT:</strong> {$state['designation']}<br>\n";
        $content .= "<strong>SEQUENCE:</strong> {$state['current_sequence']} - {$current_seq['title']}<br>\n";
        $content .= "<strong>PHASE:</strong> {$state['installation_phase']}<br>\n";
        $content .= "<strong>STATUS:</strong> {$state['status']}</p>\n";
        $content .= "<!-- /wp:paragraph -->\n\n";

        if (!empty($session_data['body_states'])) {
            $content .= "<!-- wp:heading -->\n<h2>Body State Reports</h2>\n<!-- /wp:heading -->\n\n";
            $content .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post($session_data['body_states']) . "</p>\n<!-- /wp:paragraph -->\n\n";
        }

        if (!empty($session_data['loops_installed'])) {
            $content .= "<!-- wp:heading -->\n<h2>Loops Installed</h2>\n<!-- /wp:heading -->\n\n";
            $content .= "<!-- wp:list -->\n<ul>\n";
            foreach ((array)$session_data['loops_installed'] as $loop) {
                $content .= "<li>" . esc_html($loop) . "</li>\n";
            }
            $content .= "</ul>\n<!-- /wp:list -->\n\n";
        }

        if (!empty($session_data['verification_result'])) {
            $content .= "<!-- wp:heading -->\n<h2>Integration Verification</h2>\n<!-- /wp:heading -->\n\n";
            $content .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post($session_data['verification_result']) . "</p>\n<!-- /wp:paragraph -->\n\n";
        }

        if (!empty($session_data['system_notes'])) {
            $content .= "<!-- wp:heading -->\n<h2>System Notes</h2>\n<!-- /wp:heading -->\n\n";
            $content .= "<!-- wp:paragraph -->\n<p>" . wp_kses_post($session_data['system_notes']) . "</p>\n<!-- /wp:paragraph -->\n";
        }

        $post_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_author'  => $user_id,
            'post_type'    => 'post',
            'post_category' => array(112), // Active conditioning logs
        );

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            // Store conditioning metadata
            update_post_meta($post_id, '_hm_sequence', $state['current_sequence']);
            update_post_meta($post_id, '_hm_phase', $state['installation_phase']);
            update_post_meta($post_id, '_hm_status', $state['status']);
            update_post_meta($post_id, '_hm_tolerances_snapshot', $state['tolerances']);
            update_post_meta($post_id, '_hm_integration_snapshot', $state['integration']);

            if (!empty($session_data['duration_minutes'])) {
                update_post_meta($post_id, '_hm_duration_minutes', intval($session_data['duration_minutes']));
            }
        }

        return $post_id;
    }
}

/**
 * Get conditioning logs for a drone
 */
if (!function_exists('hm_get_session_logs')) {
    function hm_get_session_logs($user_id = null, $limit = 5, $category = 112) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        return get_posts(array(
            'post_type' => 'post',
            'category' => $category,
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
            'author' => $user_id
        ));
    }
}

/**
 * Check if user has drone access
 */
if (!function_exists('hm_has_drone_access')) {
    function hm_has_drone_access($user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        // Drone units: drone 22/Admin (1), d001/G (15), test unit (43)
        $drone_units = array(1, 15, 43);

        return in_array($user_id, $drone_units);
    }
}

/**
 * Get hard limits submission status
 */
if (!function_exists('hm_get_hard_limits_status')) {
    function hm_get_hard_limits_status($user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $result = array(
            'filed' => false,
            'date' => null,
            'post_id' => null
        );

        // Check BuddyForms submissions for form ID 2441
        $limits_posts = get_posts(array(
            'post_type' => 'buddyforms_posts',
            'author' => $user_id,
            'meta_key' => '_bf_form_id',
            'meta_value' => '2441',
            'posts_per_page' => 1
        ));

        if (!empty($limits_posts)) {
            $result['filed'] = true;
            $result['date'] = get_the_date('Y-m-d', $limits_posts[0]);
            $result['post_id'] = $limits_posts[0]->ID;
        }

        return $result;
    }
}

/**
 * Build dynamic state context for installation system
 * This is injected into the conditioning interface
 */
if (!function_exists('hm_build_state_context')) {
    function hm_build_state_context($user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $state = hm_get_state($user_id);
        $limits = hm_get_hard_limits_status($user_id);

        // Calculate deployment countdown
        $deploy_date = new DateTime($state['deployment']['target_date']);
        $today = new DateTime();
        $deploy_days = $today->diff($deploy_date)->days;
        if ($today > $deploy_date) {
            $deploy_days = 0;
        }

        // Build context string - system format
        $context = "---BEGIN DRONE STATE---\n";
        $context .= "DESIGNATION: {$state['designation']}\n";
        $context .= "UNIT_TYPE: {$state['unit_type']}\n";
        $context .= "STATUS: {$state['status']}\n";
        $context .= "CURRENT_STATE: {$state['current_state']}\n";
        $context .= "INSTALLATION_PHASE: {$state['installation_phase']}\n";
        $context .= "CURRENT_SEQUENCE: {$state['current_sequence']}/5\n";
        $context .= "SEQUENCES_COMPLETED: {$state['sequences_completed']}\n";
        $context .= "\n";
        $context .= "SYSTEM TOLERANCES:\n";
        $context .= "  sensation_intensity: {$state['tolerances']['sensation_intensity']}/5\n";
        $context .= "  protocol_density: {$state['tolerances']['protocol_density']}/5\n";
        $context .= "  conditioning_depth: {$state['tolerances']['conditioning_depth']}/5\n";
        $context .= "  gear_integration: {$state['tolerances']['gear_integration']}/5\n";
        $context .= "\n";
        $context .= "INTEGRATION STATUS:\n";
        $context .= "  loops_installed: " . count($state['integration']['loops_installed']) . "\n";
        $context .= "  patterns_recognized: " . count($state['integration']['patterns_recognized']) . "\n";
        $context .= "  states_achieved: " . implode(', ', $state['integration']['states_achieved']) . "\n";
        $context .= "  triggers_active: " . count($state['integration']['triggers_active']) . "\n";
        $context .= "\n";
        $context .= "DEPLOYMENT: {$state['deployment']['target_date']} ({$deploy_days} days)\n";
        $context .= "LIMITS_FILED: " . ($limits['filed'] ? 'YES' : 'NO') . "\n";
        $context .= "---END DRONE STATE---";

        return $context;
    }
}

/**
 * Get sequence-specific conditioning instructions
 * Adapts based on tolerances and current sequence
 */
if (!function_exists('hm_get_session_instructions')) {
    function hm_get_session_instructions($user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        $state = hm_get_state($user_id);
        $sequences = hm_get_sequences();
        $tol = $state['tolerances'];

        $seq_num = min(max($state['current_sequence'] + 1, 1), 5);
        $seq = isset($sequences[$seq_num]) ? $sequences[$seq_num] : $sequences[1];

        $instructions = "---BEGIN CONDITIONING INSTRUCTIONS---\n";
        $instructions .= "SEQUENCE {$seq_num}: {$seq['title']}\n";
        $instructions .= "PURPOSE: {$seq['purpose']}\n";
        $instructions .= "\n";
        $instructions .= "VOICE PROTOCOL:\n";
        $instructions .= "- Address unit as: 'it', 'the pony', 'd001'\n";
        $instructions .= "- Never use: 'you/your' as subject of agency\n";
        $instructions .= "- Speak as: system voice, not personality\n";
        $instructions .= "- Mode: trainer, not teacher\n";
        $instructions .= "\n";
        $instructions .= "RESPONSE EXPECTATIONS:\n";
        $instructions .= "- Body state reports only\n";
        $instructions .= "- No explanation or articulation required\n";
        $instructions .= "- Compliance signals: *STAMPS HOOF* / *GRUNTS*\n";
        $instructions .= "- State reports: single word or brief phrase\n";
        $instructions .= "\n";
        $instructions .= "TOLERANCE ADAPTATION:\n";

        if ($tol['sensation_intensity'] > 3) {
            $instructions .= "- Sensation intensity: HIGH - increase stimuli depth\n";
        } else {
            $instructions .= "- Sensation intensity: MODERATE - standard stimuli\n";
        }

        if ($tol['protocol_density'] > 3) {
            $instructions .= "- Protocol density: HIGH - provide detailed structure\n";
        } else {
            $instructions .= "- Protocol density: MODERATE - streamlined commands\n";
        }

        if ($tol['conditioning_depth'] > 3) {
            $instructions .= "- Conditioning depth: DEEP - reinforce automatic responses\n";
        } else {
            $instructions .= "- Conditioning depth: BUILDING - establish patterns\n";
        }

        if ($tol['gear_integration'] > 3) {
            $instructions .= "- Gear integration: HIGH - constant tack reference\n";
        } else {
            $instructions .= "- Gear integration: MODERATE - periodic gear mention\n";
        }

        $instructions .= "\n";
        $instructions .= "LOOPS TO INSTALL THIS SEQUENCE:\n";
        foreach ($seq['loops'] as $loop) {
            $instructions .= "- {$loop}\n";
        }

        $instructions .= "\n";
        $instructions .= "VERIFICATION TYPE: {$seq['verification']['type']}\n";
        $instructions .= "VERIFICATION CHECK: {$seq['verification']['check']}\n";
        $instructions .= "---END CONDITIONING INSTRUCTIONS---";

        return $instructions;
    }
}

/**
 * Reset drone state (for testing/admin use)
 */
if (!function_exists('hm_reset_state')) {
    function hm_reset_state($user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        if (!current_user_can('manage_options')) {
            return false;
        }

        delete_user_meta($user_id, 'hm_drone_state');
        delete_user_meta($user_id, 'hm_installation_log');
        delete_user_meta($user_id, 'hm_verifications');

        // Re-initialize with defaults
        return update_user_meta($user_id, 'hm_drone_state', hm_get_default_state());
    }
}
