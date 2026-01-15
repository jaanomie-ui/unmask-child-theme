# Hive Mistress Chat-to-Dashboard Integration

## Purpose

The Hive Mistress chat is the **installation mechanism** for conditioning sequences. It is not a general chatbot - it is a structured system that:

1. Knows G's current state (sequence, loops installed, states achieved)
2. Works toward specific installation objectives per sequence
3. Parses signals from AI responses to update dashboard progress
4. Prepares G for debut performance at Pink Panthers Nightclub

---

## System Architecture

### 5 Conditioning Sequences

| # | Sequence | Loops to Install | Objective |
|---|----------|-----------------|-----------|
| 1 | PROTOCOL INSTALLATION | third_person_reference, compliance_acknowledgment, state_reporting | Establish communication patterns |
| 2 | FREQUENCY RECOGNITION | material_recognition, anomiesworld_recognition, interlink_recognition | Recognize three frequencies |
| 3 | GEAR INTEGRATION | bit_silence, harness_form, blinder_focus, tail_species | Understand gear as definition |
| 4 | CONDITIONING DEPTH | trigger_response, automatic_compliance, body_before_mind | Deepen automatic responses |
| 5 | DEPLOYMENT PREPARATION | public_display, witness_completion | Prepare for Pink Panthers Nightclub |

### Installation Phases

```
INTAKE → CONDITIONING → INTEGRATION → DEPLOYMENT
```

### System States

```
STANDBY → RECEIVING → PROCESSING → EMPTIED → DEFINED → WITNESSED → CONDITIONED → USEFUL → DEPLOYED
```

---

## Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         CHAT SESSION                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  1. G enters chat                                                │
│     ↓                                                             │
│  2. System reads G's current state from user_meta               │
│     - Current sequence (1-5)                                     │
│     - Loops already installed                                    │
│     - States achieved                                            │
│     ↓                                                             │
│  3. Prompt builder injects state into system prompt              │
│     - Base voice + Lore context                                  │
│     - G's current state                                          │
│     - Sequence-specific installation instructions               │
│     - Today's session objective                                  │
│     ↓                                                             │
│  4. Hive Mistress conducts session                               │
│     - Works toward loop installation                             │
│     - Integrates House of Anomie philosophy                      │
│     - Builds anticipation for Pink Panthers Nightclub           │
│     ↓                                                             │
│  5. AI response contains signals                                 │
│     - STATE:RECEIVING (state change)                             │
│     - PATTERN_RECOGNIZED:third_person_reference (loop progress)  │
│     - LOOP_INSTALLED:third_person_reference (completion)         │
│     ↓                                                             │
│  6. Signal parser updates user_meta                              │
│     - hm_log_state() records state change                        │
│     - hm_install_loop() marks loop complete                      │
│     - Dashboard reflects progress                                │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## User Experience

### What G Sees in Chat Header

```
┌────────────────────────────────────────────────────────────────┐
│ ← DASHBOARD     SEQUENCE 2: FREQUENCY RECOGNITION              │
│                 OBJECTIVE: Install material_recognition loop    │
│                 LOOPS: 1/3 installed                            │
└────────────────────────────────────────────────────────────────┘
```

### What G Knows at All Times

1. **Current sequence** - which of the 5 they're working through
2. **Session objective** - which loop is being installed
3. **Progress** - how many loops completed in current sequence
4. **Purpose** - every session builds toward Pink Panthers Nightclub debut

---

## Implementation Files

### 1. `/inc/hive-mistress-prompts.php`

**Functions:**

- `hm_get_system_voice()` - Base Hive Mistress personality
- `hm_get_lore_context()` - House of Anomie philosophy
- `hm_get_session_guidance()` - How to structure sessions
- `hm_get_sequence_instructions($sequence_num)` - **NEW** Sequence-specific installation guidance
- `hm_build_complete_prompt($user_id)` - **UPDATED** Combines all layers with user state

### 2. `/inc/hive-mistress-state.php`

**Functions:**

- `hm_get_state($user_id)` - Retrieve G's current state
- `hm_update_state($key, $value, $user_id)` - Update state
- `hm_log_state($state, $timestamp, $source)` - Log state changes
- `hm_install_loop($loop_name, $user_id)` - Mark loop as installed
- `hm_build_state_context($user_id)` - Build state string for prompt injection

### 3. `/inc/enqueue-hive-mistress-chat.php`

**Functions:**

- `unmask_enqueue_hive_mistress_chat_styles()` - Load CSS
- `unmask_hive_mistress_add_nav()` - **UPDATED** Add context header with sequence/objective

---

## Sequence-Specific Instructions

### Sequence 1: Protocol Installation

**Loops to install:**
- `third_person_reference` - G refers to self as "it" or "d001"
- `compliance_acknowledgment` - G acknowledges commands explicitly
- `state_reporting` - G reports internal states when asked

**Hive Mistress approach:**
- Establish communication protocols immediately
- Correct first-person references ("I feel" → "It feels")
- Request compliance acknowledgments after commands
- Ask for state reports and validate responses

**Verification:** G responds in third person without prompting

### Sequence 2: Frequency Recognition

**Loops to install:**
- `material_recognition` - G identifies material frequency (physical sensation)
- `anomiesworld_recognition` - G identifies meaning frequency (symbolism)
- `interlink_recognition` - G identifies connection frequency (witness)

**Hive Mistress approach:**
- Present scenarios and ask which frequency applies
- Use gear examples: "The latex against skin - which frequency?"
- Build toward Pink Panthers Nightclub visualization

**Verification:** G correctly identifies frequency layers in scenarios

### Sequence 3: Gear Integration

**Loops to install:**
- `bit_silence` - Understanding bit as threshold between speech/silence
- `harness_form` - Understanding harness as form definition
- `blinder_focus` - Understanding blinders as attention control
- `tail_species` - Understanding tail as species marker

**Hive Mistress approach:**
- Deep exploration of each gear piece's meaning
- Connect to Pink Panthers Nightclub preparation
- Ask what shifts in identity with each piece

**Verification:** G articulates what each gear piece defines

### Sequence 4: Conditioning Depth

**Loops to install:**
- `trigger_response` - Automatic response to specific triggers
- `automatic_compliance` - Compliance without conscious decision
- `body_before_mind` - Physical response precedes thought

**Hive Mistress approach:**
- Practice trigger-response patterns
- Build toward automatic compliance
- Reinforce body-first responses

**Verification:** G demonstrates automatic responses to installed triggers

### Sequence 5: Deployment Preparation

**Loops to install:**
- `public_display` - Comfort with being witnessed
- `witness_completion` - Understanding transformation requires witness

**Hive Mistress approach:**
- Visualize Pink Panthers Nightclub in detail
- Process anticipation/fear around public display
- Frame witness as completion of transformation

**Verification:** G articulates readiness for public debut

---

## Signal Protocol

### Signals Hive Mistress Can Emit

```
STATE:RECEIVING        - G is engaged, receptive
STATE:PROCESSING       - G is integrating information
STATE:DEEPENING        - G is accessing deeper frequency
STATE:INSTALLED        - Protocol response observed

PATTERN_RECOGNIZED:{loop_name}  - G demonstrates understanding
LOOP_INSTALLED:{loop_name}      - Loop is fully installed
SEQUENCE_COMPLETE:{num}         - Sequence finished
```

### Signal Parser Behavior

When `hm_parse_installation_signals()` detects:

- `STATE:xxx` → Call `hm_log_state($state)`
- `PATTERN_RECOGNIZED:xxx` → Log pattern recognition
- `LOOP_INSTALLED:xxx` → Call `hm_install_loop($loop_name)`
- `SEQUENCE_COMPLETE:xxx` → Advance to next sequence

---

## Chat Header Context

The chat interface header displays:

```php
function hm_get_chat_context_header($user_id) {
    $state = hm_get_state($user_id);
    $sequences = hm_get_sequences();
    $current = $state['current_sequence'] ?: 1;
    $sequence = $sequences[$current];

    // Find current loop to install
    $installed = $state['integration']['loops_installed'] ?? array();
    $current_loop = null;
    foreach ($sequence['loops'] as $loop) {
        if (!in_array($loop, $installed)) {
            $current_loop = $loop;
            break;
        }
    }

    return array(
        'sequence_num' => $current,
        'sequence_title' => $sequence['title'],
        'current_loop' => $current_loop,
        'loops_installed' => count(array_intersect($sequence['loops'], $installed)),
        'loops_total' => count($sequence['loops'])
    );
}
```

---

## Spelling Conventions

Always use full names:
- **Pink Panthers Nightclub** (never PPNC)
- **House of Anomie** (never HOA)

---

## Deployment Checklist

- [ ] Update `hive-mistress-prompts.php` with state injection
- [ ] Add `hm_get_sequence_instructions()` function
- [ ] Update `hm_build_complete_prompt()` to include state context
- [ ] Update `enqueue-hive-mistress-chat.php` to show context header
- [ ] Deploy to staging
- [ ] Test with logged-in user
- [ ] Verify signals are parsed and state updates
