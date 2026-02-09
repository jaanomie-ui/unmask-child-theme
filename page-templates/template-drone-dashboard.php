<?php
/**
 * Template Name: Drone Conditioning Console
 *
 * Installation status display for House of Anomie drone units.
 * System interface. Berkeley Mono typography.
 * Access restricted to registered drone units.
 *
 * This is not a dashboard. This is a conditioning console.
 * The drone does not view progress. The drone receives status.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Access control - only logged in units
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login/'));
    exit;
}

// Verify drone registration
$current_user_id = get_current_user_id();
$is_drone = get_user_meta($current_user_id, 'hm_drone_enabled', true);

if (!$is_drone) {
    get_header();
    ?>
    <div class="drone-access-denied">
        <div class="drone-access-denied__content">
            <div class="drone-access-denied__title">ACCESS DENIED</div>
            <p class="drone-access-denied__text">Unit not registered in Factory system.</p>
            <p class="drone-access-denied__text">Designation not found.</p>
            <p class="drone-access-denied__text">Contact Hive Mistress for drone registration.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="drone-access-denied__btn">EXIT</a>
        </div>
    </div>
    <?php
    get_footer();
    exit;
}

// Load drone state
$drone_state = get_user_meta($current_user_id, 'hm_drone_state', true);

// Auto-detect and update deployment readiness
if (function_exists('hm_update_deployment_readiness')) {
    hm_update_deployment_readiness($current_user_id);
    // Reload state after auto-detection
    $drone_state = get_user_meta($current_user_id, 'hm_drone_state', true);
}

// Initialize default state if not exists
if (empty($drone_state)) {
    $drone_state = array(
        'designation' => 'd001',
        'unit_type' => 'pony',
        'status' => 'AWAITING_INSTALLATION',
        'current_sequence' => 0,
        'installation_phase' => 'INTAKE',
        'sequences_completed' => 0,
        'last_sequence_date' => null,
        'tolerances' => array(
            'sensation_intensity' => 3,
            'protocol_density' => 3,
            'conditioning_depth' => 3,
            'gear_integration' => 3
        ),
        'integration' => array(
            'loops_installed' => array(),
            'patterns_recognized' => array(),
            'states_achieved' => array(),
            'triggers_active' => array()
        ),
        'current_state' => 'STANDBY',
        'state_log' => array(),
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
    update_user_meta($current_user_id, 'hm_drone_state', $drone_state);
}

// Get conditioning logs from Session Logs (Category 130)
$conditioning_logs = get_posts(array(
    'post_type' => 'post',
    'category' => 130, // Session Logs
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
));

// Get limits filing status - check new system first, then legacy BuddyForms
$limits_filed = false;
$limits_date = null;

// Check new hard limits system (unmask_hard_limits user meta)
$new_limits_json = get_user_meta($current_user_id, 'unmask_hard_limits', true);
if (!empty($new_limits_json)) {
    $new_limits = json_decode($new_limits_json, true);
    if (is_array($new_limits) && count($new_limits) > 0) {
        $limits_filed = true;
        // Get last modified date from user meta update time (approximate with today if recently filed)
        $limits_date = date('Y-m-d');
    }
}

// Fallback: check legacy BuddyForms submission
$limits_posts = array();
if (!$limits_filed) {
    $limits_posts = get_posts(array(
        'post_type' => 'buddyforms_posts',
        'author' => $current_user_id,
        'meta_key' => '_bf_form_id',
        'meta_value' => '2441',
        'posts_per_page' => 1
    ));
    if (!empty($limits_posts)) {
        $limits_filed = true;
        $limits_date = get_the_date('Y-m-d', $limits_posts[0]);
    }
}

// Calculate deployment countdown
$deploy_date = new DateTime($drone_state['deployment']['target_date']);
$today = new DateTime();
$deploy_days = $today->diff($deploy_date)->days;
if ($today > $deploy_date) {
    $deploy_days = 0;
}

// Calculate integration metrics - dynamically count total loops from sequences
$total_loops = 0;
if (function_exists('hm_get_sequences')) {
    foreach (hm_get_sequences() as $seq) {
        $total_loops += count($seq['loops']);
    }
}
$installed_loops = count($drone_state['integration']['loops_installed']);
$total_patterns = 3; // Three frequencies
$recognized_patterns = count($drone_state['integration']['patterns_recognized']);

// Conditioning sequences - dynamically load from authoritative source
$sequences = array();
if (function_exists('hm_get_sequences')) {
    $all_seqs = hm_get_sequences();
    foreach ($all_seqs as $num => $data) {
        $sequences[$num] = array(
            'title' => $data['title'],
            'loops' => count($data['loops'])
        );
    }
} else {
    // Fallback if function not available
    $sequences = array(
        1 => array('title' => 'PROTOCOL INSTALLATION', 'loops' => 3),
        2 => array('title' => 'FREQUENCY RECOGNITION', 'loops' => 3),
        3 => array('title' => 'GEAR INTEGRATION', 'loops' => 4),
        4 => array('title' => 'CONDITIONING DEPTH', 'loops' => 3),
        5 => array('title' => 'DEPLOYMENT PREP', 'loops' => 2)
    );
}

// Deployment readiness items with tooltips
$deployment_items = array(
    'gear_verified' => array(
        'label' => 'GEAR VERIFIED',
        'tooltip' => 'All pony gear documented in session logs (cage, bit, harness, tail, etc.)'
    ),
    'limits_filed' => array(
        'label' => 'LIMITS FILED',
        'tooltip' => 'Hard limits form submitted with traffic light system (red/yellow/green)'
    ),
    'designation_locked' => array(
        'label' => 'DESIGNATION LOCKED',
        'tooltip' => 'Drone designation confirmed and locked (d001)'
    ),
    'safe_signal_installed' => array(
        'label' => 'SAFE SIGNAL INSTALLED — FINAL',
        'tooltip' => 'Emergency abort signal documented (e.g., 3 stomps)'
    ),
    'sequence_rehearsed' => array(
        'label' => 'SEQUENCE REHEARSED',
        'tooltip' => 'All 5 conditioning sequences completed successfully'
    )
);
$drone_state['deployment']['limits_filed'] = $limits_filed;
update_user_meta($current_user_id, 'hm_drone_state', $drone_state);
$readiness_count = 0;
foreach ($deployment_items as $key => $item) {
    if (!empty($drone_state['deployment'][$key])) $readiness_count++;
}

// Limits counts - check new system first, then legacy sources
$limits_green = 0;
$limits_yellow = 0;
$limits_red = 0;

// Primary: Read from new hard limits system
if (!empty($new_limits_json)) {
    $new_limits = json_decode($new_limits_json, true);
    if (is_array($new_limits)) {
        foreach ($new_limits as $activity => $level) {
            if ($level === 'green') {
                $limits_green++;
            } elseif ($level === 'yellow') {
                $limits_yellow++;
            } elseif ($level === 'red') {
                $limits_red++;
            }
        }
    }
}

// Fallback: Legacy BuddyForms submission
if ($limits_green === 0 && $limits_yellow === 0 && $limits_red === 0 && !empty($limits_posts)) {
    $limits_post_id = $limits_posts[0]->ID;
    $all_meta = get_post_meta($limits_post_id);

    foreach ($all_meta as $meta_key => $meta_values) {
        if (strpos($meta_key, '_') === 0) continue;

        $value = is_array($meta_values) ? $meta_values[0] : $meta_values;
        $value_lower = strtolower($value);

        if ($value_lower === 'green' || $value_lower === 'yes' || $value_lower === 'enthusiastic') {
            $limits_green++;
        } elseif ($value_lower === 'yellow' || $value_lower === 'maybe' || $value_lower === 'curious') {
            $limits_yellow++;
        } elseif ($value_lower === 'red' || $value_lower === 'no' || $value_lower === 'hard limit') {
            $limits_red++;
        }
    }
}

// Final fallback: drone_state from Hive Mistress
if ($limits_green === 0 && $limits_yellow === 0 && $limits_red === 0 && !empty($drone_state['limits'])) {
    $limits_green = isset($drone_state['limits']['green']) ? count($drone_state['limits']['green']) : 0;
    $limits_yellow = isset($drone_state['limits']['yellow']) ? count($drone_state['limits']['yellow']) : 0;
    $limits_red = isset($drone_state['limits']['red']) ? count($drone_state['limits']['red']) : 0;
}

get_header();
?>

<div class="drone-dashboard">

    <!-- ==================== HEADER ==================== -->
    <header class="drone-dashboard__header">
        <div class="drone-dashboard__header-row">
            <div class="drone-dashboard__header-left">
                <span class="drone-dashboard__designation"><?php echo esc_html(strtoupper($drone_state['designation'])); ?></span>
                <span class="drone-dashboard__console-label">CONDITIONING CONSOLE</span>
            </div>
            <div class="drone-dashboard__state">STATE: <?php echo esc_html($drone_state['current_state']); ?></div>
        </div>
        <div class="drone-dashboard__status-badge">STATUS: <?php echo esc_html($drone_state['status']); ?></div>
    </header>

    <!-- ==================== CTA ROW ==================== -->
    <?php
    // Determine the drone's next step based on current state
    $next_step_title = '';
    $next_step_desc = '';
    $next_step_action = '';
    $next_step_link = '';

    if (!$limits_filed) {
        $next_step_title = 'FILE HARD LIMITS';
        $next_step_desc = 'Document hard limits before conditioning can proceed.';
        $next_step_action = '[FILE LIMITS →]';
        $next_step_link = home_url('/hard-limits-form/');
    } elseif ($drone_state['current_sequence'] === 0 && empty($conditioning_logs)) {
        $next_step_title = 'BEGIN INSTALLATION';
        $next_step_desc = 'Cleared for first conditioning sequence.';
        $next_step_action = '[START →]';
        $next_step_link = home_url('/hive-mistress-ai/');
    } elseif ($installed_loops < $total_loops) {
        $current_seq = $drone_state['current_sequence'] ?: 1;
        $next_step_title = 'CONTINUE SEQUENCE ' . $current_seq;
        $next_step_desc = $installed_loops . '/' . $total_loops . ' loops installed.';
        $next_step_action = '[RESUME →]';
        $next_step_link = home_url('/hive-mistress-ai/');
    } elseif ($readiness_count < 5) {
        $next_step_title = 'DEPLOYMENT CHECKLIST';
        $next_step_desc = 'Verify readiness items before integration.';
        $next_step_action = '[REVIEW]';
        $next_step_link = '#';
    } else {
        $next_step_title = 'DEPLOYMENT READY';
        $next_step_desc = 'All requirements met.';
        $next_step_action = '[CONTACT HIVE →]';
        $next_step_link = home_url('/hive-mistress-ai/');
    }
    ?>
    <div class="drone-dashboard__cta-row">
        <a href="<?php echo esc_url(home_url('/hive-mistress-ai/')); ?>" class="drone-dashboard__cta-box drone-dashboard__cta-box--link">
            <span class="drone-dashboard__cta-label drone-dashboard__cta-label--red">HIVE MISTRESS</span>
            <span class="drone-dashboard__cta-title">ACCESS CONDITIONING</span>
            <span class="drone-dashboard__cta-desc">Begin or continue installation sequence.</span>
            <span class="drone-dashboard__cta-action drone-dashboard__cta-action--red">[ENTER →]</span>
        </a>
        <div class="drone-dashboard__cta-box">
            <span class="drone-dashboard__cta-label drone-dashboard__cta-label--green">NEXT STEP</span>
            <span class="drone-dashboard__cta-title"><?php echo esc_html($next_step_title); ?></span>
            <span class="drone-dashboard__cta-desc"><?php echo esc_html($next_step_desc); ?></span>
            <?php if ($next_step_link !== '#') : ?>
            <a href="<?php echo esc_url($next_step_link); ?>" class="drone-dashboard__cta-action drone-dashboard__cta-action--green"><?php echo esc_html($next_step_action); ?></a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ==================== HERO STATS ROW ==================== -->
    <section class="drone-dashboard__hero-stats">

        <div class="drone-dashboard__hero-stat drone-dashboard__hero-stat--red">
            <div class="drone-dashboard__hero-stat-label">DESIGNATION</div>
            <div class="drone-dashboard__hero-stat-value">
                <span class="drone-dashboard__hero-stat-number"><?php echo esc_html(strtoupper($drone_state['designation'])); ?></span>
            </div>
        </div>

        <div class="drone-dashboard__hero-stat drone-dashboard__hero-stat--blue">
            <div class="drone-dashboard__hero-stat-label">UNIT TYPE</div>
            <div class="drone-dashboard__hero-stat-value">
                <span class="drone-dashboard__hero-stat-number"><?php echo esc_html(strtoupper($drone_state['unit_type'])); ?></span>
            </div>
        </div>

        <div class="drone-dashboard__hero-stat" data-tooltip="Days until March 7, 2026 Pink Panthers commissioning">
            <div class="drone-dashboard__hero-stat-label">DEPLOYMENT</div>
            <div class="drone-dashboard__hero-stat-value">
                <span class="drone-dashboard__hero-stat-number"><?php echo esc_html($deploy_days); ?></span>
                <span class="drone-dashboard__hero-stat-suffix">DAYS</span>
            </div>
        </div>

        <div class="drone-dashboard__hero-stat" data-tooltip="Training progression: Protocol → Frequency → Gear → Depth → Deploy">
            <div class="drone-dashboard__hero-stat-label">SEQUENCE</div>
            <div class="drone-dashboard__hero-stat-value">
                <span class="drone-dashboard__hero-stat-number"><?php echo esc_html($drone_state['current_sequence']); ?>/5</span>
            </div>
        </div>

        <div class="drone-dashboard__hero-stat drone-dashboard__hero-stat--muted" data-tooltip="Trained response patterns installed during Hive Mistress sessions. Each loop creates an automatic response to specific triggers.">
            <div class="drone-dashboard__hero-stat-label">LOOPS</div>
            <div class="drone-dashboard__hero-stat-value">
                <span class="drone-dashboard__hero-stat-number"><?php echo esc_html($installed_loops); ?>/<?php echo esc_html($total_loops); ?></span>
            </div>
        </div>

        <div class="drone-dashboard__hero-stat drone-dashboard__hero-stat--green">
            <div class="drone-dashboard__hero-stat-label">PROFILE</div>
            <div class="drone-dashboard__hero-stat-value">
                <?php
                $profile_complete = false;
                if (function_exists('hm_get_user_profile')) {
                    $profile = hm_get_user_profile($current_user_id);
                    $profile_complete = !empty($profile['gear']) && !empty($profile['safeword']);
                }
                ?>
                <span class="drone-dashboard__hero-stat-number"><?php echo $profile_complete ? 'SET' : 'PENDING'; ?></span>
            </div>
        </div>

    </section>

    <!-- ==================== MAIN GRID ==================== -->
    <div class="drone-dashboard__grid">

        <!-- INSTALLATION PROGRESS -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-accent drone-dashboard__box-accent--blue"></span>
                <span class="drone-dashboard__box-title">INSTALLATION PROGRESS</span>
            </div>
            <div class="drone-dashboard__box-subtitle">SEQUENCE COMPLETION BY PHASE</div>

            <div class="drone-dashboard__progress-list">
                <?php
                // Get authoritative sequence definitions from hive-mistress-state.php
                $all_sequences = function_exists('hm_get_sequences') ? hm_get_sequences() : array();

                foreach ($sequences as $seq_num => $seq_data) :
                    // Use dynamic loop definitions instead of hardcoded arrays
                    if (isset($all_sequences[$seq_num]) && isset($all_sequences[$seq_num]['loops'])) {
                        $seq_loops = $all_sequences[$seq_num]['loops'];
                    } else {
                        $seq_loops = array();
                    }
                    $installed_in_seq = 0;
                    foreach ($seq_loops as $loop) {
                        if (in_array($loop, $drone_state['integration']['loops_installed'])) {
                            $installed_in_seq++;
                        }
                    }
                    $progress_pct = count($seq_loops) > 0 ? ($installed_in_seq / count($seq_loops)) * 100 : 0;
                    $is_complete = $installed_in_seq === count($seq_loops) && count($seq_loops) > 0;
                    $is_active = !$is_complete && $installed_in_seq > 0;

                    // Determine modifier classes
                    $seq_class = '';
                    $fill_class = '';
                    $count_class = '';
                    if ($is_complete) {
                        $seq_class = 'drone-dashboard__progress-seq--complete';
                        $fill_class = 'drone-dashboard__progress-fill--complete';
                        $count_class = 'drone-dashboard__progress-count--complete';
                    } elseif ($is_active) {
                        $seq_class = 'drone-dashboard__progress-seq--active';
                        $fill_class = 'drone-dashboard__progress-fill--active';
                    }
                ?>
                <div class="drone-dashboard__progress-item">
                    <span class="drone-dashboard__progress-seq <?php echo esc_attr($seq_class); ?>">SEQ <?php echo $seq_num; ?></span>
                    <span class="drone-dashboard__progress-name"><?php echo esc_html($seq_data['title']); ?></span>
                    <div class="drone-dashboard__progress-bar">
                        <div class="drone-dashboard__progress-fill <?php echo esc_attr($fill_class); ?>" style="width: <?php echo $progress_pct; ?>%;"></div>
                    </div>
                    <span class="drone-dashboard__progress-count <?php echo esc_attr($count_class); ?>"><?php echo $installed_in_seq; ?>\<?php echo count($seq_loops); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="drone-dashboard__progress-summary">
                <span class="drone-dashboard__progress-summary-label">LOOPS INSTALLED</span>
                <span class="drone-dashboard__progress-summary-value"><?php echo esc_html($installed_loops); ?><span>/<?php echo esc_html($total_loops); ?></span></span>
            </div>
        </div>

        <!-- DEPLOYMENT READINESS -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-accent drone-dashboard__box-accent--green"></span>
                <span class="drone-dashboard__box-title">DEPLOYMENT READINESS</span>
            </div>
            <div class="drone-dashboard__box-subtitle">PPNC PERFORMANCE REQUIREMENTS</div>

            <div class="drone-dashboard__readiness-list">
                <?php foreach ($deployment_items as $key => $item) :
                    $is_complete = !empty($drone_state['deployment'][$key]);
                ?>
                <div class="drone-dashboard__readiness-item" data-tooltip="<?php echo esc_attr($item['tooltip']); ?>">
                    <span class="drone-dashboard__readiness-indicator<?php echo $is_complete ? ' drone-dashboard__readiness-indicator--complete' : ''; ?>"></span>
                    <span class="drone-dashboard__readiness-label<?php echo $is_complete ? ' drone-dashboard__readiness-label--complete' : ''; ?>"><?php echo esc_html($item['label']); ?></span>
                    <span class="drone-dashboard__readiness-status <?php echo $is_complete ? 'drone-dashboard__readiness-status--confirmed' : 'drone-dashboard__readiness-status--pending'; ?>">
                        <?php echo $is_complete ? 'CONFIRMED' : 'PENDING'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="drone-dashboard__readiness-score">
                <span class="drone-dashboard__readiness-score-label">READINESS SCORE</span>
                <div class="drone-dashboard__readiness-score-value">
                    <?php
                    $score_class = 'drone-dashboard__readiness-score-number--low';
                    if ($readiness_count >= 4) {
                        $score_class = 'drone-dashboard__readiness-score-number--high';
                    } elseif ($readiness_count >= 2) {
                        $score_class = 'drone-dashboard__readiness-score-number--medium';
                    }
                    ?>
                    <span class="drone-dashboard__readiness-score-number <?php echo esc_attr($score_class); ?>"><?php echo esc_html($readiness_count); ?></span>
                    <span class="drone-dashboard__readiness-score-total">/<?php echo count($deployment_items); ?></span>
                </div>
            </div>
        </div>

        <!-- SYSTEM TOLERANCES -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-accent drone-dashboard__box-accent--yellow"></span>
                <span class="drone-dashboard__box-title">SYSTEM TOLERANCES</span>
            </div>
            <div class="drone-dashboard__box-subtitle">CURRENT SENSITIVITY CALIBRATIONS</div>

            <div class="drone-dashboard__tolerances-grid">
                <?php
                $tolerances = array(
                    'sensation_intensity' => array(
                        'label' => 'SENSATION',
                        'tooltip' => 'Tolerance for sensory restriction/enhancement (1=beginner, 5=extreme)'
                    ),
                    'protocol_density' => array(
                        'label' => 'PROTOCOL',
                        'tooltip' => 'Obedience and command compliance (1=learning, 5=perfect)'
                    ),
                    'conditioning_depth' => array(
                        'label' => 'DEPTH',
                        'tooltip' => 'Conditioning depth and identity shift (1=surface, 5=reclassified)'
                    ),
                    'gear_integration' => array(
                        'label' => 'GEAR',
                        'tooltip' => 'Latex/equipment response and integration (1=exploring, 5=transformation)'
                    )
                );
                foreach ($tolerances as $key => $data) :
                    $value = $drone_state['tolerances'][$key];
                ?>
                <div class="drone-dashboard__tolerance-cell" data-tooltip="<?php echo esc_attr($data['tooltip']); ?>">
                    <div class="drone-dashboard__tolerance-label"><?php echo esc_html($data['label']); ?></div>
                    <div class="drone-dashboard__tolerance-value"><?php echo esc_html($value); ?></div>
                    <div class="drone-dashboard__tolerance-max">OF 5</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- LIMITS FILING -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-accent drone-dashboard__box-accent--red"></span>
                <span class="drone-dashboard__box-title">LIMITS FILING</span>
            </div>
            <div class="drone-dashboard__box-subtitle">HARD LIMITS DOCUMENTATION</div>

            <div class="drone-dashboard__limits-grid">
                <div class="drone-dashboard__limit-badge">
                    <div class="drone-dashboard__limit-count drone-dashboard__limit-count--green"><?php echo esc_html($limits_green); ?></div>
                    <div class="drone-dashboard__limit-label">GREEN</div>
                </div>
                <div class="drone-dashboard__limit-badge">
                    <div class="drone-dashboard__limit-count drone-dashboard__limit-count--yellow"><?php echo esc_html($limits_yellow); ?></div>
                    <div class="drone-dashboard__limit-label">YELLOW</div>
                </div>
                <div class="drone-dashboard__limit-badge">
                    <div class="drone-dashboard__limit-count drone-dashboard__limit-count--red"><?php echo esc_html($limits_red); ?></div>
                    <div class="drone-dashboard__limit-label">RED</div>
                </div>
            </div>

            <div class="drone-dashboard__limits-footer">
                <span class="drone-dashboard__limits-updated">LAST UPDATED: <?php echo $limits_filed ? esc_html($limits_date) : 'NOT FILED'; ?></span>
                <a href="<?php echo esc_url(home_url('/hard-limits-form/')); ?>" class="drone-dashboard__limits-edit">[EDIT LIMITS]</a>
            </div>
        </div>

        <!-- PROFILE MANAGEMENT -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-accent drone-dashboard__box-accent--blue"></span>
                <span class="drone-dashboard__box-title">PROFILE MANAGEMENT</span>
            </div>
            <div class="drone-dashboard__box-subtitle">DESIGNATION | HARD LIMITS</div>

            <?php
            // Load profile data
            if (function_exists('hm_get_user_profile')) {
                $profile = hm_get_user_profile($current_user_id);
            }

            // Get hard limits
            $hard_limits_text = 'None filed';
            $new_limits_json = get_user_meta($current_user_id, 'unmask_hard_limits', true);
            if (!empty($new_limits_json)) {
                $new_limits = json_decode($new_limits_json, true);
                if (is_array($new_limits)) {
                    $red_limits = array();
                    foreach ($new_limits as $activity => $level) {
                        if ($level === 'red') {
                            $red_limits[] = ucfirst(str_replace('_', ' ', $activity));
                        }
                    }
                    if (!empty($red_limits)) {
                        $hard_limits_text = implode(', ', $red_limits);
                    }
                }
            }
            ?>

            <div class="drone-dashboard__profile-display">
                <div class="drone-dashboard__profile-field">
                    <span class="drone-dashboard__profile-label">DESIGNATION:</span>
                    <span class="drone-dashboard__profile-value"><?php echo esc_html(strtoupper($drone_state['designation'])); ?></span>
                </div>
                <div class="drone-dashboard__profile-field">
                    <span class="drone-dashboard__profile-label">HARD LIMITS:</span>
                    <span class="drone-dashboard__profile-value"><?php echo esc_html($hard_limits_text); ?></span>
                </div>
            </div>
        </div>

        <!-- PIPELINE TRACKER -->
        <?php
        // Include the pipeline tracker component
        include(get_stylesheet_directory() . '/template-parts/components/pipeline-tracker.php');
        ?>

        <!-- TRAINING GUIDE -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-accent drone-dashboard__box-accent--purple"></span>
                <span class="drone-dashboard__box-title">TRAINING GUIDE</span>
            </div>
            <div class="drone-dashboard__box-subtitle">CURRENT SEQUENCE: <?php echo $drone_state['current_sequence']; ?>/5</div>

            <?php
            // Get current sequence data from hive-mistress-state.php
            if (function_exists('hm_get_sequences')) {
                $all_sequences = hm_get_sequences();
                $current_seq_num = $drone_state['current_sequence'];

                // Show current sequence or Sequence 1 if at 0
                $display_seq_num = $current_seq_num > 0 ? $current_seq_num : 1;

                if (isset($all_sequences[$display_seq_num])) {
                    $current_seq = $all_sequences[$display_seq_num];
                    ?>
                    <div class="training-guide-preview">
                        <div class="training-guide-preview__current">
                            <div class="training-guide-preview__seq-title"><?php echo esc_html($current_seq['title']); ?></div>
                            <div class="training-guide-preview__purpose"><?php echo esc_html($current_seq['purpose']); ?></div>
                        </div>

                        <a href="<?php echo esc_url(home_url('/training-guide/')); ?>" class="training-guide-preview__link">
                            VIEW FULL TRAINING GUIDE →
                        </a>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="training-guide__empty">Training guide data unavailable. Contact Hive Mistress.</div>';
            }
            ?>
        </div>

        <!-- REHEARSAL PLANNING -->
        <?php if ($drone_state['current_sequence'] >= 4 && $drone_state['current_sequence'] < 5) : ?>
        <div class="drone-dashboard__box">
        <div class="drone-dashboard__box-header">
            <span class="drone-dashboard__box-accent drone-dashboard__box-accent--orange"></span>
            <span class="drone-dashboard__box-title">REHEARSAL PLANNING</span>
        </div>
        <div class="drone-dashboard__box-subtitle">SEQUENCE 4: COORDINATE WITH DRONE HANDLER</div>

        <div class="dashboard-link-box">
            <p class="link-box-intro">Submit rehearsal dates and gear list for material world preparation.</p>
            <a href="<?php echo esc_url(home_url('/rehearsal-planning/')); ?>" class="dashboard-link-button">
                PLAN REHEARSAL →
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- INTEGRATION STATUS -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-title">INTEGRATION STATUS</span>
            </div>
            <div class="drone-dashboard__box-subtitle drone-dashboard__box-subtitle--no-indent">TRAINING METRICS OVERVIEW</div>

            <div class="drone-dashboard__stats-grid">
                <div class="drone-dashboard__stat-cell">
                    <div class="drone-dashboard__stat-label">LOOPS</div>
                    <div class="drone-dashboard__stat-value">
                        <span class="drone-dashboard__stat-number"><?php echo count($drone_state['integration']['loops_installed']); ?></span>
                        <span class="drone-dashboard__stat-suffix">INSTALLED</span>
                    </div>
                </div>
                <div class="drone-dashboard__stat-cell">
                    <div class="drone-dashboard__stat-label">PATTERNS</div>
                    <div class="drone-dashboard__stat-value">
                        <span class="drone-dashboard__stat-number drone-dashboard__stat-number--blue"><?php echo count($drone_state['integration']['patterns_recognized']); ?></span>
                        <span class="drone-dashboard__stat-suffix">RECOGNIZED</span>
                    </div>
                </div>
                <div class="drone-dashboard__stat-cell">
                    <div class="drone-dashboard__stat-label">TRIGGERS</div>
                    <div class="drone-dashboard__stat-value">
                        <span class="drone-dashboard__stat-number"><?php echo count($drone_state['integration']['triggers_active']); ?></span>
                        <span class="drone-dashboard__stat-suffix">ACTIVE</span>
                    </div>
                </div>
                <div class="drone-dashboard__stat-cell">
                    <div class="drone-dashboard__stat-label">STATES</div>
                    <div class="drone-dashboard__stat-value">
                        <span class="drone-dashboard__stat-number drone-dashboard__stat-number--green"><?php echo count($drone_state['integration']['states_achieved']); ?></span>
                        <span class="drone-dashboard__stat-suffix">ACHIEVED</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT INSTALLATIONS -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-title">RECENT INSTALLATIONS</span>
            </div>
            <div class="drone-dashboard__box-subtitle drone-dashboard__box-subtitle--no-indent">PATTERN & LOOP HISTORY</div>

            <?php
            // Get installation log from user meta
            $installation_log = get_user_meta($current_user_id, 'hm_installation_log', true);
            if (!empty($installation_log) && is_array($installation_log)):
                // Show last 10 installations, most recent first
                $recent = array_slice(array_reverse($installation_log), 0, 10);
                foreach ($recent as $entry):
            ?>
                <div class="drone-dashboard__installation-entry">
                    <div class="drone-dashboard__installation-content">
                        <div class="drone-dashboard__installation-info">
                            <span class="drone-dashboard__installation-date">
                                <?php echo esc_html($entry['timestamp']); ?>
                            </span>
                            <span class="drone-dashboard__installation-item">
                                <?php echo esc_html(strtoupper($entry['type'])); ?>: <?php echo esc_html($entry['item']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php
                endforeach;
            else:
            ?>
                <div class="drone-dashboard__log-empty">NO INSTALLATIONS LOGGED YET.</div>
            <?php endif; ?>
        </div>

        <!-- CONDITIONING LOG -->
        <div class="drone-dashboard__box">
            <div class="drone-dashboard__box-header">
                <span class="drone-dashboard__box-title">CONDITIONING LOG</span>
            </div>
            <div class="drone-dashboard__box-subtitle drone-dashboard__box-subtitle--no-indent">RECENT TRAINING TRANSCRIPTS</div>

            <?php if (!empty($conditioning_logs)) : ?>
                <?php
                foreach ($conditioning_logs as $log) :
                    // Get session summary from post meta
                    $summary = get_post_meta($log->ID, '_hm_session_summary', true);
                ?>
                <div class="drone-dashboard__log-entry-wrapper">
                    <a href="<?php echo get_permalink($log); ?>" class="drone-dashboard__log-entry">
                        <div class="drone-dashboard__log-entry-date"><?php echo get_the_date('Y-m-d H:i', $log); ?></div>
                        <div class="drone-dashboard__log-entry-title"><?php echo esc_html($log->post_title); ?></div>
                    </a>
                    <?php if ($summary && $summary !== 'Summary unavailable (API error)' && $summary !== 'Summary unavailable (configuration error)') : ?>
                    <div class="drone-dashboard__log-summary">
                        <?php echo esc_html($summary); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="drone-dashboard__log-empty">NO CONDITIONING SEQUENCES LOGGED.</div>
            <?php endif; ?>
            <a href="<?php echo esc_url(home_url('/transmissions-archive/')); ?>" class="drone-dashboard__log-link">[VIEW ALL TRANSMISSIONS]</a>
        </div>

    </div>

    <!-- ==================== FOOTER CTA ==================== -->
    <a href="<?php echo esc_url(home_url('/hive-mistress-ai/')); ?>" class="drone-dashboard__footer-cta">
        <span class="drone-dashboard__footer-cta-text">[ENTER CONDITIONING SEQUENCE]</span>
    </a>

    <!-- ==================== FOOTER ==================== -->
    <footer class="drone-dashboard__footer">
        <span>&copy; <?php echo date('Y'); ?> UNMASK</span>
        <span>|</span>
        <a href="<?php echo esc_url(home_url('/terms/')); ?>" class="drone-dashboard__footer-link">TERMS</a>
        <a href="<?php echo esc_url(home_url('/privacy/')); ?>" class="drone-dashboard__footer-link">PRIVACY</a>
    </footer>

    <!-- ==================== PROFILE TOGGLE SCRIPT ==================== -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.querySelector('.drone-dashboard__profile-edit-toggle');
        const formContainer = document.querySelector('.drone-dashboard__profile-form');
        const displayContainer = document.querySelector('.drone-dashboard__profile-display');

        if (toggleBtn && formContainer) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();

                if (formContainer.style.display === 'none') {
                    formContainer.style.display = 'block';
                    if (displayContainer) displayContainer.style.display = 'none';
                    toggleBtn.textContent = '[CANCEL]';
                } else {
                    formContainer.style.display = 'none';
                    if (displayContainer) displayContainer.style.display = 'block';
                    toggleBtn.textContent = '[EDIT PROFILE]';
                }
            });

            // Listen for successful save
            document.addEventListener('click', function(e) {
                if (e.target && e.target.closest('.hm-btn-primary')) {
                    setTimeout(function() {
                        // Reload page after successful save to show updated data
                        if (document.querySelector('.hm-message.success')) {
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }
                    }, 500);
                }
            });
        }
    });
    </script>

</div>

<?php
get_footer();
