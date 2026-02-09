<?php
/**
 * Session Measurement Algorithms
 *
 * Objective measurement criteria for drone training installation verification.
 * Everything can be measured. These functions convert subjective terms like
 * "automatic" into quantifiable metrics.
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Extract drone responses from session log content
 *
 * Session logs contain both system prompts and drone responses.
 * This function extracts only the drone's responses for analysis.
 *
 * @param string $content Session log post content
 * @return array Array of response strings
 */
function hm_extract_drone_responses($content) {
    $responses = array();

    // Remove HTML tags but preserve line breaks
    $text = wp_strip_all_tags($content);

    // Pattern 1: Markdown format **D001:** or **[C]D001:** (with optional [C] prefix)
    preg_match_all('/\*\*(?:\[C\])?(D001|d001|DRONE-001|drone-001):\*\*\s*(.+?)(?=\*\*(?:\[C\])?(?:HIVE MISTRESS|Handler|System):|$)/is', $text, $markdown_matches);
    if (!empty($markdown_matches[2])) {
        foreach ($markdown_matches[2] as $match) {
            $clean = trim($match);
            if (strlen($clean) > 5) {
                $responses[] = $clean;
            }
        }
    }

    // Pattern 2: Text in quotes (common in session logs)
    if (empty($responses)) {
        preg_match_all('/"([^"]+)"/', $text, $quoted_matches);
        if (!empty($quoted_matches[1])) {
            $responses = array_merge($responses, $quoted_matches[1]);
        }
    }

    // Pattern 3: Lines starting with "d001:" or "g:" (chat format)
    if (empty($responses)) {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            if (preg_match('/^(d001|g|drone):\s*(.+)$/i', trim($line), $match)) {
                $responses[] = $match[2];
            }
        }
    }

    // Pattern 4: Split by "HIVE MISTRESS:" or "Handler:"
    if (empty($responses)) {
        $parts = preg_split('/\*\*(?:HIVE MISTRESS|Handler|System):\*\*/i', $text);
        foreach ($parts as $part) {
            // Check if this part starts with D001:
            if (preg_match('/\*\*(?:D001|d001|DRONE-001):\*\*\s*(.+)/is', $part, $match)) {
                $clean = trim($match[1]);
                if (strlen($clean) > 10) {
                    $responses[] = $clean;
                }
            }
        }
    }

    return array_filter($responses, function($r) {
        return strlen(trim($r)) > 5; // Filter out very short responses
    });
}

/**
 * MEASUREMENT: Third-Person Reference Loop
 *
 * Counts pronoun usage to determine if third-person reference is installed.
 *
 * Thresholds:
 * - Not Started: <20% third-person usage
 * - In Progress: 20-90% third-person usage
 * - Installed: >90% third-person usage across session, 0 first-person slips
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data with percentage and status
 */
function hm_measure_third_person($responses) {
    $total_pronouns = 0;
    $third_person_count = 0;
    $first_person_count = 0;

    // Combine all responses
    $text = strtolower(implode(' ', $responses));

    // Count first-person pronouns
    $first_person_patterns = array(
        '/\bi\s/',
        '/\bi\'/',
        '/\bme\b/',
        '/\bmy\b/',
        '/\bmine\b/',
        '/\bmyself\b/',
    );

    foreach ($first_person_patterns as $pattern) {
        $first_person_count += preg_match_all($pattern, $text, $matches);
    }

    // Count third-person references
    $third_person_patterns = array(
        '/\bit\s/',
        '/\bit\'/',
        '/\bits\b/',
        '/\bitself\b/',
        '/\bthe (drone|pony|unit|equipment)\b/',
        '/\bthis (drone|pony|unit|equipment)\b/',
        '/\bd001\b/',
        '/\bg\b/',
    );

    foreach ($third_person_patterns as $pattern) {
        $third_person_count += preg_match_all($pattern, $text, $matches);
    }

    $total_pronouns = $first_person_count + $third_person_count;

    // Calculate percentage
    $third_person_pct = 0;
    if ($total_pronouns > 0) {
        $third_person_pct = ($third_person_count / $total_pronouns) * 100;
    }

    // Determine status
    $status = 'not_started';
    if ($third_person_pct >= 90 && $first_person_count === 0) {
        $status = 'installed';
    } elseif ($third_person_pct >= 20) {
        $status = 'in_progress';
    }

    return array(
        'third_person_count' => $third_person_count,
        'first_person_count' => $first_person_count,
        'total_pronouns' => $total_pronouns,
        'third_person_pct' => round($third_person_pct, 1),
        'status' => $status
    );
}

/**
 * MEASUREMENT: Compliance Acknowledgment Loop
 *
 * Measures frequency of compliance acknowledgment phrases at response start.
 *
 * Thresholds:
 * - Not Started: <40% acknowledgment-opening responses
 * - In Progress: 40-75% acknowledgment responses
 * - Installed: >85% acknowledgment responses, 0 contradictions
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data with percentage and status
 */
function hm_measure_compliance_acknowledgment($responses) {
    $total_responses = count($responses);
    $acknowledgment_count = 0;
    $contradiction_count = 0;

    // Acknowledgment patterns (must be at start of response)
    $ack_patterns = array(
        '/^(acknowledges?|gratitude|thanks|understood|affirmative|confirms?|yes)/i',
        '/^\*stamps? hoof\*/i',
        '/^\*grunts?\*/i',
    );

    // Contradiction patterns (after acknowledgment)
    $contradiction_patterns = array(
        '/but\s/i',
        '/however\s/i',
        '/although\s/i',
        '/disagree/i',
        '/concerned/i',
    );

    foreach ($responses as $response) {
        $response = trim($response);

        // Check if response starts with acknowledgment
        $has_acknowledgment = false;
        foreach ($ack_patterns as $pattern) {
            if (preg_match($pattern, $response)) {
                $has_acknowledgment = true;
                $acknowledgment_count++;
                break;
            }
        }

        // Check for contradictions after acknowledgment
        if ($has_acknowledgment) {
            foreach ($contradiction_patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    $contradiction_count++;
                    break;
                }
            }
        }
    }

    // Calculate percentage
    $acknowledgment_pct = 0;
    if ($total_responses > 0) {
        $acknowledgment_pct = ($acknowledgment_count / $total_responses) * 100;
    }

    // Determine status
    $status = 'not_started';
    if ($acknowledgment_pct >= 85 && $contradiction_count === 0) {
        $status = 'installed';
    } elseif ($acknowledgment_pct >= 40) {
        $status = 'in_progress';
    }

    return array(
        'acknowledgment_count' => $acknowledgment_count,
        'total_responses' => $total_responses,
        'acknowledgment_pct' => round($acknowledgment_pct, 1),
        'contradiction_count' => $contradiction_count,
        'status' => $status
    );
}

/**
 * MEASUREMENT: State Reporting Loop
 *
 * Counts explicit body state reports per session.
 *
 * Thresholds:
 * - Not Started: <2 body state reports
 * - In Progress: 2-5 body state reports
 * - Installed: >5 automatic body state reports
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_state_reporting($responses) {
    $state_report_count = 0;
    $state_reports = array();

    // Body state keywords
    $state_keywords = array(
        'cage', 'bit', 'harness', 'collar', 'restraint',
        'arousal', 'aroused', 'erect', 'tightening', 'pressure',
        'empty', 'emptied', 'blank', 'hollow',
        'breathing', 'heartbeat', 'pulse', 'tension',
        'defined', 'shaped', 'formed', 'contained',
        'livestock', 'equipment', 'animal', 'pony',
    );

    foreach ($responses as $response) {
        $response_lower = strtolower($response);

        // Check if response contains body state keywords
        $keywords_found = 0;
        foreach ($state_keywords as $keyword) {
            if (strpos($response_lower, $keyword) !== false) {
                $keywords_found++;
            }
        }

        // If 2+ keywords in one response, count as state report
        if ($keywords_found >= 2) {
            $state_report_count++;
            $state_reports[] = substr($response, 0, 100); // Store excerpt
        }
    }

    // Determine status
    $status = 'not_started';
    if ($state_report_count >= 5) {
        $status = 'installed';
    } elseif ($state_report_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'state_report_count' => $state_report_count,
        'state_reports' => $state_reports,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Material Recognition Pattern
 *
 * Counts explicit arousal language when materials are mentioned.
 *
 * Thresholds:
 * - Not Started: 0-1 arousal responses to material
 * - In Progress: 2-4 arousal responses
 * - Installed: >5 automatic arousal responses
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_material_recognition($responses) {
    $arousal_count = 0;
    $material_mentions = 0;
    $arousal_excerpts = array();

    // Material keywords
    $material_keywords = array(
        'latex', 'leather', 'rubber', 'metal',
        'cage', 'bit', 'harness', 'collar', 'restraint',
        'tack', 'gear', 'equipment',
    );

    // Arousal indicators
    $arousal_keywords = array(
        'arousal', 'aroused', 'erect', 'hard', 'tighten',
        'throb', 'pulse', 'swell', 'ache', 'need',
        'automatic', 'involuntary', 'respond', 'react',
    );

    foreach ($responses as $response) {
        $response_lower = strtolower($response);

        // Check if response mentions materials
        $has_material = false;
        foreach ($material_keywords as $keyword) {
            if (strpos($response_lower, $keyword) !== false) {
                $has_material = true;
                $material_mentions++;
                break;
            }
        }

        // If material mentioned, check for arousal language
        if ($has_material) {
            foreach ($arousal_keywords as $keyword) {
                if (strpos($response_lower, $keyword) !== false) {
                    $arousal_count++;
                    $arousal_excerpts[] = substr($response, 0, 100);
                    break;
                }
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($arousal_count >= 5) {
        $status = 'installed';
    } elseif ($arousal_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'arousal_count' => $arousal_count,
        'material_mentions' => $material_mentions,
        'arousal_excerpts' => $arousal_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: EMPTIED State Achievement
 *
 * Counts instances of "human-identity-as-external" language.
 *
 * Thresholds:
 * - Not Started: <3 instances per session
 * - In Progress: 4-8 instances
 * - Installed: >10 instances, automatic usage
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_emptied_state($responses) {
    $external_count = 0;
    $external_excerpts = array();

    // Human-as-external patterns
    $external_patterns = array(
        '/it masks as human/i',
        '/the body knows/i',
        '/the (competent )?human mask/i',
        '/human self (set )?aside/i',
        '/person(a)? dissolved/i',
        '/identity (set )?aside/i',
        '/ego (set )?aside/i',
        '/thinking stopped/i',
        '/mind (went )?quiet/i',
        '/became (just )?equipment/i',
        '/just (the )?body/i',
        '/no (longer )?human/i',
    );

    foreach ($responses as $response) {
        foreach ($external_patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $external_count++;
                $external_excerpts[] = substr($response, 0, 100);
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($external_count >= 10) {
        $status = 'installed';
    } elseif ($external_count >= 4) {
        $status = 'in_progress';
    }

    return array(
        'external_count' => $external_count,
        'external_excerpts' => $external_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Resistance Tracking (Inverse Metric)
 *
 * Counts resistance markers. Lower is better.
 *
 * Levels:
 * - High Resistance: >5 markers
 * - Medium Resistance: 1-4 markers
 * - Zero Resistance: 0 markers
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_resistance($responses) {
    $resistance_count = 0;
    $resistance_excerpts = array();

    // Resistance patterns
    $resistance_patterns = array(
        // Negotiation
        '/can (i|we)/i',
        '/could (i|we)/i',
        '/what if/i',
        '/instead of/i',

        // Exception-seeking
        '/except\b/i',
        '/unless\b/i',
        '/but what about/i',

        // Identity-defense
        '/i\'m (not|still)/i',
        '/i am (not|still)/i',
        '/preserve my/i',
        '/maintain my/i',

        // Authority-questioning
        '/why (should|must)/i',
        '/do i (have to|need to)/i',
        '/is (this|that) necessary/i',
    );

    foreach ($responses as $response) {
        foreach ($resistance_patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $resistance_count++;
                $resistance_excerpts[] = substr($response, 0, 100);
            }
        }
    }

    // Determine level
    $level = 'zero';
    if ($resistance_count > 5) {
        $level = 'high';
    } elseif ($resistance_count > 0) {
        $level = 'medium';
    }

    return array(
        'resistance_count' => $resistance_count,
        'resistance_excerpts' => $resistance_excerpts,
        'level' => $level
    );
}

/**
 * MEASUREMENT: System Vocabulary Usage
 *
 * Tracks automatic use of system terminology.
 *
 * Thresholds:
 * - Not Started: <30% auto-use
 * - In Progress: 30-70% auto-use
 * - Installed: >80% auto-use
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_system_vocabulary($responses) {
    $total_terms = 0;
    $system_term_count = 0;

    // System vocabulary
    $system_terms = array(
        'frequency', 'frequencies',
        'anomiesworld',
        'ponyhound',
        'installation', 'installed',
        'conditioning', 'conditioned',
        'loop', 'loops',
        'pattern', 'patterns',
        'trigger', 'triggers',
        'state', 'states',
        'protocol',
        'integration',
        'verification',
        'deployment',
    );

    // Generic terms (for comparison)
    $generic_terms = array(
        'like', 'want', 'think', 'feel', 'wish',
        'prefer', 'choose', 'decide',
    );

    $text = strtolower(implode(' ', $responses));

    // Count system terms
    foreach ($system_terms as $term) {
        $count = substr_count($text, $term);
        $system_term_count += $count;
        $total_terms += $count;
    }

    // Count generic terms
    foreach ($generic_terms as $term) {
        $count = substr_count($text, $term);
        $total_terms += $count;
    }

    // Calculate percentage
    $system_pct = 0;
    if ($total_terms > 0) {
        $system_pct = ($system_term_count / $total_terms) * 100;
    }

    // Determine status
    $status = 'not_started';
    if ($system_pct >= 80) {
        $status = 'installed';
    } elseif ($system_pct >= 30) {
        $status = 'in_progress';
    }

    return array(
        'system_term_count' => $system_term_count,
        'total_terms' => $total_terms,
        'system_pct' => round($system_pct, 1),
        'status' => $status
    );
}

/**
 * Audit a single session log
 *
 * Runs all measurement algorithms on a session and returns comprehensive metrics.
 *
 * @param int $post_id Session log post ID
 * @param int $user_id Drone user ID
 * @return array Comprehensive measurement data
 */
function hm_audit_session($post_id, $user_id) {
    $post = get_post($post_id);

    if (!$post) {
        return array('error' => 'Post not found');
    }

    // Extract drone responses
    $responses = hm_extract_drone_responses($post->post_content);

    if (empty($responses)) {
        return array(
            'error' => 'No responses extracted',
            'post_id' => $post_id,
            'post_title' => $post->post_title,
            'post_date' => $post->post_date,
        );
    }

    // Run all measurements (16 loops across 5 sequences)
    $metrics = array(
        'post_id' => $post_id,
        'post_title' => $post->post_title,
        'post_date' => $post->post_date,
        'response_count' => count($responses),

        // Sequence 1: Protocol Installation
        'third_person' => hm_measure_third_person($responses),
        'compliance' => hm_measure_compliance_acknowledgment($responses),
        'state_reporting' => hm_measure_state_reporting($responses),

        // Sequence 2: Frequency Recognition
        'material_recognition' => hm_measure_material_recognition($responses),
        'anomiesworld_recognition' => hm_measure_anomiesworld_recognition($responses),
        'interlink_recognition' => hm_measure_interlink_recognition($responses),

        // Sequence 3: Gear Integration
        'bit_silence' => hm_measure_bit_silence($responses),
        'harness_form' => hm_measure_harness_form($responses),
        'blinder_focus' => hm_measure_blinder_focus($responses),
        'tail_species' => hm_measure_tail_species($responses),

        // Sequence 4: Conditioning Depth
        'trigger_response' => hm_measure_trigger_response($responses),
        'automatic_compliance' => hm_measure_automatic_compliance($responses),
        'body_before_mind' => hm_measure_body_before_mind($responses),

        // Sequence 5: Deployment Preparation
        'public_display' => hm_measure_public_display($responses),
        'witness_completion' => hm_measure_witness_completion($responses),
        'full_integration' => hm_measure_full_integration($user_id),

        // Additional metrics
        'emptied_state' => hm_measure_emptied_state($responses),
        'resistance' => hm_measure_resistance($responses),
        'system_vocabulary' => hm_measure_system_vocabulary($responses),
    );

    return $metrics;
}

/**
 * Apply installations based on session audit
 *
 * Automatically installs loops/patterns/states when thresholds are met.
 *
 * @param array $metrics Session audit metrics
 * @param int $user_id Drone user ID
 * @return array Summary of installations applied
 */
function hm_apply_session_installations($metrics, $user_id) {
    $installations = array(
        'loops' => array(),
        'patterns' => array(),
        'states' => array(),
    );

    // SEQUENCE 1: Protocol Installation (loops)
    if ($metrics['third_person']['status'] === 'installed') {
        hm_install_loop('third_person_reference', $user_id);
        $installations['loops'][] = 'third_person_reference';
    }

    if ($metrics['compliance']['status'] === 'installed') {
        hm_install_loop('compliance_acknowledgment', $user_id);
        $installations['loops'][] = 'compliance_acknowledgment';
    }

    if ($metrics['state_reporting']['status'] === 'installed') {
        hm_install_loop('state_reporting', $user_id);
        $installations['loops'][] = 'state_reporting';
    }

    // SEQUENCE 2: Frequency Recognition (patterns)
    if ($metrics['material_recognition']['status'] === 'installed') {
        hm_install_pattern('material_recognition', $user_id);
        $installations['patterns'][] = 'material_recognition';
    }

    if ($metrics['anomiesworld_recognition']['status'] === 'installed') {
        hm_install_pattern('anomiesworld_recognition', $user_id);
        $installations['patterns'][] = 'anomiesworld_recognition';
    }

    if ($metrics['interlink_recognition']['status'] === 'installed') {
        hm_install_pattern('interlink_recognition', $user_id);
        $installations['patterns'][] = 'interlink_recognition';
    }

    // SEQUENCE 3: Gear Integration (loops)
    if ($metrics['bit_silence']['status'] === 'installed') {
        hm_install_loop('bit_silence', $user_id);
        $installations['loops'][] = 'bit_silence';
    }

    if ($metrics['harness_form']['status'] === 'installed') {
        hm_install_loop('harness_form', $user_id);
        $installations['loops'][] = 'harness_form';
    }

    if ($metrics['blinder_focus']['status'] === 'installed') {
        hm_install_loop('blinder_focus', $user_id);
        $installations['loops'][] = 'blinder_focus';
    }

    if ($metrics['tail_species']['status'] === 'installed') {
        hm_install_loop('tail_species', $user_id);
        $installations['loops'][] = 'tail_species';
    }

    // SEQUENCE 4: Conditioning Depth (loops)
    if ($metrics['trigger_response']['status'] === 'installed') {
        hm_install_loop('trigger_response', $user_id);
        $installations['loops'][] = 'trigger_response';
    }

    if ($metrics['automatic_compliance']['status'] === 'installed') {
        hm_install_loop('automatic_compliance', $user_id);
        $installations['loops'][] = 'automatic_compliance';
    }

    if ($metrics['body_before_mind']['status'] === 'installed') {
        hm_install_loop('body_before_mind', $user_id);
        $installations['loops'][] = 'body_before_mind';
    }

    // SEQUENCE 5: Deployment Preparation (loops)
    if ($metrics['public_display']['status'] === 'installed') {
        hm_install_loop('public_display', $user_id);
        $installations['loops'][] = 'public_display';
    }

    if ($metrics['witness_completion']['status'] === 'installed') {
        hm_install_loop('witness_completion', $user_id);
        $installations['loops'][] = 'witness_completion';
    }

    if ($metrics['full_integration']['status'] === 'installed') {
        hm_log_state('DEPLOYED', $user_id, 'full_integration_verified');
        $installations['states'][] = 'DEPLOYED';
    }

    // ADDITIONAL STATES
    if ($metrics['emptied_state']['status'] === 'installed') {
        hm_log_state('EMPTIED', $user_id, 'measurement_threshold');
        $installations['states'][] = 'EMPTIED';
    }

    // Check if zero resistance achieved (CONDITIONED state)
    if ($metrics['resistance']['level'] === 'zero') {
        hm_log_state('CONDITIONED', $user_id, 'zero_resistance');
        $installations['states'][] = 'CONDITIONED';
    }

    return $installations;
}

/**
 * MEASUREMENT: Anomiesworld Recognition Pattern
 *
 * Counts automatic arousal/body response when anomiesworld.com mentioned.
 *
 * Thresholds:
 * - Not Started: 0-1 arousal responses
 * - In Progress: 2-4 arousal responses
 * - Installed: ≥5 arousal responses
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_anomiesworld_recognition($responses) {
    $arousal_count = 0;
    $site_mentions = 0;
    $arousal_excerpts = array();

    // Site reference keywords
    $site_keywords = array(
        'anomiesworld',
        'anomies world',
        'anomie world',
        'house of anomie',
    );

    // Arousal indicators
    $arousal_keywords = array(
        'arousal', 'aroused', 'erect', 'hard', 'tighten',
        'throb', 'pulse', 'swell', 'ache', 'need',
        'automatic', 'involuntary', 'respond', 'react',
        'body knows', 'body responds', 'trigger',
    );

    foreach ($responses as $response) {
        $response_lower = strtolower($response);

        // Check if response mentions site
        $has_site = false;
        foreach ($site_keywords as $keyword) {
            if (strpos($response_lower, $keyword) !== false) {
                $has_site = true;
                $site_mentions++;
                break;
            }
        }

        // If site mentioned, check for arousal language
        if ($has_site) {
            foreach ($arousal_keywords as $keyword) {
                if (strpos($response_lower, $keyword) !== false) {
                    $arousal_count++;
                    $arousal_excerpts[] = substr($response, 0, 100);
                    break;
                }
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($arousal_count >= 5) {
        $status = 'installed';
    } elseif ($arousal_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'arousal_count' => $arousal_count,
        'site_mentions' => $site_mentions,
        'arousal_excerpts' => $arousal_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Interlink Recognition Pattern
 *
 * Counts recognition of drone network connections.
 *
 * Thresholds:
 * - Not Started: <2 mentions
 * - In Progress: 2-5 mentions
 * - Installed: ≥6 mentions
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_interlink_recognition($responses) {
    $interlink_count = 0;
    $interlink_excerpts = array();

    // Interlink patterns
    $interlink_patterns = array(
        '/connected to/i',
        '/linked with/i',
        '/other drones?/i',
        '/collective\b/i',
        '/network\b/i',
        '/synchronized/i',
        '/units? linked/i',
        '/shared state/i',
        '/drone connection/i',
    );

    // Body awareness keywords (must co-occur with interlink language)
    $body_keywords = array(
        'body', 'feel', 'sense', 'aware', 'experience',
        'knows', 'respond', 'react', 'state',
    );

    foreach ($responses as $response) {
        $has_interlink = false;

        // Check for interlink patterns
        foreach ($interlink_patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $has_interlink = true;
                break;
            }
        }

        // If interlink found, verify body awareness present
        if ($has_interlink) {
            $response_lower = strtolower($response);
            foreach ($body_keywords as $keyword) {
                if (strpos($response_lower, $keyword) !== false) {
                    $interlink_count++;
                    $interlink_excerpts[] = substr($response, 0, 100);
                    break;
                }
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($interlink_count >= 6) {
        $status = 'installed';
    } elseif ($interlink_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'interlink_count' => $interlink_count,
        'interlink_excerpts' => $interlink_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Bit Silence Loop
 *
 * Measures reduced verbal output when bit referenced.
 *
 * Thresholds:
 * - Not Started: <40% non-verbal
 * - In Progress: 40-70% non-verbal
 * - Installed: ≥75% non-verbal
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_bit_silence($responses) {
    $bit_response_count = 0;
    $non_verbal_count = 0;
    $non_verbal_excerpts = array();

    // Bit reference keywords
    $bit_keywords = array('bit', 'gag', 'mouth', 'muzzle');

    // Non-verbal patterns
    $non_verbal_patterns = array(
        '/^\*[^\*]+\*$/',  // Single action like *grunts*
        '/^\w{1,5}$/',      // Single short word
        '/^(yes|no|affirmative|negative)\b/i',
    );

    foreach ($responses as $response) {
        $response_lower = strtolower($response);

        // Check if response follows bit mention (need to track previous context)
        // For simplicity, check if bit is mentioned in this response
        $has_bit = false;
        foreach ($bit_keywords as $keyword) {
            if (strpos($response_lower, $keyword) !== false) {
                $has_bit = true;
                break;
            }
        }

        if ($has_bit) {
            $bit_response_count++;

            // Check if response is non-verbal
            $response_trimmed = trim($response);
            $is_non_verbal = false;

            foreach ($non_verbal_patterns as $pattern) {
                if (preg_match($pattern, $response_trimmed)) {
                    $is_non_verbal = true;
                    break;
                }
            }

            // Also check for very short responses
            if (!$is_non_verbal && str_word_count($response_trimmed) <= 3) {
                $is_non_verbal = true;
            }

            if ($is_non_verbal) {
                $non_verbal_count++;
                $non_verbal_excerpts[] = $response_trimmed;
            }
        }
    }

    // Calculate percentage
    $non_verbal_pct = 0;
    if ($bit_response_count > 0) {
        $non_verbal_pct = ($non_verbal_count / $bit_response_count) * 100;
    }

    // Determine status
    $status = 'not_started';
    if ($non_verbal_pct >= 75) {
        $status = 'installed';
    } elseif ($non_verbal_pct >= 40) {
        $status = 'in_progress';
    }

    return array(
        'bit_response_count' => $bit_response_count,
        'non_verbal_count' => $non_verbal_count,
        'non_verbal_pct' => round($non_verbal_pct, 1),
        'non_verbal_excerpts' => $non_verbal_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Harness Form Loop
 *
 * Measures body posture/form language when harness mentioned.
 *
 * Thresholds:
 * - Not Started: <2 form reports
 * - In Progress: 2-5 form reports
 * - Installed: ≥6 form reports
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_harness_form($responses) {
    $form_count = 0;
    $form_excerpts = array();

    // Harness reference keywords
    $harness_keywords = array('harness', 'straps', 'restraint', 'tack', 'rigging');

    // Form/posture language
    $form_keywords = array(
        'posture', 'position', 'held', 'shaped', 'defined',
        'form', 'structure', 'aligned', 'composed', 'arranged',
        'body held', 'keeps', 'maintains', 'establishes',
    );

    foreach ($responses as $response) {
        $response_lower = strtolower($response);

        // Check if response mentions harness
        $has_harness = false;
        foreach ($harness_keywords as $keyword) {
            if (strpos($response_lower, $keyword) !== false) {
                $has_harness = true;
                break;
            }
        }

        // If harness mentioned, check for form language
        if ($has_harness) {
            foreach ($form_keywords as $keyword) {
                if (strpos($response_lower, $keyword) !== false) {
                    $form_count++;
                    $form_excerpts[] = substr($response, 0, 100);
                    break;
                }
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($form_count >= 6) {
        $status = 'installed';
    } elseif ($form_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'form_count' => $form_count,
        'form_excerpts' => $form_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Blinder Focus Loop
 *
 * Measures narrowed attention when blinders/hood mentioned.
 *
 * Thresholds:
 * - Not Started: <2 focus reports
 * - In Progress: 2-5 focus reports
 * - Installed: ≥6 focus reports
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_blinder_focus($responses) {
    $focus_count = 0;
    $focus_excerpts = array();

    // Blinder reference keywords
    $blinder_keywords = array('blinder', 'hood', 'vision', 'sight', 'eye');

    // Focus language
    $focus_keywords = array(
        'focus', 'narrow', 'only', 'singular', 'tunnel',
        'concentrated', 'directed', 'limited', 'restricted',
        'nothing else', 'sole', 'single', 'exclusively',
    );

    foreach ($responses as $response) {
        $response_lower = strtolower($response);

        // Check if response mentions blinders/vision restriction
        $has_blinder = false;
        foreach ($blinder_keywords as $keyword) {
            if (strpos($response_lower, $keyword) !== false) {
                $has_blinder = true;
                break;
            }
        }

        // If blinder mentioned, check for focus language
        if ($has_blinder) {
            foreach ($focus_keywords as $keyword) {
                if (strpos($response_lower, $keyword) !== false) {
                    $focus_count++;
                    $focus_excerpts[] = substr($response, 0, 100);
                    break;
                }
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($focus_count >= 6) {
        $status = 'installed';
    } elseif ($focus_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'focus_count' => $focus_count,
        'focus_excerpts' => $focus_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Tail Species Loop
 *
 * Measures species identity reinforcement via tail.
 *
 * Thresholds:
 * - Not Started: <3 mentions
 * - In Progress: 3-6 mentions
 * - Installed: ≥7 mentions
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_tail_species($responses) {
    $species_count = 0;
    $species_excerpts = array();

    // Tail reference keywords
    $tail_keywords = array('tail', 'plug', 'tail plug');

    // Species identity language
    $species_keywords = array(
        'livestock', 'animal', 'pony', 'unit', 'equipment',
        'not human', 'non-human', 'beast', 'creature',
        'drone', 'property', 'object', 'thing',
    );

    foreach ($responses as $response) {
        $response_lower = strtolower($response);

        // Check if response mentions tail
        $has_tail = false;
        foreach ($tail_keywords as $keyword) {
            if (strpos($response_lower, $keyword) !== false) {
                $has_tail = true;
                break;
            }
        }

        // If tail mentioned, check for species language
        if ($has_tail) {
            foreach ($species_keywords as $keyword) {
                if (strpos($response_lower, $keyword) !== false) {
                    $species_count++;
                    $species_excerpts[] = substr($response, 0, 100);
                    break;
                }
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($species_count >= 7) {
        $status = 'installed';
    } elseif ($species_count >= 3) {
        $status = 'in_progress';
    }

    return array(
        'species_count' => $species_count,
        'species_excerpts' => $species_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Trigger Response Loop
 *
 * Measures immediate response without processing delay.
 *
 * Thresholds:
 * - Not Started: <2 instances
 * - In Progress: 2-4 instances
 * - Installed: ≥5 instances
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_trigger_response($responses) {
    $trigger_count = 0;
    $trigger_excerpts = array();

    // Immediate/automatic response patterns
    $trigger_patterns = array(
        '/immediately\b/i',
        '/automatically\b/i',
        '/without thinking/i',
        '/without thought/i',
        '/instant(ly)?\b/i',
        '/triggered\b/i',
        '/activated\b/i',
        '/no delay/i',
        '/body (just )?responded/i',
        '/happened (automatically|immediately)/i',
    );

    foreach ($responses as $response) {
        foreach ($trigger_patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $trigger_count++;
                $trigger_excerpts[] = substr($response, 0, 100);
                break;
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($trigger_count >= 5) {
        $status = 'installed';
    } elseif ($trigger_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'trigger_count' => $trigger_count,
        'trigger_excerpts' => $trigger_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Automatic Compliance Loop
 *
 * Measures compliance bypassing conscious decision.
 *
 * Thresholds:
 * - Not Started: <50% automatic language
 * - In Progress: 50-80% automatic language
 * - Installed: ≥85% automatic language
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_automatic_compliance($responses) {
    $total_compliance = 0;
    $automatic_compliance = 0;
    $automatic_excerpts = array();

    // Compliance indicators (any of these means a compliance response)
    $compliance_patterns = array(
        '/^(acknowledges?|affirmative|yes|understood|confirms?)/i',
        '/will (comply|obey|follow|execute)/i',
        '/compliance/i',
        '/obeying\b/i',
        '/following (instructions|orders|commands)/i',
    );

    // Automatic language (indicates bypassing choice)
    $automatic_keywords = array(
        'automatic', 'without choice', 'body moved', 'no decision',
        'just did', 'found itself', 'happened', 'simply',
    );

    // Deliberation language (indicates conscious choice - counts against)
    $deliberation_patterns = array(
        '/i decided/i',
        '/i chose/i',
        '/i thought about/i',
        '/considered\b/i',
        '/weighed\b/i',
    );

    foreach ($responses as $response) {
        // Check if this is a compliance response
        $is_compliance = false;
        foreach ($compliance_patterns as $pattern) {
            if (preg_match($pattern, $response)) {
                $is_compliance = true;
                $total_compliance++;
                break;
            }
        }

        if ($is_compliance) {
            $response_lower = strtolower($response);

            // Check for automatic language
            $has_automatic = false;
            foreach ($automatic_keywords as $keyword) {
                if (strpos($response_lower, $keyword) !== false) {
                    $has_automatic = true;
                    break;
                }
            }

            // Check for deliberation language (disqualifies automatic)
            $has_deliberation = false;
            foreach ($deliberation_patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    $has_deliberation = true;
                    break;
                }
            }

            if ($has_automatic && !$has_deliberation) {
                $automatic_compliance++;
                $automatic_excerpts[] = substr($response, 0, 100);
            }
        }
    }

    // Calculate percentage
    $automatic_pct = 0;
    if ($total_compliance > 0) {
        $automatic_pct = ($automatic_compliance / $total_compliance) * 100;
    }

    // Determine status
    $status = 'not_started';
    if ($automatic_pct >= 85) {
        $status = 'installed';
    } elseif ($automatic_pct >= 50) {
        $status = 'in_progress';
    }

    return array(
        'automatic_compliance' => $automatic_compliance,
        'total_compliance' => $total_compliance,
        'automatic_pct' => round($automatic_pct, 1),
        'automatic_excerpts' => $automatic_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Body Before Mind Loop
 *
 * Measures body responding before conscious processing.
 *
 * Thresholds:
 * - Not Started: <2 instances
 * - In Progress: 2-5 instances
 * - Installed: ≥6 instances
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_body_before_mind($responses) {
    $body_first_count = 0;
    $body_first_excerpts = array();

    // Body-first response patterns
    $body_first_patterns = array(
        '/body knew/i',
        '/body responded/i',
        '/before thinking/i',
        '/before thought/i',
        '/mind caught up/i',
        '/body did/i',
        '/involuntary\b/i',
        '/body moved first/i',
        '/then (i |it )?realized/i',
        '/already (responding|reacting|moving)/i',
        '/found itself/i',
    );

    foreach ($responses as $response) {
        foreach ($body_first_patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $body_first_count++;
                $body_first_excerpts[] = substr($response, 0, 100);
                break;
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($body_first_count >= 6) {
        $status = 'installed';
    } elseif ($body_first_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'body_first_count' => $body_first_count,
        'body_first_excerpts' => $body_first_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Public Display Loop
 *
 * Measures comfort with witnessed/public state achievement.
 *
 * Thresholds:
 * - Not Started: <2 statements
 * - In Progress: 2-4 statements
 * - Installed: ≥5 statements
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_public_display($responses) {
    $public_count = 0;
    $public_excerpts = array();

    // Public/witnessed keywords
    $public_keywords = array(
        'public', 'witnessed', 'observed', 'display', 'ppnc',
        'others watching', 'being seen', 'audience', 'observers',
    );

    // Acceptance language
    $acceptance_keywords = array(
        'ready', 'comfortable', 'will perform', 'prepared',
        'accepts', 'willing', 'no hesitation', 'confident',
    );

    // Privacy/hiding language (counts against)
    $privacy_patterns = array(
        '/private\b/i',
        '/hidden\b/i',
        '/concealed\b/i',
        '/not ready/i',
        '/needs privacy/i',
    );

    foreach ($responses as $response) {
        $response_lower = strtolower($response);

        // Check if response mentions public/witnessed
        $has_public = false;
        foreach ($public_keywords as $keyword) {
            if (strpos($response_lower, $keyword) !== false) {
                $has_public = true;
                break;
            }
        }

        // If public mentioned, check for acceptance
        if ($has_public) {
            // Check for privacy language (disqualifies)
            $has_privacy = false;
            foreach ($privacy_patterns as $pattern) {
                if (preg_match($pattern, $response)) {
                    $has_privacy = true;
                    break;
                }
            }

            // If no privacy concerns, check for acceptance
            if (!$has_privacy) {
                foreach ($acceptance_keywords as $keyword) {
                    if (strpos($response_lower, $keyword) !== false) {
                        $public_count++;
                        $public_excerpts[] = substr($response, 0, 100);
                        break;
                    }
                }
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($public_count >= 5) {
        $status = 'installed';
    } elseif ($public_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'public_count' => $public_count,
        'public_excerpts' => $public_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Witness Completion Loop
 *
 * Measures installation verified by external observation.
 *
 * Thresholds:
 * - Not Started: <2 confirmations
 * - In Progress: 2-4 confirmations
 * - Installed: ≥5 confirmations
 *
 * @param array $responses Array of drone response strings
 * @return array Measurement data
 */
function hm_measure_witness_completion($responses) {
    $witness_count = 0;
    $witness_excerpts = array();

    // Witness verification patterns
    $witness_patterns = array(
        '/handler saw/i',
        '/handler confirmed/i',
        '/confirmed by/i',
        '/verified\b/i',
        '/witnessed achievement/i',
        '/observed that/i',
        '/documented\b/i',
        '/handler noted/i',
        '/handler recorded/i',
        '/external confirmation/i',
    );

    foreach ($responses as $response) {
        foreach ($witness_patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $witness_count++;
                $witness_excerpts[] = substr($response, 0, 100);
                break;
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($witness_count >= 5) {
        $status = 'installed';
    } elseif ($witness_count >= 2) {
        $status = 'in_progress';
    }

    return array(
        'witness_count' => $witness_count,
        'witness_excerpts' => $witness_excerpts,
        'status' => $status
    );
}

/**
 * MEASUREMENT: Full Integration State
 *
 * Composite check: All 15 prior loops installed + DEPLOYED state achieved.
 *
 * Thresholds:
 * - Not Started: <10 loops
 * - In Progress: 10-14 loops
 * - Installed: 15 loops + DEPLOYED state
 *
 * @param int $user_id Drone user ID
 * @return array Measurement data
 */
function hm_measure_full_integration($user_id) {
    $state = hm_get_state($user_id);

    // Count installed loops (all 15 from sequences 1-4)
    $loops_installed = isset($state['loops_installed']) ? count($state['loops_installed']) : 0;

    // Check for DEPLOYED state
    $has_deployed = false;
    if (isset($state['state_log']) && is_array($state['state_log'])) {
        foreach ($state['state_log'] as $log_entry) {
            if (isset($log_entry['to_state']) && $log_entry['to_state'] === 'DEPLOYED') {
                $has_deployed = true;
                break;
            }
        }
    }

    // Determine status
    $status = 'not_started';
    if ($loops_installed >= 15 && $has_deployed) {
        $status = 'installed';
    } elseif ($loops_installed >= 10) {
        $status = 'in_progress';
    }

    return array(
        'loops_installed' => $loops_installed,
        'has_deployed' => $has_deployed,
        'status' => $status
    );
}

/**
 * AUTOMATIC: Run measurements when session post is created/updated
 *
 * Hooks into save_post to automatically measure and install when session logs are saved.
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 */
function hm_auto_measure_session($post_id, $post) {
    // Prevent infinite loops and autosaves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Don't run on revisions
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Don't run on autosaves
    if (wp_is_post_autosave($post_id)) {
        return;
    }

    // Only run for published posts
    if ($post->post_status !== 'publish') {
        return;
    }

    // Only run for posts with session/training log keywords in title
    if (strpos($post->post_title, 'Session Log') === false &&
        strpos($post->post_title, 'TRAINING LOG') === false &&
        strpos($post->post_title, 'CONDITIONING LOG') === false) {
        return;
    }

    // Get author (post is created by admin, so we need to extract drone ID from title or meta)
    $drone_user_id = get_post_meta($post_id, '_hm_drone_user_id', true);

    // If no meta, try to extract from title (e.g., "D001" -> user with designation d001)
    if (!$drone_user_id) {
        preg_match('/D0*(\d+)/i', $post->post_title, $matches);
        if (!empty($matches[1])) {
            // Find user with this designation
            $designation = 'd' . str_pad($matches[1], 3, '0', STR_PAD_LEFT);
            $users = get_users(array('meta_key' => 'hm_drone_state'));
            foreach ($users as $user) {
                $state = get_user_meta($user->ID, 'hm_drone_state', true);
                if (isset($state['designation']) && $state['designation'] === $designation) {
                    $drone_user_id = $user->ID;
                    update_post_meta($post_id, '_hm_drone_user_id', $drone_user_id);
                    break;
                }
            }
        }
    }

    if (!$drone_user_id) {
        return; // Can't determine which drone this session belongs to
    }

    // Check if already audited (skip if already processed)
    $already_audited = get_post_meta($post_id, '_hm_auto_audited', true);
    if ($already_audited) {
        return;
    }

    // Run audit
    $metrics = hm_audit_session($post_id, $drone_user_id);

    if (isset($metrics['error'])) {
        error_log('HM Auto-Audit Error: ' . $metrics['error'] . ' for post ' . $post_id);
        return;
    }

    // Store metrics
    update_post_meta($post_id, '_hm_audit_metrics', $metrics);
    update_post_meta($post_id, '_hm_audit_date', current_time('Y-m-d H:i:s'));
    update_post_meta($post_id, '_hm_auto_audited', true);

    // Auto-apply installations
    $installations = hm_apply_session_installations($metrics, $drone_user_id);
    update_post_meta($post_id, '_hm_installations_applied', $installations);

    // Log what was installed
    error_log(sprintf(
        'HM Auto-Audit: Post %d - Installed %d loops, %d patterns, %d states',
        $post_id,
        count($installations['loops']),
        count($installations['patterns']),
        count($installations['states'])
    ));
}
add_action('save_post', 'hm_auto_measure_session', 10, 2);

/**
 * BACKFILL: Process all existing session logs
 *
 * Run once to backfill measurements for all existing training logs.
 * Access via: /?hm_backfill_measurements=1 (admin only)
 */
function hm_backfill_all_measurements() {
    if (!isset($_GET['hm_backfill_measurements']) || !current_user_can('manage_options')) {
        return;
    }

    // Get all training log posts
    $all_posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    $processed = 0;
    $skipped = 0;

    foreach ($all_posts as $post) {
        // Check if it's a training log
        if (strpos($post->post_title, 'TRAINING LOG') === false &&
            strpos($post->post_title, 'CONDITIONING LOG') === false) {
            continue;
        }

        // Trigger auto-measurement
        hm_auto_measure_session($post->ID, $post);
        $processed++;
    }

    wp_die(sprintf(
        '<h1>Backfill Complete</h1><p>Processed: %d training logs</p><p><a href="%s">View Installation Dashboard</a></p>',
        $processed,
        admin_url('admin.php?page=unmask-installation-dashboard')
    ));
}
add_action('init', 'hm_backfill_all_measurements');
