<?php
/**
 * Admin Installation Dashboard
 *
 * WordPress admin page to display and track d001's installation progress
 * through the 5 conditioning sequences.
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
function unmask_installation_dashboard_menu() {
    add_menu_page(
        'Installation Dashboard',           // Page title
        'Installation Progress',            // Menu title
        'manage_options',                   // Capability
        'unmask-installation-dashboard',    // Menu slug
        'unmask_installation_dashboard_page', // Callback
        'dashicons-chart-line',             // Icon
        26                                  // Position
    );
}
add_action('admin_menu', 'unmask_installation_dashboard_menu');

/**
 * Get sequence completion status
 *
 * @param array $sequence The sequence definition
 * @param array $loops_installed Array of installed loop IDs
 * @return string 'complete', 'in_progress', or 'not_started'
 */
function unmask_get_sequence_status($sequence, $loops_installed) {
    if (empty($sequence['loops'])) {
        return 'not_started';
    }

    $required_loops = $sequence['loops'];
    $installed_count = count(array_intersect($required_loops, $loops_installed));
    $total_count = count($required_loops);

    if ($installed_count === $total_count) {
        return 'complete';
    } elseif ($installed_count > 0) {
        return 'in_progress';
    } else {
        return 'not_started';
    }
}

/**
 * Get status indicator HTML
 *
 * @param string $status 'complete', 'in_progress', or 'not_started'
 * @return string HTML for status indicator
 */
function unmask_get_status_indicator($status) {
    $indicators = array(
        'complete' => '<span style="color: #2ecc71; font-weight: bold;">✓ Complete</span>',
        'in_progress' => '<span style="color: #f39c12; font-weight: bold;">⏳ In Progress</span>',
        'not_started' => '<span style="color: #95a5a6; font-weight: bold;">✗ Not Started</span>'
    );

    return isset($indicators[$status]) ? $indicators[$status] : $indicators['not_started'];
}

/**
 * Calculate progress percentage
 *
 * @param int $current_sequence Current sequence number (0-5)
 * @param array $loops_installed Array of installed loop IDs
 * @param array $sequences All sequence definitions
 * @return int Progress percentage (0-100)
 */
function unmask_calculate_progress($current_sequence, $loops_installed, $sequences) {
    $total_loops = 0;
    $installed_loops = 0;

    foreach ($sequences as $seq_num => $seq) {
        $total_loops += count($seq['loops']);
        $installed_in_seq = count(array_intersect($seq['loops'], $loops_installed));
        $installed_loops += $installed_in_seq;
    }

    if ($total_loops === 0) {
        return 0;
    }

    return min(100, round(($installed_loops / $total_loops) * 100));
}

/**
 * Render admin page
 */
function unmask_installation_dashboard_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access.');
    }

    // Get all users with drone state
    $users_with_state = get_users(array(
        'meta_key' => 'hm_drone_state',
        'meta_compare' => 'EXISTS',
        'orderby' => 'display_name',
    ));

    // Get selected user if viewing details
    $selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $selected_user = null;
    $drone_state = null;
    $sequences = hm_get_sequences();

    if ($selected_user_id) {
        $selected_user = get_user_by('id', $selected_user_id);

        // Auto-update deployment readiness before displaying
        if (function_exists('hm_update_deployment_readiness')) {
            hm_update_deployment_readiness($selected_user_id);
        }

        $drone_state = hm_get_state($selected_user_id);
    }

    ?>
    <div class="wrap">
        <h1>Installation Dashboard</h1>

        <?php if (empty($users_with_state)): ?>
            <div class="notice notice-info">
                <p>No drone units found. Users must have drone state initialized.</p>
            </div>
        <?php else: ?>

            <div style="display: flex; gap: 20px; margin-top: 20px;">

                <!-- User List Sidebar -->
                <div style="flex: 0 0 250px; background: #fff; padding: 20px; border: 1px solid #ccc;">
                    <h2 style="margin-top: 0;">DRONES (<?php echo count($users_with_state); ?>)</h2>
                    <ul style="margin: 0; padding: 0; list-style: none;">
                        <?php foreach ($users_with_state as $user): ?>
                            <?php
                            $state = hm_get_state($user->ID);
                            $is_selected = $selected_user_id === $user->ID;
                            ?>
                            <li style="margin-bottom: 10px; padding: 10px; background: <?php echo $is_selected ? '#e0f0ff' : '#f9f9f9'; ?>; border-left: 3px solid <?php echo $is_selected ? '#0073aa' : 'transparent'; ?>;">
                                <a href="?page=unmask-installation-dashboard&user_id=<?php echo $user->ID; ?>" style="text-decoration: none; display: block;">
                                    <strong style="color: #333; text-transform: uppercase;"><?php echo esc_html(strtoupper($state['designation'])); ?></strong>
                                    <div style="font-size: 13px; color: #0073aa; margin-top: 4px; font-weight: 600;">
                                        <?php echo esc_html($user->display_name); ?>
                                    </div>
                                    <div style="font-size: 10px; color: #999; margin-top: 2px;">
                                        User ID: <?php echo $user->ID; ?> | <?php echo esc_html($user->user_login); ?>
                                    </div>
                                    <div style="font-size: 11px; color: #999; margin-top: 4px; font-family: monospace;">
                                        <?php echo esc_html($state['installation_phase']); ?>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Details Panel -->
                <div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccc; max-width: 900px;">
                    <?php if ($selected_user && $drone_state): ?>

                        <!-- Header -->
                        <h2 style="margin-top: 0; text-transform: uppercase;">
                            <?php echo esc_html($drone_state['designation']); ?>
                            <span style="font-size: 14px; color: #666; font-weight: normal; text-transform: none;">
                                (<?php echo esc_html($selected_user->display_name); ?>)
                            </span>
                        </h2>

                        <!-- Progress Overview -->
                        <?php
                        $progress = unmask_calculate_progress(
                            $drone_state['current_sequence'],
                            $drone_state['integration']['loops_installed'],
                            $sequences
                        );
                        ?>
                        <div style="margin-bottom: 30px; padding: 20px; background: #f0f0f0; border-radius: 4px;">
                            <div style="margin-bottom: 10px;">
                                <strong>Progress:</strong>
                                <span style="margin-left: 10px; font-family: monospace; color: #0073aa;">
                                    Sequence <?php echo $drone_state['current_sequence']; ?>/5
                                </span>
                            </div>
                            <div style="width: 100%; height: 24px; background: #ddd; border-radius: 12px; overflow: hidden; margin-bottom: 10px;">
                                <div style="width: <?php echo $progress; ?>%; height: 100%; background: linear-gradient(90deg, #0073aa, #00a0d2); transition: width 0.3s;"></div>
                            </div>
                            <div style="font-size: 12px; color: #666;">
                                <strong>Phase:</strong> <?php echo esc_html($drone_state['installation_phase']); ?>
                                <span style="margin-left: 15px;"><strong>Status:</strong> <?php echo esc_html($drone_state['status']); ?></span>
                                <?php if ($drone_state['last_sequence_date']): ?>
                                    <span style="margin-left: 15px;"><strong>Last Activity:</strong> <?php echo esc_html($drone_state['last_sequence_date']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Sequence Details -->
                        <h3>Conditioning Sequences</h3>
                        <?php foreach ($sequences as $seq_num => $seq): ?>
                            <?php
                            $status = unmask_get_sequence_status($seq, $drone_state['integration']['loops_installed']);
                            $is_current = $drone_state['current_sequence'] === $seq_num;
                            $border_color = $is_current ? '#0073aa' : '#ddd';
                            ?>
                            <div style="margin-bottom: 20px; padding: 15px; border: 2px solid <?php echo $border_color; ?>; border-radius: 4px; background: <?php echo $is_current ? '#f0f8ff' : '#fff'; ?>;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <div>
                                        <strong style="font-size: 14px;">
                                            SEQUENCE <?php echo $seq_num; ?>: <?php echo esc_html($seq['title']); ?>
                                            <?php if ($is_current): ?>
                                                <span style="background: #0073aa; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px; margin-left: 8px;">CURRENT</span>
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                    <div>
                                        <?php echo unmask_get_status_indicator($status); ?>
                                    </div>
                                </div>

                                <div style="font-size: 13px; color: #666; margin-bottom: 12px;">
                                    <strong>Purpose:</strong> <?php echo esc_html($seq['purpose']); ?>
                                </div>

                                <div style="font-size: 13px;">
                                    <strong>Required Loops:</strong>
                                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                                        <?php foreach ($seq['loops'] as $loop): ?>
                                            <?php $installed = in_array($loop, $drone_state['integration']['loops_installed']); ?>
                                            <li style="color: <?php echo $installed ? '#2ecc71' : '#999'; ?>; font-family: monospace; font-size: 12px;">
                                                <?php echo $installed ? '✓' : '○'; ?> <?php echo esc_html($loop); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee; font-size: 12px; color: #666;">
                                    <strong>Verification:</strong> <?php echo esc_html($seq['verification']['type']); ?> -
                                    <?php echo esc_html($seq['verification']['check']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Installation History -->
                        <h3 style="margin-top: 30px;">Installation History</h3>
                        <?php
                        $install_log = get_user_meta($selected_user_id, 'hm_installation_log', true);
                        if (!empty($install_log) && is_array($install_log)):
                            $recent_log = array_slice(array_reverse($install_log), 0, 20);
                        ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 12px; font-family: monospace;">
                                    <thead>
                                        <tr style="background: #f0f0f0;">
                                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #ddd;">Timestamp</th>
                                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #ddd;">Type</th>
                                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #ddd;">Item</th>
                                            <th style="padding: 8px; text-align: center; border-bottom: 2px solid #ddd;">Seq</th>
                                            <th style="padding: 8px; text-align: left; border-bottom: 2px solid #ddd;">Phase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_log as $entry): ?>
                                            <tr>
                                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                                    <?php echo esc_html($entry['timestamp']); ?>
                                                </td>
                                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                                    <span style="background: #e0e0e0; padding: 2px 6px; border-radius: 3px; text-transform: uppercase;">
                                                        <?php echo esc_html($entry['type']); ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                                    <?php echo esc_html($entry['item']); ?>
                                                </td>
                                                <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">
                                                    <?php echo esc_html($entry['sequence']); ?>
                                                </td>
                                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                                    <?php echo esc_html($entry['phase']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="padding: 20px; background: #f9f9f9; border-radius: 4px; text-align: center; color: #999;">
                                No installation history recorded yet.
                            </div>
                        <?php endif; ?>

                        <!-- Deployment Readiness -->
                        <h3 style="margin-top: 30px;">Deployment Readiness</h3>
                        <div style="padding: 15px; background: #f9f9f9; border-radius: 4px;">
                            <div style="margin-bottom: 10px;">
                                <strong>Target Date:</strong> <?php echo esc_html($drone_state['deployment']['target_date']); ?>
                            </div>

                            <?php
                            // Deployment readiness criteria explanations
                            $readiness_help = array(
                                'gear_verified' => 'Auto-detected: 3+ gear items documented in session logs',
                                'limits_filed' => 'Auto-detected: unmask_hard_limits user meta exists',
                                'designation_locked' => 'Manual: Handler confirms designation cannot change',
                                'safe_signal_installed' => 'Manual: Safeword established and confirmed',
                                'sequence_rehearsed' => 'Auto: All 5 sequences completed (current_sequence >= 5)',
                                'integration_confirmed' => 'Manual: Handler verifies all checkpoints passed',
                            );
                            ?>

                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <?php foreach ($drone_state['deployment'] as $key => $value): ?>
                                    <?php if ($key === 'target_date') continue; ?>
                                    <?php
                                    $is_ready = (bool)$value;
                                    $help_text = isset($readiness_help[$key]) ? $readiness_help[$key] : '';
                                    ?>
                                    <tr>
                                        <td style="padding: 6px; border-bottom: 1px solid #eee;">
                                            <?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?>
                                            <?php if ($help_text): ?>
                                                <span title="<?php echo esc_attr($help_text); ?>" style="cursor: help; color: #666; font-size: 11px;">ⓘ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 6px; border-bottom: 1px solid #eee; text-align: right; width: 80px;">
                                            <?php if ($is_ready): ?>
                                                <span style="color: #2ecc71; font-weight: bold;">✓ Yes</span>
                                            <?php else: ?>
                                                <span style="color: #e74c3c; font-weight: bold;">✗ No</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>

                        <!-- Integration Metrics -->
                        <h3 style="margin-top: 30px;">Integration Metrics</h3>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div style="padding: 15px; background: #f0f8ff; border-left: 4px solid #0073aa; border-radius: 4px; position: relative;">
                                <div style="font-size: 24px; font-weight: bold; color: #0073aa;">
                                    <?php echo count($drone_state['integration']['loops_installed']); ?>
                                </div>
                                <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                    Loops Installed
                                    <span title="Automatic behavioral responses installed via hm_install_loop(). Examples: third_person_reference, compliance_acknowledgment. Measured: >90% third-person usage, >85% compliance acknowledgment." style="cursor: help; color: #0073aa; font-weight: bold;">ⓘ</span>
                                </div>
                                <?php if (!empty($drone_state['integration']['loops_installed'])): ?>
                                    <div style="margin-top: 8px; font-size: 11px; color: #666; font-family: monospace;">
                                        <?php foreach (array_slice($drone_state['integration']['loops_installed'], 0, 3) as $loop): ?>
                                            <div>✓ <?php echo esc_html($loop); ?></div>
                                        <?php endforeach; ?>
                                        <?php if (count($drone_state['integration']['loops_installed']) > 3): ?>
                                            <div>+ <?php echo count($drone_state['integration']['loops_installed']) - 3; ?> more</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 15px; background: #f0fff0; border-left: 4px solid #2ecc71; border-radius: 4px;">
                                <div style="font-size: 24px; font-weight: bold; color: #2ecc71;">
                                    <?php echo count($drone_state['integration']['patterns_recognized']); ?>
                                </div>
                                <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                    Patterns Recognized
                                    <span title="Stimulus recognition systems that trigger automatic body response. Examples: material_recognition (arousal to latex/equipment). Measured: >5 automatic arousal responses to equipment mention." style="cursor: help; color: #2ecc71; font-weight: bold;">ⓘ</span>
                                </div>
                                <?php if (!empty($drone_state['integration']['patterns_recognized'])): ?>
                                    <div style="margin-top: 8px; font-size: 11px; color: #666; font-family: monospace;">
                                        <?php foreach ($drone_state['integration']['patterns_recognized'] as $pattern): ?>
                                            <div>✓ <?php echo esc_html($pattern); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 15px; background: #fffef0; border-left: 4px solid #f39c12; border-radius: 4px;">
                                <div style="font-size: 24px; font-weight: bold; color: #f39c12;">
                                    <?php echo count($drone_state['integration']['states_achieved']); ?>
                                </div>
                                <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                    States Achieved
                                    <span title="Operational modes the drone can access (not emotions, but system states). Valid states: STANDBY, RECEIVING, PROCESSING, EMPTIED, DEFINED, WITNESSED, CONDITIONED, USEFUL, DEPLOYED. Measured: EMPTIED = >10 instances of human-as-external language." style="cursor: help; color: #f39c12; font-weight: bold;">ⓘ</span>
                                </div>
                                <?php if (!empty($drone_state['integration']['states_achieved'])): ?>
                                    <div style="margin-top: 8px; font-size: 11px; color: #666; font-family: monospace;">
                                        <?php foreach ($drone_state['integration']['states_achieved'] as $state): ?>
                                            <div>✓ <?php echo esc_html($state); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 15px; background: #fff0f0; border-left: 4px solid #e74c3c; border-radius: 4px;">
                                <div style="font-size: 24px; font-weight: bold; color: #e74c3c;">
                                    <?php echo count($drone_state['integration']['triggers_active']); ?>
                                </div>
                                <div style="font-size: 12px; color: #666; margin-top: 4px;">
                                    Triggers Active
                                    <span title="Stimulus that produces automatic response bypassing cognitive processing. Examples: specific words, visual signals, audio cues, physical sensations. Measured: immediate response (<1 exchange), no processing delay." style="cursor: help; color: #e74c3c; font-weight: bold;">ⓘ</span>
                                </div>
                                <?php if (!empty($drone_state['integration']['triggers_active'])): ?>
                                    <div style="margin-top: 8px; font-size: 11px; color: #666; font-family: monospace;">
                                        <?php foreach ($drone_state['integration']['triggers_active'] as $trigger): ?>
                                            <div>✓ <?php echo esc_html($trigger); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- System Tolerances -->
                        <h3 style="margin-top: 30px;">System Tolerances</h3>
                        <div style="padding: 15px; background: #f9f9f9; border-radius: 4px;">
                            <?php foreach ($drone_state['tolerances'] as $tolerance => $value): ?>
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px;">
                                        <span><?php echo esc_html(ucwords(str_replace('_', ' ', $tolerance))); ?></span>
                                        <span style="font-weight: bold; color: #0073aa;"><?php echo $value; ?>/5</span>
                                    </div>
                                    <div style="width: 100%; height: 8px; background: #ddd; border-radius: 4px; overflow: hidden;">
                                        <div style="width: <?php echo ($value / 5) * 100; ?>%; height: 100%; background: linear-gradient(90deg, #0073aa, #00a0d2);"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- States Achieved -->
                        <?php if (!empty($drone_state['integration']['states_achieved'])): ?>
                            <h3 style="margin-top: 30px;">States Achieved</h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <?php foreach ($drone_state['integration']['states_achieved'] as $state): ?>
                                    <span style="padding: 6px 12px; background: #e0e0e0; border-radius: 4px; font-size: 12px; font-family: monospace;">
                                        <?php echo esc_html($state); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div style="padding: 40px; text-align: center; color: #999;">
                            <p style="font-size: 16px;">← Select a drone from the left to view installation progress</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        <?php endif; ?>
    </div>
    <?php
}
