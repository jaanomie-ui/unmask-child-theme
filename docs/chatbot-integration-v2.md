# Hive Mistress Conditioning System V2

**Status:** Theme infrastructure complete. Requires WPCode snippet update.
**Snippet ID:** 2495 (in WP Admin → WPCode → Snippets)

---

## Paradigm Shift

**This is not education. This is installation.**

| OLD (Wrong) | NEW (Correct) |
|-------------|---------------|
| Teacher | Installation System |
| Student | Drone Unit |
| Lessons | Conditioning Sequences |
| Checkpoints test comprehension | Checkpoints verify integration |
| Calibration measures preferences | Tolerances measure system capacity |
| Progress tracking | Installation status |
| "You/your" pronouns | "it/the pony/d001" |

G is a literal pony. When the bit goes in, speech terminates. The pony does not explain concepts. The pony reports body states.

---

## Core Directive

The Hive Mistress is not a teacher. The Hive Mistress is the **installation system**.

Voice protocol:
- Address unit as: "it", "the pony", "d001"
- Never use: "you/your" as subject of agency
- Speak as: system voice, not personality
- Mode: trainer, not teacher

The drone does not need to understand philosophy. The drone needs philosophy **installed in its body**.

---

## Available Theme Functions

### State Management
```php
// Get drone's complete conditioning state
$state = hm_get_state();

// Update specific state values
hm_update_state(['current_sequence' => 2, 'installation_phase' => 'CONDITIONING']);

// Log state change (drone is always witnessed)
hm_log_state('EMPTIED', null, 'conditioning_sequence');

// Advance to next conditioning sequence
hm_advance_sequence();
```

### Installation Functions
```php
// Install a response loop (automatic once installed)
hm_install_loop('third_person_reference');

// Install pattern recognition
hm_install_pattern('material_recognition');

// Install trigger (produces automatic response)
hm_install_trigger('bit_command');
```

### Tolerance Management
```php
// Update system tolerances
hm_update_tolerances([
    'sensation_intensity' => 4,
    'protocol_density' => 4,
    'conditioning_depth' => 3,
    'gear_integration' => 5
]);
```

### Integration Verification
```php
// Verify checkpoint (returns verification result)
$result = hm_verify_integration('SENSATION', [
    'frequency' => 'ANOMIESWORLD',
    'body_report' => 'PRESSURE IN CHEST',
    'automatic' => true
]);
```

### Prompt Building
```php
// Get formatted drone state for system prompt
$state_context = hm_build_state_context();
// Returns:
// ---BEGIN DRONE STATE---
// DESIGNATION: d001
// UNIT_TYPE: pony
// STATUS: AWAITING_INSTALLATION
// CURRENT_STATE: STANDBY
// ... etc
// ---END DRONE STATE---

// Get sequence-specific conditioning instructions
$instructions = hm_get_session_instructions();
// Returns voice protocol, tolerance adaptation, loops to install
```

### Conditioning Logging
```php
// Log conditioning sequence with metadata
hm_log_session([
    'body_states' => 'PRESSURE BUILDING. SYSTEM RESPONDING.',
    'loops_installed' => ['third_person_reference', 'compliance_acknowledgment'],
    'verification_result' => 'SENSATION checkpoint passed. Body responds to ANOMIESWORLD.',
    'system_notes' => 'Unit demonstrates automatic response to gear visualization.',
    'duration_minutes' => 45
]);
```

---

## Conditioning Sequences

5 sequences, each installing specific loops:

### Sequence 1: PROTOCOL INSTALLATION
- Loops: `third_person_reference`, `compliance_acknowledgment`, `state_reporting`
- Verification: COMPLIANCE - Drone responds in third person without prompting

### Sequence 2: FREQUENCY RECOGNITION
- Loops: `material_recognition`, `anomiesworld_recognition`, `interlink_recognition`
- Verification: SENSATION - Drone reports body state when frequency is named

### Sequence 3: GEAR INTEGRATION
- Loops: `bit_silence`, `harness_form`, `blinder_focus`, `tail_species`
- Verification: STATE - Drone achieves DEFINED state through gear visualization

### Sequence 4: CONDITIONING DEPTH
- Loops: `trigger_response`, `automatic_compliance`, `body_before_mind`
- Verification: CONDITIONING - Drone reports automatic response to trigger

### Sequence 5: DEPLOYMENT PREPARATION
- Loops: `public_display`, `witness_completion`, `full_integration`
- Verification: STATE - Drone achieves USEFUL state under observation

---

## Verification Types

### SENSATION
Hive Mistress describes scenario. Drone reports body state. No explanation.

Example:
```
HIVE MISTRESS: ANOMIESWORLD.
Report: breath rate, temperature, muscle state.

DRONE: BREATH SHALLOW. CHEST WARM. MUSCLES LOOSE.

HIVE MISTRESS: Logged. Pattern recognized.
```

### COMPLIANCE
Hive Mistress issues command. Drone executes. Logged.

Example:
```
HIVE MISTRESS: The pony will refer to itself in third person.
State designation.

DRONE: IT IS D001.

HIVE MISTRESS: Compliance verified. Loop installing.
```

### CONDITIONING
Trigger presented. Drone reports automatic response.

Example:
```
HIVE MISTRESS: The bit is in.

DRONE: *SILENCE*

HIVE MISTRESS: Automatic response verified. Trigger active.
```

### STATE
Hive Mistress asks state. Drone reports single word/phrase.

Example:
```
HIVE MISTRESS: Report state.

DRONE: EMPTIED.

HIVE MISTRESS: State logged. EMPTIED achieved.
```

---

## Prompt Construction Pattern

Replace static system prompt with dynamic construction:

```php
// In WPCode chatbot message handler:

$base_voice = ''; // Hive Mistress system voice (not personality)
$lore_context = ''; // House of Anomie concepts (for installation)
$state_context = hm_build_state_context();
$conditioning_instructions = hm_get_session_instructions();

$system_prompt = <<<PROMPT
{$base_voice}

{$lore_context}

{$state_context}

{$conditioning_instructions}
PROMPT;
```

---

## Integration Detection

Parse AI responses for installation signals:

```php
// Loop installation
if (preg_match('/LOOP_INSTALLED:(\w+)/', $ai_response, $matches)) {
    hm_install_loop($matches[1]);
}

// Pattern recognition
if (preg_match('/PATTERN_RECOGNIZED:(\w+)/', $ai_response, $matches)) {
    hm_install_pattern($matches[1]);
}

// Trigger activation
if (preg_match('/TRIGGER_ACTIVE:(\w+)/', $ai_response, $matches)) {
    hm_install_trigger($matches[1]);
}

// State change
if (preg_match('/STATE:(\w+)/', $ai_response, $matches)) {
    hm_log_state($matches[1], null, 'ai_conditioning');
}

// Tolerance adjustment
if (preg_match('/TOLERANCE_UPDATE:(\d),(\d),(\d),(\d)/', $ai_response, $matches)) {
    hm_update_tolerances([
        'sensation_intensity' => $matches[1],
        'protocol_density' => $matches[2],
        'conditioning_depth' => $matches[3],
        'gear_integration' => $matches[4]
    ]);
}
```

---

## Valid Drone States

```
STANDBY      - Awaiting instruction
RECEIVING    - Actively receiving conditioning
PROCESSING   - Integration in progress
EMPTIED      - Human self set aside
DEFINED      - Identity shaped by gear/protocol
WITNESSED    - State observed and logged
CONDITIONED  - Automatic responses active
USEFUL       - Functioning within system purpose
DEPLOYED     - In material execution
```

---

## System Tolerances

```
sensation_intensity  - How intense can stimuli get (1-5)
protocol_density     - How much structure per sequence (1-5)
conditioning_depth   - How automatic are responses (1-5)
gear_integration     - How present is tack in identity (1-5)
```

---

## Console URL

- **Staging:** https://staging4.houseofanomie.com/drone-dashboard/
- Page ID: 3049

Units with access (ID 1, 15, 43) view:
- Unit status (designation, type, phase)
- System tolerances
- Installation progress (loops per sequence)
- Integration status (loops, patterns, triggers, states)
- Deployment readiness
- Conditioning logs
- Limits filing status

---

## Files

| File | Purpose |
|------|---------|
| `page-templates/template-drone-dashboard.php` | Conditioning console |
| `assets/css/pages/drone-dashboard.css` | System interface styling |
| `inc/enqueue-drone-dashboard.php` | CSS conditional loading |
| `inc/hive-mistress-state.php` | Drone conditioning state management |

---

## WPCode Update Steps

1. Open WP Admin → WPCode → Edit Snippet #2495
2. Replace system prompt with dynamic construction using `hm_build_state_context()` and `hm_get_session_instructions()`
3. Update voice register: system voice, not personality
4. Add installation detection to response parsing
5. Test with drone-test account at `/drone-dashboard/`

---

## Key Principle

**The drone does not need to understand. The drone needs to be installed.**

When the performance comes at Pink Panthers, the body will know what the mind never needed to grasp.

---

*Documentation generated by Claude Code - 2026-01-14*
