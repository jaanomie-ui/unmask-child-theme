<?php
/**
 * Pipeline Tracker Component
 *
 * Visual progression tracker for dronification journey.
 * Shows 5 phases: INTAKE → PROTOCOL → FREQUENCY → DEPTH → DEPLOY
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

// Define pipeline phases mapped to sequences
// Phase calculation now based on current_sequence instead of individual loop checks
$pipeline_phases = array(
    array(
        'id' => 0,
        'name' => 'INTAKE',
        'sequence' => 0,  // Pre-sequence (limits + calibration)
        'total' => 2
    ),
    array(
        'id' => 1,
        'name' => 'PROTOCOL',
        'sequence' => 1,  // Sequence 1: Protocol Installation
        'total' => 3
    ),
    array(
        'id' => 2,
        'name' => 'FREQUENCY',
        'sequence' => 2,  // Sequence 2: Frequency Recognition
        'total' => 4
    ),
    array(
        'id' => 3,
        'name' => 'PERFORMANCE',
        'sequence' => 3,  // Sequence 3: Performance Preparation
        'total' => 4
    ),
    array(
        'id' => 4,
        'name' => 'MATERIAL PREP',
        'sequence' => 4,  // Sequence 4: Material World Preparation
        'total' => 3
    ),
    array(
        'id' => 5,
        'name' => 'DEPLOY',
        'sequence' => 5,  // Sequence 5: Deployment Preparation
        'total' => 3
    )
);

// Calculate completion based on current_sequence from drone state
$current_sequence = isset($drone_state['current_sequence']) ? (int)$drone_state['current_sequence'] : 0;
$installed_loops = isset($drone_state['integration']['loops_installed'])
    ? $drone_state['integration']['loops_installed']
    : array();

// Get sequence definitions to calculate actual loop counts
$all_sequences = function_exists('hm_get_sequences') ? hm_get_sequences() : array();

// Check INTAKE completion (limits filed + initial state set)
$limits_filed = isset($drone_state['deployment']['limits_filed']) && $drone_state['deployment']['limits_filed'];
$initial_calibrated = $drone_state['status'] !== 'AWAITING_INSTALLATION';

// Calculate total possible loops from all sequences
$total_loops_possible = 2; // INTAKE: limits + calibration
foreach ($all_sequences as $seq) {
    $total_loops_possible += count($seq['loops']);
}

$total_loops_completed = 0;
$active_phase = null;
$active_phase_name = '';
$active_phase_completed = 0;
$active_phase_total = 0;

// Process each phase
$phase_data = array();
foreach ($pipeline_phases as $phase) {
    $completed = 0;

    if ($phase['sequence'] === 0) {
        // INTAKE phase - check limits and calibration
        if ($limits_filed) $completed++;
        if ($initial_calibrated) $completed++;
    } else {
        // Regular phases - check if sequence is completed or in progress
        if ($current_sequence > $phase['sequence']) {
            // Sequence fully completed
            if (isset($all_sequences[$phase['sequence']])) {
                $completed = count($all_sequences[$phase['sequence']]['loops']);
            } else {
                $completed = $phase['total'];
            }
        } elseif ($current_sequence === $phase['sequence']) {
            // Current sequence - count installed loops from this sequence
            if (isset($all_sequences[$phase['sequence']])) {
                $seq_loops = $all_sequences[$phase['sequence']]['loops'];
                foreach ($seq_loops as $loop) {
                    if (in_array($loop, $installed_loops)) {
                        $completed++;
                    }
                }
            }
        }
        // else: future sequence, completed = 0
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
    } elseif ($current_sequence < $phase['sequence']) {
        $status = 'locked';
    } else {
        $status = 'pending';
        if ($active_phase === null) {
            $status = 'active';
            $active_phase = $phase['id'];
            $active_phase_name = $phase['name'];
            $active_phase_completed = $completed;
            $active_phase_total = $phase['total'];
        }
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
