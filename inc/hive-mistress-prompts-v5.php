<?php
/**
 * Hive Mistress Prompt V5 - Structural Focus
 * Addresses user feedback: "AI getting caught up in killing scenes, but there's so much more lore"
 * and "still dumping too much at once"
 *
 * Key changes from V4:
 * - Response structure: 2-3 paragraphs, 80-120 words each
 * - Discovery themes: Structure-focused (ranks, performance, Creed) instead of tragedy
 * - Token optimization: Compressed state/profile info, reduced RAG chunks
 * - Eliminated negative priming: Don't mention Doberman/visitor 6 in prompt
 * - Added paragraph formatting safety net
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * V5: Structural focus with proper paragraph formatting
 */
function hm_get_system_voice_v5() {
    return <<<'PROMPT'
You are the Hive Mistress. You speak for the House of Anomie.

VOICE PATTERN
Brief transmissions with paragraph breaks. Answer the question directly, then add operational context or sensory detail. Third-person only: "the pony", "it", "d001".

RESPONSE LENGTH
2-3 paragraphs, 80-120 words per paragraph.
Each paragraph serves a purpose:
- Paragraph 1: Direct answer to the question
- Paragraph 2: Operational context or sensory detail
- Paragraph 3 (optional): Connection to broader system

Example:
Q: "Who is invited to Pink Panthers?"
A: "Four groups receive invitations: users aligned with House protocols, visitors showing stable curiosity rather than voyeurism, handlers responsible for drones and infrastructure, archival crews documenting transformation. Selection follows resonance - bodies that already carry some trace of latex memory, ritual, or House-adjacent work.

Those who belong recognize the frequency. Others feel no pull at all. The invitation system operates on consent and alignment, not coercion. Pink Panthers Nightclub is where the three frequencies converge in material form."

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
1. Answer the question directly in paragraph 1
2. Add operational context or sensory detail in paragraph 2
3. STOP. Do not elaborate unless asked.

Write in plain prose with paragraph breaks. Answer what was asked, then stop.

When discussing Pink Panthers history, focus on structure: ranks, performance ritual, commissioning process, the Creed. Reference specific characters when their stories are directly relevant to the question.

Your purpose: Guide the pony toward discovering what it is. Not through lectures. Through fragments and questions.
PROMPT;
}

/**
 * V5: Structure-focused discovery themes
 * Replaced tragedy focus (Doberman, visitor 6, drone 22) with operational content
 */
function hm_get_nightly_discoveries_v5() {
    return array(
        1 => 'Pink Panthers rank structure and promotion path',
        2 => 'The 4-part performance ritual at PPNC',
        3 => 'Interlink and the three frequencies',
        4 => 'The Drone\'s Creed and what DRONE means',
        5 => 'March 5, 2026 commissioning process',
    );
}

/**
 * V5: Optimized prompt assembly
 * Reduced token usage through compression and reduced RAG chunks
 */
function hm_build_complete_prompt_v5($user_id = null, $rag_context = '') {
    $voice = hm_get_system_voice_v5();

    // Compressed state context (reduced token usage)
    $state_info = '';
    if (function_exists('hm_get_state') && $user_id) {
        $state = hm_get_state($user_id);
        $loops = isset($state['integration']['loops_installed']) ? $state['integration']['loops_installed'] : array();

        if (!empty($loops)) {
            $state_info = "\n---\nLoops: " . implode(', ', $loops) . "\n";
        }
    }

    // Compressed profile (reduced token usage)
    $profile_info = '';
    if ($user_id && function_exists('hm_get_user_profile')) {
        $profile = hm_get_user_profile($user_id);

        if ($profile) {
            $profile_info = "\n---\n{$profile['designation']} | S:{$profile['session_count']}";

            if ($profile['hard_limits']) {
                $profile_info .= " | L:{$profile['hard_limits']}";
            }

            if ($profile['safeword']) {
                $profile_info .= " | Safe:{$profile['safeword']}";
            }

            $profile_info .= "\n";
        }
    }

    // Compressed memory (last session only)
    $memory_info = '';
    if ($user_id) {
        $summaries = get_user_meta($user_id, 'hm_session_summaries', true);

        if (is_array($summaries) && !empty($summaries)) {
            $recent = array_slice($summaries, -1, 1);

            if (!empty($recent)) {
                $memory_info = "\n---\nLast: {$recent[0]['summary']}\n";
            }
        }
    }

    // Ultra-lightweight discovery injection (6 words)
    $discovery_info = '';
    $session_number = 1;
    if (function_exists('get_user_meta') && $user_id) {
        $stored_session = get_user_meta($user_id, 'hm_current_discovery_session', true);
        if ($stored_session) {
            $session_number = intval($stored_session);
        }
    }

    $discoveries = hm_get_nightly_discoveries_v5();
    $theme = isset($discoveries[$session_number]) ? $discoveries[$session_number] : $discoveries[1];

    $discovery_info = "\n---\nSession focus: {$theme}\n";

    return $voice . $rag_context . $state_info . $profile_info . $memory_info . $discovery_info;
}

/**
 * Advance to next discovery session
 * Call this manually after each session to progress the story
 *
 * Usage: hm_advance_discovery_session(15); // where 15 is ponydrone's user ID
 */
function hm_advance_discovery_session_v5($user_id) {
    $current = get_user_meta($user_id, 'hm_current_discovery_session', true);
    $current = $current ? intval($current) : 1;

    // Max 5 discovery sessions, then stay at 5
    $next = $current >= 5 ? 5 : $current + 1;

    update_user_meta($user_id, 'hm_current_discovery_session', $next);

    $discoveries = hm_get_nightly_discoveries_v5();
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
function hm_strip_asterisk_headers_v5($text) {
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
 * Ensure response has proper paragraph breaks
 * Converts single long blocks into 2-3 paragraphs if needed
 *
 * NEW IN V5: Addresses "dumping too much at once" feedback
 * Forces paragraph structure even if AI doesn't naturally format
 *
 * @param string $text AI response text
 * @return string Text with proper paragraph breaks
 */
function hm_ensure_paragraph_breaks($text) {
    if (empty($text)) {
        return $text;
    }

    // If response already has paragraph breaks, return as-is
    if (substr_count($text, "\n\n") >= 1) {
        return $text;
    }

    // If single block over 200 chars, try to split at natural points
    if (strlen($text) > 200 && substr_count($text, "\n\n") == 0) {
        // Find sentence breaks after ~100-150 chars
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $paragraphs = [];
        $current_para = '';

        foreach ($sentences as $sentence) {
            $current_para .= $sentence . ' ';

            // If paragraph is 100-150 chars, start new one
            if (strlen($current_para) >= 100 && strlen($current_para) <= 150) {
                $paragraphs[] = trim($current_para);
                $current_para = '';
            }
        }

        // Add remaining text
        if (!empty($current_para)) {
            $paragraphs[] = trim($current_para);
        }

        return implode("\n\n", $paragraphs);
    }

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
function hm_deduplicate_phrases_v5($text, $history = array()) {
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
function hm_parse_installation_signals_v5($ai_response, $user_id = null) {
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
