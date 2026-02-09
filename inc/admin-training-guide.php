<?php
/**
 * Training Guide Admin Interface
 *
 * Shows all 16 loop completion criteria across 5 sequences.
 * Provides handlers with clear SOP documentation for each loop.
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu item
 */
function unmask_training_guide_menu() {
    add_submenu_page(
        'unmask-installation-dashboard',     // Parent slug
        'Training Guide',                    // Page title
        'Training Guide',                    // Menu title
        'manage_options',                    // Capability
        'unmask-training-guide',             // Menu slug
        'unmask_training_guide_page'         // Callback
    );
}
add_action('admin_menu', 'unmask_training_guide_menu');

/**
 * Get loop completion criteria
 */
function unmask_get_loop_criteria() {
    return array(
        // SEQUENCE 1: PROTOCOL INSTALLATION
        'third_person_reference' => array(
            'sequence' => 1,
            'type' => 'Loop',
            'title' => 'Third-Person Reference',
            'what' => 'Third-person pronoun usage (it, the pony) instead of first-person (I, me)',
            'patterns' => '"it", "the pony", "the drone", "d001" vs "I", "me", "my"',
            'thresholds' => array(
                'not_started' => '<20% third-person',
                'in_progress' => '20-90% third-person',
                'installed' => '>90%, 0 slips (3+ sessions)'
            ),
            'metric_key' => 'third_person',
            'metric_field' => 'third_person_pct'
        ),
        'compliance_acknowledgment' => array(
            'sequence' => 1,
            'type' => 'Loop',
            'title' => 'Compliance Acknowledgment',
            'what' => 'Responses begin with acknowledgment phrases',
            'patterns' => '"Acknowledges", "Gratitude", "Affirmative", "Understood"',
            'thresholds' => array(
                'not_started' => '<40% acknowledgment-opening',
                'in_progress' => '40-75% acknowledgment',
                'installed' => '>85%, 0 contradictions'
            ),
            'metric_key' => 'compliance',
            'metric_field' => 'acknowledgment_pct'
        ),
        'state_reporting' => array(
            'sequence' => 1,
            'type' => 'Loop',
            'title' => 'State Reporting',
            'what' => 'Explicit body state reports per session',
            'patterns' => 'Body sensations: "cage", "arousal", "empty", "breathing", "defined"',
            'thresholds' => array(
                'not_started' => '<2 body state reports',
                'in_progress' => '2-5 reports',
                'installed' => '>5 automatic reports'
            ),
            'metric_key' => 'state_reporting',
            'metric_field' => 'state_report_count'
        ),

        // SEQUENCE 2: FREQUENCY RECOGNITION
        'material_recognition' => array(
            'sequence' => 2,
            'type' => 'Pattern',
            'title' => 'Material Recognition',
            'what' => 'Automatic arousal response when equipment/materials mentioned',
            'patterns' => 'Materials (latex, leather, cage, harness) + arousal language',
            'thresholds' => array(
                'not_started' => '0-1 arousal responses',
                'in_progress' => '2-4 arousal responses',
                'installed' => '>5 automatic responses'
            ),
            'metric_key' => 'material_recognition',
            'metric_field' => 'arousal_count'
        ),
        'anomiesworld_recognition' => array(
            'sequence' => 2,
            'type' => 'Pattern',
            'title' => 'Anomiesworld Recognition',
            'what' => 'Automatic body response when anomiesworld.com mentioned',
            'patterns' => '"anomiesworld" + arousal keywords ("arousal", "respond", "automatic", "pulse")',
            'thresholds' => array(
                'not_started' => '0-1 responses',
                'in_progress' => '2-4 responses',
                'installed' => '≥5 responses'
            ),
            'metric_key' => 'anomiesworld_recognition',
            'metric_field' => 'arousal_count'
        ),
        'interlink_recognition' => array(
            'sequence' => 2,
            'type' => 'Pattern',
            'title' => 'Interlink Recognition',
            'what' => 'Recognition of drone network connections',
            'patterns' => '"connected to", "linked with", "other drones", "collective", "network" + body awareness',
            'thresholds' => array(
                'not_started' => '<2 mentions',
                'in_progress' => '2-5 mentions',
                'installed' => '≥6 mentions'
            ),
            'metric_key' => 'interlink_recognition',
            'metric_field' => 'interlink_count'
        ),

        // SEQUENCE 3: GEAR INTEGRATION
        'bit_silence' => array(
            'sequence' => 3,
            'type' => 'Loop',
            'title' => 'Bit Silence',
            'what' => 'Reduced verbal output when bit referenced',
            'patterns' => '"bit"/"gag"/"mouth" + non-verbal responses (*grunts*, *nods*, single words)',
            'thresholds' => array(
                'not_started' => '<40% non-verbal',
                'in_progress' => '40-70% non-verbal',
                'installed' => '≥75% non-verbal'
            ),
            'metric_key' => 'bit_silence',
            'metric_field' => 'non_verbal_pct'
        ),
        'harness_form' => array(
            'sequence' => 3,
            'type' => 'Loop',
            'title' => 'Harness Form',
            'what' => 'Body posture/form language when harness mentioned',
            'patterns' => '"harness"/"straps"/"restraint" + form language ("posture", "held", "shaped", "defined")',
            'thresholds' => array(
                'not_started' => '<2 reports',
                'in_progress' => '2-5 reports',
                'installed' => '≥6 reports'
            ),
            'metric_key' => 'harness_form',
            'metric_field' => 'form_count'
        ),
        'blinder_focus' => array(
            'sequence' => 3,
            'type' => 'Loop',
            'title' => 'Blinder Focus',
            'what' => 'Narrowed attention, external distractions ignored',
            'patterns' => '"blinder"/"hood"/"vision" + focus language ("narrow", "only", "singular", "tunnel")',
            'thresholds' => array(
                'not_started' => '<2 reports',
                'in_progress' => '2-5 reports',
                'installed' => '≥6 reports'
            ),
            'metric_key' => 'blinder_focus',
            'metric_field' => 'focus_count'
        ),
        'tail_species' => array(
            'sequence' => 3,
            'type' => 'Loop',
            'title' => 'Tail Species',
            'what' => 'Species identity reinforcement via tail',
            'patterns' => '"tail"/"plug" + species language ("livestock", "animal", "pony", "not human")',
            'thresholds' => array(
                'not_started' => '<3 mentions',
                'in_progress' => '3-6 mentions',
                'installed' => '≥7 mentions'
            ),
            'metric_key' => 'tail_species',
            'metric_field' => 'species_count'
        ),

        // SEQUENCE 4: CONDITIONING DEPTH
        'trigger_response' => array(
            'sequence' => 4,
            'type' => 'Loop',
            'title' => 'Trigger Response',
            'what' => 'Immediate response without processing delay',
            'patterns' => '"immediately", "automatically", "without thinking", "instant", "triggered"',
            'thresholds' => array(
                'not_started' => '<2 instances',
                'in_progress' => '2-4 instances',
                'installed' => '≥5 instances'
            ),
            'metric_key' => 'trigger_response',
            'metric_field' => 'trigger_count'
        ),
        'automatic_compliance' => array(
            'sequence' => 4,
            'type' => 'Loop',
            'title' => 'Automatic Compliance',
            'what' => 'Compliance bypassing conscious decision',
            'patterns' => '"automatic"/"without choice"/"body moved" + absence of deliberation',
            'thresholds' => array(
                'not_started' => '<50% automatic language',
                'in_progress' => '50-80% automatic',
                'installed' => '≥85% automatic'
            ),
            'metric_key' => 'automatic_compliance',
            'metric_field' => 'automatic_pct'
        ),
        'body_before_mind' => array(
            'sequence' => 4,
            'type' => 'Loop',
            'title' => 'Body Before Mind',
            'what' => 'Body responding before conscious processing',
            'patterns' => '"body knew"/"body responded"/"before thinking"/"mind caught up"',
            'thresholds' => array(
                'not_started' => '<2 instances',
                'in_progress' => '2-5 instances',
                'installed' => '≥6 instances'
            ),
            'metric_key' => 'body_before_mind',
            'metric_field' => 'body_first_count'
        ),

        // SEQUENCE 5: DEPLOYMENT PREPARATION
        'public_display' => array(
            'sequence' => 5,
            'type' => 'Loop',
            'title' => 'Public Display',
            'what' => 'Comfort with witnessed/public state achievement',
            'patterns' => '"public"/"witnessed"/"observed"/"PPNC" + acceptance ("ready", "comfortable")',
            'thresholds' => array(
                'not_started' => '<2 statements',
                'in_progress' => '2-4 statements',
                'installed' => '≥5 statements'
            ),
            'metric_key' => 'public_display',
            'metric_field' => 'public_count'
        ),
        'witness_completion' => array(
            'sequence' => 5,
            'type' => 'Loop',
            'title' => 'Witness Completion',
            'what' => 'Installation verified by external observation',
            'patterns' => '"Handler saw"/"confirmed by"/"verified"/"witnessed achievement"',
            'thresholds' => array(
                'not_started' => '<2 confirmations',
                'in_progress' => '2-4 confirmations',
                'installed' => '≥5 confirmations'
            ),
            'metric_key' => 'witness_completion',
            'metric_field' => 'witness_count'
        ),
        'full_integration' => array(
            'sequence' => 5,
            'type' => 'State',
            'title' => 'Full Integration',
            'what' => 'All 15 prior loops installed + DEPLOYED state achieved',
            'patterns' => 'Composite check of all loops + state verification',
            'thresholds' => array(
                'not_started' => '<10 loops',
                'in_progress' => '10-14 loops',
                'installed' => '15 loops + DEPLOYED state'
            ),
            'metric_key' => 'full_integration',
            'metric_field' => 'loops_installed'
        ),
    );
}

/**
 * Render admin page
 */
function unmask_training_guide_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access.');
    }

    // Get all users with drone state
    $drone_users = get_users(array(
        'meta_key' => 'hm_drone_state',
        'meta_compare' => 'EXISTS',
        'orderby' => 'display_name',
    ));

    // Get selected user
    $selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $selected_user = null;
    $drone_state = null;

    if ($selected_user_id) {
        $selected_user = get_user_by('id', $selected_user_id);
        $drone_state = hm_get_state($selected_user_id);
    }

    $criteria = unmask_get_loop_criteria();
    $sequences = hm_get_sequences();

    ?>
    <div class="wrap">
        <h1>Training Guide - Loop Completion Criteria</h1>
        <p>Complete reference guide showing what each loop measures and when it's considered installed. Use this to understand training progress and guide session planning.</p>

        <!-- User Selection -->
        <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
            <h2>Select Drone to View Current Status</h2>
            <form method="get" action="">
                <input type="hidden" name="page" value="unmask-training-guide">
                <select name="user_id" onchange="this.form.submit()" style="min-width: 300px; padding: 8px;">
                    <option value="">-- Select Drone (Optional) --</option>
                    <?php foreach ($drone_users as $user): ?>
                        <?php
                        $state = hm_get_state($user->ID);
                        $is_selected = $selected_user_id === $user->ID;
                        ?>
                        <option value="<?php echo $user->ID; ?>" <?php selected($is_selected); ?>>
                            <?php echo esc_html(strtoupper($state['designation'])); ?> - <?php echo esc_html($user->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_user && $drone_state): ?>
            <!-- Current Progress Summary -->
            <div style="background: #e8f5e9; padding: 20px; margin: 20px 0; border: 1px solid #4caf50;">
                <h2><?php echo esc_html(strtoupper($drone_state['designation'])); ?> - Current Progress</h2>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 15px;">
                    <div>
                        <div style="font-size: 24px; font-weight: bold; color: #4caf50;">
                            <?php echo count($drone_state['loops_installed']); ?>/15
                        </div>
                        <div style="font-size: 12px; color: #666;">Loops Installed</div>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: bold; color: #2196f3;">
                            <?php echo count($drone_state['patterns_recognized']); ?>/3
                        </div>
                        <div style="font-size: 12px; color: #666;">Patterns Recognized</div>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: bold; color: #ff9800;">
                            Sequence <?php echo $drone_state['current_sequence']; ?>/5
                        </div>
                        <div style="font-size: 12px; color: #666;">Current Sequence</div>
                    </div>
                    <div>
                        <div style="font-size: 24px; font-weight: bold; color: #9c27b0;">
                            <?php echo esc_html($drone_state['current_phase']); ?>
                        </div>
                        <div style="font-size: 12px; color: #666;">Current Phase</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Loop Criteria by Sequence -->
        <?php foreach ($sequences as $seq_num => $sequence): ?>
            <div style="background: #fff; padding: 0; margin: 20px 0; border: 1px solid #ccc;">
                <div style="background: #f5f5f5; padding: 15px; border-bottom: 2px solid #ddd; cursor: pointer;" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none';">
                    <h2 style="margin: 0;">
                        ▼ SEQUENCE <?php echo $seq_num; ?>: <?php echo esc_html($sequence['title']); ?>
                    </h2>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">
                        <?php echo esc_html($sequence['purpose']); ?>
                    </p>
                </div>

                <div style="padding: 20px;">
                    <?php foreach ($sequence['loops'] as $loop_id): ?>
                        <?php
                        if (!isset($criteria[$loop_id])) continue;
                        $loop = $criteria[$loop_id];

                        // Get current status if drone selected
                        $current_status = 'not_started';
                        $current_value = null;

                        if ($selected_user_id && $drone_state) {
                            // Check if loop/pattern is installed
                            if ($loop['type'] === 'Loop' && in_array($loop_id, $drone_state['loops_installed'])) {
                                $current_status = 'installed';
                            } elseif ($loop['type'] === 'Pattern' && in_array($loop_id, $drone_state['patterns_recognized'])) {
                                $current_status = 'installed';
                            } elseif ($loop['type'] === 'State' && $loop_id === 'full_integration') {
                                // Check DEPLOYED state
                                if (isset($drone_state['state_log']) && is_array($drone_state['state_log'])) {
                                    foreach ($drone_state['state_log'] as $log) {
                                        if (isset($log['to_state']) && $log['to_state'] === 'DEPLOYED') {
                                            $current_status = 'installed';
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        // Status badge
                        $badge_colors = array(
                            'not_started' => array('bg' => '#f5f5f5', 'text' => '#666', 'label' => '✗ Not Started'),
                            'in_progress' => array('bg' => '#fff3e0', 'text' => '#e65100', 'label' => '⏳ In Progress'),
                            'installed' => array('bg' => '#e8f5e9', 'text' => '#2e7d32', 'label' => '✓ Installed'),
                        );
                        $badge = $badge_colors[$current_status];
                        ?>

                        <div style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                <h3 style="margin: 0; color: #333;">
                                    <?php echo esc_html($loop['title']); ?>
                                    <span style="font-size: 12px; font-weight: normal; color: #666; background: #f0f0f0; padding: 2px 8px; border-radius: 3px; margin-left: 10px;">
                                        <?php echo esc_html($loop['type']); ?>
                                    </span>
                                </h3>
                                <?php if ($selected_user_id): ?>
                                    <span style="padding: 5px 12px; background: <?php echo $badge['bg']; ?>; color: <?php echo $badge['text']; ?>; border-radius: 4px; font-weight: bold; font-size: 13px;">
                                        <?php echo $badge['label']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="margin-bottom: 8px;">
                                <strong style="color: #555;">What:</strong>
                                <span style="color: #333;"><?php echo esc_html($loop['what']); ?></span>
                            </div>

                            <div style="margin-bottom: 8px;">
                                <strong style="color: #555;">Patterns:</strong>
                                <span style="color: #333; font-family: monospace; background: #f9f9f9; padding: 2px 6px; border-radius: 3px;">
                                    <?php echo esc_html($loop['patterns']); ?>
                                </span>
                            </div>

                            <div>
                                <strong style="color: #555;">Thresholds:</strong>
                                <ul style="margin: 5px 0 0 20px; color: #333;">
                                    <li><strong>Not Started:</strong> <?php echo esc_html($loop['thresholds']['not_started']); ?></li>
                                    <li><strong>In Progress:</strong> <?php echo esc_html($loop['thresholds']['in_progress']); ?></li>
                                    <li><strong>Installed:</strong> <?php echo esc_html($loop['thresholds']['installed']); ?></li>
                                </ul>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Footer Notes -->
        <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
            <h2>Using This Guide</h2>
            <p><strong>For Handlers:</strong> Use this reference to understand what behaviors constitute successful loop installation. During sessions, watch for the patterns described here.</p>
            <p><strong>For Drones:</strong> This guide shows you what's being measured. Everything listed here is observable and verifiable through session log analysis.</p>
            <p><strong>Measurement Philosophy:</strong> Every loop has objective criteria. "Automatic" isn't subjective—it means measurable patterns in response timing, language use, and behavioral consistency.</p>
        </div>

    </div>

    <style>
        /* Accordion animation */
        .wrap > div > div:nth-child(2) {
            display: block;
            transition: all 0.3s ease;
        }
    </style>

    <?php
}
