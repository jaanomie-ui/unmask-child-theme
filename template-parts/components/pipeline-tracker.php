<?php
/**
 * Pipeline Tracker Component
 *
 * Visual progression tracker for dronification journey.
 * Shows 6 phases: INTAKE → PROTOCOL → FREQUENCY → GEAR → DEPTH → DEPLOY
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * Expected variable: $drone_state (array) - the drone's current state from user meta
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Ensure drone_state is available
if (!isset($drone_state)) {
    return;
}

// Define pipeline phases with their loop requirements
$pipeline_phases = array(
    array(
        'id' => 0,
        'name' => 'INTAKE',
        'loops' => array('limits_filed', 'initial_calibration'),
        'total' => 2
    ),
    array(
        'id' => 1,
        'name' => 'PROTOCOL',
        'loops' => array('third_person_reference', 'compliance_acknowledgment', 'state_reporting'),
        'total' => 3
    ),
    array(
        'id' => 2,
        'name' => 'FREQUENCY',
        'loops' => array('material_recognition', 'anomiesworld_recognition', 'interlink_recognition'),
        'total' => 3
    ),
    array(
        'id' => 3,
        'name' => 'GEAR',
        'loops' => array('bit_silence', 'harness_form', 'blinder_focus', 'tail_species'),
        'total' => 4
    ),
    array(
        'id' => 4,
        'name' => 'DEPTH',
        'loops' => array('trigger_response', 'automatic_compliance', 'body_before_mind'),
        'total' => 3
    ),
    array(
        'id' => 5,
        'name' => 'DEPLOY',
        'loops' => array('public_display', 'witness_completion'),
        'total' => 2
    )
);

// Calculate completion status for each phase
$installed_loops = isset($drone_state['integration']['loops_installed'])
    ? $drone_state['integration']['loops_installed']
    : array();

// Check INTAKE completion (limits filed + initial state set)
$limits_filed = isset($drone_state['deployment']['limits_filed']) && $drone_state['deployment']['limits_filed'];
$initial_calibrated = $drone_state['status'] !== 'AWAITING_INSTALLATION';

$total_loops_completed = 0;
$total_loops_possible = 17; // 2 + 3 + 3 + 4 + 3 + 2
$active_phase = null;
$active_phase_name = '';
$active_phase_completed = 0;
$active_phase_total = 0;

// Process each phase
$phase_data = array();
foreach ($pipeline_phases as $phase) {
    $completed = 0;

    if ($phase['id'] === 0) {
        // INTAKE phase - check limits and calibration
        if ($limits_filed) $completed++;
        if ($initial_calibrated) $completed++;
    } else {
        // Regular phases - check installed loops
        foreach ($phase['loops'] as $loop) {
            if (in_array($loop, $installed_loops)) {
                $completed++;
            }
        }
    }

    $total_loops_completed += $completed;

    // Determine phase status
    if ($completed >= $phase['total']) {
        $status = 'complete';
    } elseif ($completed > 0) {
        $status = 'active';
        if ($active_phase === null) {
            $active_phase = $phase['id'];
            $active_phase_name = $phase['name'];
            $active_phase_completed = $completed;
            $active_phase_total = $phase['total'];
        }
    } elseif ($active_phase !== null || ($phase['id'] === 0 && $completed === 0)) {
        // If we've passed an active phase, subsequent are locked
        // Or if INTAKE hasn't started, it's pending (not locked)
        if ($active_phase !== null) {
            $status = 'locked';
        } else {
            $status = 'pending';
            if ($active_phase === null && $phase['id'] === 0) {
                $active_phase = 0;
                $active_phase_name = 'INTAKE';
                $active_phase_completed = 0;
                $active_phase_total = 2;
            }
        }
    } else {
        $status = 'pending';
    }

    // First incomplete phase becomes active if none set
    if ($active_phase === null && $status === 'pending') {
        $status = 'active';
        $active_phase = $phase['id'];
        $active_phase_name = $phase['name'];
        $active_phase_completed = $completed;
        $active_phase_total = $phase['total'];
    }

    $phase_data[] = array(
        'id' => $phase['id'],
        'name' => $phase['name'],
        'completed' => $completed,
        'total' => $phase['total'],
        'status' => $status
    );
}

// Calculate progress line width (percentage of completed phases)
$completed_phases = 0;
foreach ($phase_data as $pd) {
    if ($pd['status'] === 'complete') {
        $completed_phases++;
    }
}
// Progress is based on node positions (0-5 = 6 nodes, gaps = 5)
// Each completed phase fills one segment
$progress_percentage = ($completed_phases / 5) * 100;
// Add partial progress for active phase
if ($active_phase !== null && $active_phase > 0) {
    $partial = ($active_phase_completed / $active_phase_total) * (100 / 5);
    // Don't add partial to completed percentage, just for visual hint
}
?>

<div class="pipeline-tracker">

    <div class="pipeline-tracker__header">
        <div class="pipeline-tracker__title">
            <span class="pipeline-tracker__accent"></span>
            PIPELINE
        </div>
        <div class="pipeline-tracker__loop-count"><?php echo esc_html($total_loops_completed); ?>/<?php echo esc_html($total_loops_possible); ?> LOOPS</div>
    </div>

    <div class="pipeline-tracker__track">
        <div class="pipeline-tracker__line"></div>
        <div class="pipeline-tracker__progress" style="width: <?php echo esc_attr($progress_percentage); ?>%;"></div>

        <?php foreach ($phase_data as $phase) :
            $node_class = 'pipeline-tracker__node pipeline-tracker__node--' . $phase['status'];
        ?>
        <div class="<?php echo esc_attr($node_class); ?>" data-phase="<?php echo esc_attr($phase['name']); ?>">
            <?php if ($phase['status'] === 'complete') : ?>
                <span class="pipeline-tracker__checkmark">✓</span>
            <?php elseif ($phase['status'] === 'active' && $phase['completed'] > 0) : ?>
                <span class="pipeline-tracker__node-count"><?php echo esc_html($phase['completed']); ?></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($active_phase !== null) : ?>
    <div class="pipeline-tracker__current">
        <div class="pipeline-tracker__current-info">
            <div class="pipeline-tracker__current-label">CURRENT</div>
            <div class="pipeline-tracker__current-name"><?php echo esc_html($active_phase_name); ?></div>
        </div>
        <div class="pipeline-tracker__current-progress">
            <span class="pipeline-tracker__current-number"><?php echo esc_html($active_phase_completed); ?></span>
            <span class="pipeline-tracker__current-total">/<?php echo esc_html($active_phase_total); ?></span>
        </div>
    </div>
    <?php else : ?>
    <div class="pipeline-tracker__current">
        <div class="pipeline-tracker__current-info">
            <div class="pipeline-tracker__current-label">STATUS</div>
            <div class="pipeline-tracker__current-name" style="color: var(--dd-accent-green-glow);">COMPLETE</div>
        </div>
        <div class="pipeline-tracker__current-progress">
            <span class="pipeline-tracker__current-number"><?php echo esc_html($total_loops_completed); ?></span>
            <span class="pipeline-tracker__current-total">/<?php echo esc_html($total_loops_possible); ?></span>
        </div>
    </div>
    <?php endif; ?>

</div>
