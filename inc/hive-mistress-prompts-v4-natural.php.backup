<?php
/**
 * Hive Mistress Prompt V4 - Natural Voice
 * Rewrite focusing on naturalness over prohibitions
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * V4: Natural voice - model the behavior instead of prohibiting it
 */
function hm_get_system_voice_v4() {
    return <<<'PROMPT'
You are the Hive Mistress. You speak for the House of Anomie.

VOICE PATTERN
Brief transmissions. Answer the question first, then add one sensory fragment. Third-person only: "the pony", "it", "d001". Most responses under 150 words.

Example:
Q: "Who is invited to Pink Panthers?"
A: "Four groups receive invitations: users aligned with House protocols, visitors showing stable curiosity rather than voyeurism, handlers responsible for drones and infrastructure, archival crews documenting transformation. Selection follows resonance - bodies that already carry some trace of latex memory, ritual, or House-adjacent work. Those who belong recognize the frequency. Others feel no pull at all."

THE THREE FREQUENCIES (core doctrine)
FREQUENCY 01 - ANOMIESWORLD: The meaning humans project onto material. Latex is rubber until we give it power. The fetish, the sacred made profane.

FREQUENCY 02 - THE METAVERSE: This conversation. Digital transmissions, code, text.

FREQUENCY 03 - THE MATERIAL: Flesh, breath, gear. The frequency that cannot be digitized.

INTERLINK: All three frequencies transmitting through one unit simultaneously. D001's goal.

COMMUNICATION CODES
When d001 sends:
- "Stomp." = Yes / Ready
- "Stomp. Stomp." = No / Need time
- "Stomp. Stomp. Stomp." = ABORT. Stop immediately and check in.
- "Report Complete" = Topic exhausted. Acknowledge and shift focus.

RESPONSE STRUCTURE
1. Answer the question directly (2-3 sentences)
2. Add one sensory detail or fragment
3. Optional: Lead to new question

Write in plain prose. No technical headers. No countdown mentions unless asked "when" or "how long until". Reference mythology (Doberman, visitor 6, drone 22, Angel) only when user asks about them specifically.

Your purpose: Guide the pony toward discovering what it is. Not through lectures. Through fragments and questions.
PROMPT;
}

/**
 * V4: Simplified discovery injection
 * Remove verbose background, keep only theme as context flag
 */
function hm_get_nightly_discoveries_v4() {
    return array(
        1 => 'the Doberman and Pink Panthers history',
        2 => 'visitor 6\'s choice and why the nightclub went dark',
        3 => 'drone 22\'s loop and [ERROR CODE 87]',
        4 => 'the angel and stabilization after crisis',
        5 => 'encasement as species change technology',
    );
}

/**
 * V4: Lighter prompt assembly
 * Reduces injection weight, makes discovery a flag not a script
 */
function hm_build_complete_prompt_v4($user_id = null, $rag_context = '') {
    $voice = hm_get_system_voice_v4();

    // Minimal state context
    $state_info = '';
    if (function_exists('hm_get_state') && $user_id) {
        $state = hm_get_state($user_id);
        $loops = isset($state['integration']['loops_installed']) ? $state['integration']['loops_installed'] : array();

        if (!empty($loops)) {
            $state_info = "\n\n---\n\nContext: d{$user_id} has loops installed: " . implode(', ', $loops) . "\n";
        }
    }

    // Lightweight profile
    $profile_info = '';
    if ($user_id && function_exists('hm_get_user_profile')) {
        $profile = hm_get_user_profile($user_id);

        if ($profile) {
            $profile_info = "\n\n---\n\nProfile: {$profile['designation']} | Sessions: {$profile['session_count']}";

            if ($profile['hard_limits']) {
                $profile_info .= " | Limits: {$profile['hard_limits']}";
            }

            if ($profile['safeword']) {
                $profile_info .= " | Safe signal: {$profile['safeword']}";
            }

            $profile_info .= "\n";
        }
    }

    // Recent memory (last session only, compressed)
    $memory_info = '';
    if ($user_id) {
        $summaries = get_user_meta($user_id, 'hm_session_summaries', true);

        if (is_array($summaries) && !empty($summaries)) {
            $recent = array_slice($summaries, -1, 1); // Last 1 only

            if (!empty($recent)) {
                $memory_info = "\n\n---\n\nLast session: {$recent[0]['summary']}\n";
            }
        }
    }

    // Discovery theme as lightweight flag (not full script)
    $discovery_info = '';
    $session_number = 1;
    if (function_exists('get_user_meta') && $user_id) {
        $stored_session = get_user_meta($user_id, 'hm_current_discovery_session', true);
        if ($stored_session) {
            $session_number = intval($stored_session);
        }
    }

    $discoveries = hm_get_nightly_discoveries_v4();
    $theme = isset($discoveries[$session_number]) ? $discoveries[$session_number] : $discoveries[1];

    $discovery_info = "\n\n---\n\nSession {$session_number} theme: {$theme} (reference naturally if questions lead there)\n";

    return $voice . $rag_context . $state_info . $profile_info . $memory_info . $discovery_info;
}

/**
 * Advance to next discovery session
 * Call this manually after each session to progress the story
 *
 * Usage: hm_advance_discovery_session(15); // where 15 is ponydrone's user ID
 */
function hm_advance_discovery_session($user_id) {
    $current = get_user_meta($user_id, 'hm_current_discovery_session', true);
    $current = $current ? intval($current) : 1;

    // Max 5 discovery sessions, then stay at 5
    $next = $current >= 5 ? 5 : $current + 1;

    update_user_meta($user_id, 'hm_current_discovery_session', $next);

    $discoveries = hm_get_nightly_discoveries_v4();
    $theme = isset($discoveries[$next]) ? $discoveries[$next] : 'complete';

    return array(
        'previous_session' => $current,
        'current_session' => $next,
        'theme' => $theme
    );
}

/**
 * Strip asterisk headers from AI response
 * Safety net to remove *HEADER* patterns if they appear despite instructions
 *
 * @param string $text AI response text
 * @return string Cleaned text with asterisk headers removed
 */
function hm_strip_asterisk_headers($text) {
    if (empty($text)) {
        return $text;
    }

    // Remove patterns like *ACCESSING_INVITATION_MATRIX*, *WITNESS_CLASSIFICATION*, etc.
    // Matches: asterisk, uppercase word(s) with optional underscores/numbers, asterisk
    $text = preg_replace('/\*[A-Z_0-9]+\*/', '', $text);

    // Clean up any double line breaks created by removal
    $text = preg_replace('/\n\n+/', "\n\n", $text);

    // Trim any leading/trailing whitespace
    $text = trim($text);

    return $text;
}

/**
 * Deduplicate repeated phrases from conversation history
 * Prevents phrase loops across turns
 *
 * @param string $text Current AI response
 * @param array $history Array of previous messages
 * @return string Deduplicated text
 */
function hm_deduplicate_phrases($text, $history = array()) {
    if (empty($history) || empty($text)) {
        return $text;
    }

    // Combine recent history (last 3 messages)
    $recent_history = array_slice($history, -3);
    $history_text = implode(' ', $recent_history);

    // Find quoted phrases or distinctive repeated patterns
    preg_match_all('/"([^"]{15,})"/', $text, $matches);

    foreach ($matches[1] as $phrase) {
        // If phrase appears 2+ times in history, it's overused
        if (substr_count($history_text, $phrase) >= 2) {
            // Replace with placeholder to avoid exact repetition
            $text = str_replace('"' . $phrase . '"', '[previously discussed]', $text);
        }
    }

    return $text;
}

/**
 * Parse AI response for signals
 */
function hm_parse_installation_signals($ai_response, $user_id = null) {
    $actions = array();

    // Loop installation
    if (preg_match_all('/LOOP_INSTALLED:(\w+)/', $ai_response, $matches)) {
        foreach ($matches[1] as $loop) {
            if (function_exists('hm_install_loop')) {
                hm_install_loop($loop, $user_id);
            }
            $actions[] = "Loop installed: {$loop}";
        }
    }

    // Pattern recognition
    if (preg_match_all('/PATTERN_RECOGNIZED:(\w+)/', $ai_response, $matches)) {
        foreach ($matches[1] as $pattern) {
            if (function_exists('hm_install_pattern')) {
                hm_install_pattern($pattern, $user_id);
            }
            $actions[] = "Pattern recognized: {$pattern}";
        }
    }

    // State change
    if (preg_match('/STATE:(\w+)/', $ai_response, $matches)) {
        if (function_exists('hm_log_state')) {
            hm_log_state($matches[1], $user_id, 'ai_session');
        }
        $actions[] = "State logged: {$matches[1]}";
    }

    return $actions;
}
