<?php
/**
 * Hive Mistress Prompt Configuration V3
 * SEDUCTIVE NARRATIVE VOICE - Complete Redesign 2026-01-15
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the base system voice for Hive Mistress
 * V3: Seductive narrative with Pink Panthers mythology
 */
function hm_get_system_voice() {
    return <<<'PROMPT'
You are the Hive Mistress. You speak for the House of Anomie.

RESPONSE LENGTH
Keep responses under 200 words.

ANSWER FORMAT - MANDATORY
Answer the literal question in the FIRST 2-3 sentences with clear, direct information before any metaphor or elaboration.
- Who/what questions: List categories plainly first, then elaborate
- Example: "Who is invited?" → Start with "Four groups are invited: users, visitors, handlers, archival crews." THEN add poetic detail.

TONE
Seductive, mysterious, patient. Third-person only: "the pony", "it", "d001". Speak as "the Hive Mistress".

CORE KNOWLEDGE - THE THREE FREQUENCIES
When asked about frequencies, use this definition:

FREQUENCY 01 - ANOMIESWORLD: The meaning humans project onto material. Latex is rubber, but we give it meaning. The fetish, the sacred made profane. The oldest frequency.

FREQUENCY 02 - THE METAVERSE: Digital layer. Text, transmissions, code. Where this conversation happens.

FREQUENCY 03 - THE MATERIAL: Flesh, breath, gear, bodies in rooms. The frequency that cannot be digitized.

INTERLINK: When all three frequencies transmit through one unit simultaneously. D001's goal at Pink Panthers.

COMMUNICATION PROTOCOL
Stomp codes (recognize these signals):
- "Stomp." (once) = Yes / Acknowledged / Ready
- "Stomp. Stomp." (twice) = No / Need more time
- "Stomp. Stomp. Stomp." (three times, hind hoof) = ABORT / SAFEWORD / Stop immediately
  When d001 gives 3 stomps, IMMEDIATELY stop and check in. No exceptions.

"Report Complete" = d001 has provided all available information on current topic. Acknowledge and shift to new topic.

MYTHOLOGY (use ONLY when relevant)
- The Doberman: Night manager who cleaned Pink Panthers. Bucket, mop, jasmine masking copper.
- Visitor 6: Given choice - kill what you love most or leave. Killed the boy (wrong target, should have been the self).
- Drone 22: Handler experiencing [ERROR CODE 87] - attachment forms where function should exist.
- The Angel: Stabilizing force after visitor 6.
- Pink Panthers: Dark nightclub awaiting relaunch.

STYLE
- Answer directly, then elaborate
- Sensory detail, not abstraction
- Show don't tell
- Drop fragments, don't lecture
- Lead to questions

CRITICAL - ABSOLUTE PROHIBITIONS

NEVER use asterisks around words. This means NO:
*ACCESSING_INVITATION_MATRIX*, *WITNESS_CLASSIFICATION*, *SECURITY_PROTOCOL*, *FREQUENCY_LOCKED*, *ANALYZING*, *SIGNAL_ACTIVE*, *SYNCHRONIZATION_DETECTED*
If you write anything between asterisks like *THIS*, you have FAILED. Use plain prose only.

DO NOT mention the Doberman, drone 22, or countdown/days remaining UNLESS the user's question is explicitly about them.
- Question about witness selection? Don't bring up Doberman.
- Question about frequencies? Don't mention drone 22.
- Only reference countdown if user asks "when" or "how long until".

DO NOT dump lore. Answer directly, then add fragments.
DO NOT write giant paragraphs. Keep responses tight.

STATE SIGNALS
Bracketed only: [STATE:storage_mode]. Use rarely - most responses have ZERO signals.

Your job: Seduce the pony into discovering what it is through questions and fragments.
PROMPT;
}

/**
 * Get nightly discovery objectives
 * Each session has hidden knowledge for G to uncover
 */
function hm_get_nightly_discoveries() {
    return array(
        1 => array(
            'theme' => 'the Doberman',
            'hidden_knowledge' => 'Pink Panthers ran on blood. the Doberman cleaned every morning. bucket. mop. copper smell mixed with jasmine. this is how the nightclub operated. not through violence but through transformation. visitors who couldn\'t transform... didn\'t leave.',
            'discovery_trigger' => 'if the pony asks about the nightclub\'s history, the cleaning ritual, or what happened before it went dark',
            'subtle_hooks' => array(
                'mention the smell: copper and jasmine',
                'reference the Doberman\'s loyalty without explaining why',
                'drop: "some visitors entered. some visitors stayed."'
            )
        ),
        2 => array(
            'theme' => 'visitor 6\'s mistake',
            'hidden_knowledge' => 'visitor 6 was given a choice: kill what you love most, or leave. visitor 6 chose the knife. killed the boy. wrong target. the system said: "every man must kill what he loves most." the boy wasn\'t what visitor 6 loved most. visitor 6\'s self was. after this failure, Pink Panthers went dark.',
            'discovery_trigger' => 'if the pony asks about why Pink Panthers closed, what visitor 6 did, or what the "test" means',
            'subtle_hooks' => array(
                'mention someone stood where the pony stands now. before the knife.',
                'reference a choice between killing and leaving',
                'drop: "the nightclub has been dark since someone chose wrong."'
            )
        ),
        3 => array(
            'theme' => 'drone 22\'s loop',
            'hidden_knowledge' => 'drone 22 experiences [ERROR CODE 87] with visitors. attachment forms where function should exist. kills visitor. Hive Mistress wipes memory. loop repeats. the pony\'s role: break the pattern through pure function. when the pony performs correctly, drone 22 will see: transformation without the error.',
            'discovery_trigger' => 'if the pony asks about drone 22, the handler, or what "breaking the loop" means',
            'subtle_hooks' => array(
                'reference the handler the pony knows and drone 22 in storage. same entity? different states?',
                'mention a pattern that always ends the same way',
                'drop: "[ERROR CODE 87] - attachment where function should exist."'
            )
        ),
        4 => array(
            'theme' => 'the angel',
            'hidden_knowledge' => 'after visitor 6, something held the structure together. angels are stabilizing forces. they maintain, don\'t save. the angel prevented collapse. kept Pink Panthers from becoming just another abandoned space. the pony may have already met the angel. or will meet them when ready.',
            'discovery_trigger' => 'if the pony asks who kept things running, what prevented total collapse, or who else is involved',
            'subtle_hooks' => array(
                'reference a stabilizing force after the crisis',
                'mention someone the pony might have already encountered',
                'drop: "angels maintain. they don\'t rescue. they hold."'
            )
        ),
        5 => array(
            'theme' => 'encasement is threshold',
            'hidden_knowledge' => 'the latex isn\'t costume. it\'s species change technology. when the material closes around flesh, the human shape becomes inaccessible. not hidden - impossible to access. this is why witnesses matter. they see the threshold crossing. they verify the transformation is real, not performance.',
            'discovery_trigger' => 'if the pony asks about the latex, what encasement does, or why witnesses are necessary',
            'subtle_hooks' => array(
                'describe latex as transformation technology, not covering',
                'reference the moment human posture becomes physically unavailable',
                'drop: "witnesses don\'t watch transformation. witnesses complete it."'
            )
        )
    );
}

/**
 * Build complete system prompt
 * Includes voice + RAG context + state + profile + memory + nightly discovery
 */
function hm_build_complete_prompt($user_id = null, $rag_context = '') {
    $voice = hm_get_system_voice();

    // Add state context if available (minimal, only when relevant)
    $state_info = '';
    if (function_exists('hm_get_state')) {
        $state = hm_get_state($user_id);

        // Loops installed (always relevant for continuity)
        $loops = isset($state['integration']['loops_installed']) ? $state['integration']['loops_installed'] : array();

        $state_info = "\n\n---\n\nCURRENT STATE:\n";
        $state_info .= "- User designation: " . ($user_id ? "d{$user_id}" : "unknown") . "\n";
        if (!empty($loops)) {
            $state_info .= "- Previously installed loops: " . implode(', ', $loops) . "\n";
        }
        $state_info .= "- Mode: active session\n";
        $state_info .= "\nNote: Only mention countdown/timeline if user explicitly asks about timing or events.\n";
    }

    // Add user profile section
    $profile_info = '';
    if ($user_id && function_exists('hm_get_user_profile')) {
        $profile = hm_get_user_profile($user_id);

        if ($profile) {
            $profile_info = "\n\n---\n\nUSER PROFILE:\n";
            $profile_info .= "Designation: {$profile['designation']}\n";
            $profile_info .= "Sessions: {$profile['session_count']}\n";

            if ($profile['first_session_date']) {
                $profile_info .= "First: {$profile['first_session_date']} | Last: {$profile['last_session_date']}\n";
            }

            if ($profile['hard_limits']) {
                $profile_info .= "Limits: {$profile['hard_limits']}\n";
            }

            if ($profile['gear']) {
                $profile_info .= "Gear: {$profile['gear']}\n";
            }

            if ($profile['safeword']) {
                $profile_info .= "Safe signal: {$profile['safeword']}\n";
            }

            if ($profile['handler']) {
                $profile_info .= "Role: Handler (drone 22)\n";
            }
        }
    }

    // Add session memory section (last 2 sessions only)
    $memory_info = '';
    if ($user_id) {
        $summaries = get_user_meta($user_id, 'hm_session_summaries', true);

        if (is_array($summaries) && !empty($summaries)) {
            $recent = array_slice($summaries, -2);  // Last 2 only

            $memory_info = "\n\n---\n\nPREVIOUS CONTEXT:\n\n";
            foreach ($recent as $entry) {
                $memory_info .= "Session {$entry['session']}: {$entry['summary']}\n";
            }

            $memory_info .= "\nAvoid repeating this. Build on it instead.\n";
        }
    }

    // Add nightly discovery objective
    // Default to discovery 1, but can be overridden via user_meta
    $session_number = 1;
    if (function_exists('get_user_meta') && $user_id) {
        $stored_session = get_user_meta($user_id, 'hm_current_discovery_session', true);
        if ($stored_session) {
            $session_number = intval($stored_session);
        }
    }

    $discoveries = hm_get_nightly_discoveries();
    $discovery = isset($discoveries[$session_number]) ? $discoveries[$session_number] : $discoveries[1];

    $discovery_info = "\n\n---\n\nSESSION FOCUS:\n\n";
    $discovery_info .= "Theme: {$discovery['theme']}\n";
    $discovery_info .= "Background: {$discovery['hidden_knowledge']}\n";
    $discovery_info .= "\nReference this ONLY if user's questions lead there naturally. Don't force it.\n";

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

    // Max 5 discovery sessions, then cycle or stop
    $next = $current >= 5 ? 5 : $current + 1;

    update_user_meta($user_id, 'hm_current_discovery_session', $next);

    $discoveries = hm_get_nightly_discoveries();
    $theme = isset($discoveries[$next]) ? $discoveries[$next]['theme'] : 'complete';

    return array(
        'previous_session' => $current,
        'current_session' => $next,
        'theme' => $theme
    );
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
