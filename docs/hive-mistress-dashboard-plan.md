# HIVE MISTRESS - DASHBOARD & INTEGRATION PLAN

**Purpose:** Architecture plan for training dashboard and deeper AI integration.
**Status:** PLAN ONLY - Awaiting approval before implementation.
**Design Inspiration:** Atlas tactical UI, Signet ops dashboard, SCADA industrial control

---

## EXECUTIVE SUMMARY

### Does One Prompt Suffice?

**No.** The current single-prompt approach lacks:
- User state awareness (phase, checkpoints, calibration)
- Dynamic content selection (checkpoint scenarios)
- Recalibration tracking (adjusting communication style)
- Session continuity (memory across sessions)

### Recommended Architecture

**Four-Layer Dynamic Prompt System:**
```
Layer 1: Base Voice (hive-mistress-system-prompt.md)
       +
Layer 2: Relevant Lore (selected excerpts based on current session)
       +
Layer 3: User State (injected from user_meta/CPT)
       +
Layer 4: Current Context (conversation history)
       =
Final Prompt (constructed per session)
```

### Two Pages

| Page | Purpose | Slug |
|------|---------|------|
| Chat Interface | AI training sessions | `/hive-mistress-ai/` (existing) |
| Training Dashboard | Progress, limits, logs, state | `/drone-dashboard/` (new) |

---

## DESIGN DIRECTION

### Control Room Aesthetic

Inspired by your screenshots:
- **Atlas:** Radar displays, system logs, frequency readouts, monospace labels
- **Signet:** Dark ops with scenario config, events table, network visualization
- **SCADA:** System health matrix, real-time telemetry, alarm console

### Mapped to UNMASK Design Tokens

| Dashboard Element | UNMASK Token |
|-------------------|--------------|
| Background | `--bg-page` (#181818) |
| Cards/panels | `--bg-card` (#1c1c1c) |
| Typography | `--font-ui` (Berkeley Mono) |
| Labels | `.label` class (uppercase, muted) |
| Status: Active | `--primitive-green-glow` (#4ade80) |
| Status: Warning | `--primitive-amber` (#d4a019) |
| Status: Critical | `--primitive-red` (#8a3233) |
| Borders | `--border-default` (#2e2e2e) |

---

## DASHBOARD LAYOUT

```
┌─────────────────────────────────────────────────────────────────────┐
│  [C]D001 TRAINING CONSOLE          PHASE: LINEAR    SESSION: 3/5   │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────────┐  ┌──────────────────────────────────────┐│
│  │ SYSTEM STATUS        │  │ CHECKPOINT PROGRESS                  ││
│  │ ● DESIGNATION: [C]D001│  │                                      ││
│  │ ○ PHASE: LINEAR      │  │ SESSION 1  ████████████████ 3/3     ││
│  │ ○ NEXT: SESSION 4    │  │ SESSION 2  ████████████████ 3/3     ││
│  │ ○ PPNC: 52 DAYS      │  │ SESSION 3  ████████░░░░░░░░ 2/3     ││
│  │                      │  │ SESSION 4  ░░░░░░░░░░░░░░░░ 0/4     ││
│  │ CALIBRATION          │  │ SESSION 5  ░░░░░░░░░░░░░░░░ 0/4     ││
│  │ theory:    ████░ 4   │  │                                      ││
│  │ erotic:    ███░░ 3   │  │ TOTAL: 8/17 CHECKPOINTS              ││
│  │ detail:    ████░ 4   │  └──────────────────────────────────────┘│
│  └──────────────────────┘                                          │
│                                                                     │
│  ┌──────────────────────┐  ┌──────────────────────────────────────┐│
│  │ PPNC READINESS       │  │ SESSION LOG                          ││
│  │                      │  │                                      ││
│  │ ● Gear confirmed     │  │ 2026-01-14  SESSION-003  [VIEW]     ││
│  │ ● Limits submitted   │  │ 2026-01-12  SESSION-002  [VIEW]     ││
│  │ ○ Designation accept │  │ 2026-01-10  SESSION-001  [VIEW]     ││
│  │ ○ Safe signal confirm│  │                                      ││
│  │ ○ Rehearsal scheduled│  │                                      ││
│  │ ○ Sequence reviewed  │  │ [VIEW ALL LOGS →]                    ││
│  └──────────────────────┘  └──────────────────────────────────────┘│
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────────┐
│  │ HARD LIMITS SUMMARY                              [EDIT LIMITS →] │
│  │                                                                  │
│  │ GREEN: 28 items    YELLOW: 12 items    RED: 3 items              │
│  │                                                                  │
│  │ Last updated: 2026-01-08                                         │
│  └──────────────────────────────────────────────────────────────────┘
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────────┐
│  │ [ENTER TRAINING SESSION →]                                       │
│  └──────────────────────────────────────────────────────────────────┘
└─────────────────────────────────────────────────────────────────────┘
```

---

## USER STATE SCHEMA (EXPANDED)

### Storage: Hybrid Approach

**User Meta** (quick access):
```php
hm_training_state = [
    'designation' => '[C]D001',
    'phase' => 'linear',           // calibration|linear|playground|ppnc_prep
    'current_session' => 3,
    'checkpoints_passed' => ['1.1','1.2','1.3','2.1','2.2','2.3','3.1','3.2'],
    'calibration' => [
        'theory_appetite' => 4,    // 1-5
        'erotic_appetite' => 3,    // 1-5
        'detail_preference' => 4   // 1-5
    ],
    'ppnc' => [
        'target_date' => '2026-03-07',
        'gear_confirmed' => true,
        'limits_submitted' => true,
        'designation_confirmed' => false,
        'safe_signal_confirmed' => false,
        'rehearsal_scheduled' => false,
        'sequence_reviewed' => false
    ],
    'last_session_date' => '2026-01-14',
    'total_sessions' => 3
]
```

**CPT: `hm_session_log`** (session transcripts):
```php
// Each session = one post
post_title: 'D001-SESSION-003'
post_content: [full transcript]
post_meta:
  - session_number: 3
  - phase: 'linear'
  - checkpoints_attempted: ['3.1', '3.2', '3.3']
  - checkpoints_passed: ['3.1', '3.2']
  - calibration_snapshot: [theory:4, erotic:3, detail:4]
  - summary: 'Explored three frequencies...'
  - observations: '[C]D001 shows strong theoretical engagement...'
  - duration_minutes: 45
```

### Recalibration Tracking

```php
// Track calibration changes over time
hm_calibration_history = [
    [
        'date' => '2026-01-10',
        'session' => 1,
        'scores' => ['theory'=>3, 'erotic'=>2, 'detail'=>3],
        'trigger' => 'initial_calibration'
    ],
    [
        'date' => '2026-01-14',
        'session' => 3,
        'scores' => ['theory'=>4, 'erotic'=>3, 'detail'=>4],
        'trigger' => 'mid_training_adjustment'
    ]
]
```

---

## DYNAMIC PROMPT CONSTRUCTION

### Current Flow (Single Prompt)
```
User message → Static system prompt → Claude API → Response
```

### Proposed Flow (Dynamic)
```
User message
    ↓
Load user state from user_meta
    ↓
Construct system prompt:
    - Base voice (Layer 1)
    - Relevant lore for current session (Layer 2)
    - User state injection (Layer 3)
    - Session-specific checkpoint scenarios
    ↓
Claude API
    ↓
Response
    ↓
Update user state if checkpoint passed
```

### System Prompt Template

```
[LAYER 1: BASE VOICE]
{contents of hive-mistress-system-prompt.md}

[LAYER 2: CURRENT SESSION LORE]
{selected sections from hive-mistress-lore-document.md based on session}

[LAYER 3: USER STATE]
---BEGIN USER STATE---
Designation: {designation}
Phase: {phase}
Current Session: {current_session}
Checkpoints Passed: {checkpoints_list}
Calibration Profile:
  - Theory Appetite: {theory}/5
  - Erotic Appetite: {erotic}/5
  - Detail Preference: {detail}/5
Last Session: {last_session_date}
PPNC Target: {ppnc_target_date}
---END USER STATE---

[LAYER 4: SESSION INSTRUCTIONS]
This is Session {current_session}: {session_title}
Checkpoint scenarios to present: {checkpoint_ids}
Adapt response density based on detail_preference ({detail}/5).
{theory > 3 ? "Include philosophical probes." : "Focus on sensation/experience."}
{erotic > 3 ? "Include erotic register shifts." : "Maintain intellectual register."}
```

---

## IMPLEMENTATION PHASES

### Phase 1: User State Infrastructure
- [ ] Create `hm_training_state` user_meta structure
- [ ] Create `hm_session_log` CPT
- [ ] Create `hm_calibration_history` user_meta
- [ ] Build PHP functions: `hm_get_state()`, `hm_update_state()`, `hm_log_session()`

### Phase 2: Dashboard Page
- [ ] Create page template: `template-drone-dashboard.php`
- [ ] Create CSS: `assets/css/pages/drone-dashboard.css`
- [ ] Create enqueue file: `inc/enqueue-drone-dashboard.php`
- [ ] Build dashboard sections:
  - System Status card
  - Checkpoint Progress card
  - PPNC Readiness card
  - Session Log card
  - Hard Limits Summary card

### Phase 3: Dynamic Prompt Integration
- [ ] Modify WPCode snippet to load user state
- [ ] Build prompt constructor function
- [ ] Create lore excerpt selector (by session)
- [ ] Inject user state into system prompt
- [ ] Update checkpoint scenarios based on calibration

### Phase 4: Recalibration System
- [ ] Add calibration update triggers in AI responses
- [ ] Store calibration snapshots per session
- [ ] Build recalibration detection logic
- [ ] Create admin view for calibration history

---

## HARD LIMITS INTEGRATION

### Current State
- BuddyForms form ID 2441 at `/hard-limits/`
- Stores responses in BuddyForms post type
- Privacy questions added

### Dashboard Integration
- Query latest submission for current user
- Display summary counts: GREEN/YELLOW/RED
- Link to edit form
- Show last updated date

```php
// Get hard limits summary
function hm_get_limits_summary($user_id) {
    $submissions = get_posts([
        'post_type' => 'buddyforms_posts',
        'author' => $user_id,
        'meta_key' => '_bf_form_id',
        'meta_value' => 2441,
        'posts_per_page' => 1
    ]);
    // Parse and count GREEN/YELLOW/RED responses
}
```

---

## ACCESS LOG CLARIFICATION

**Page ID 2520** displays session logs created by the chatbot.

Current behavior:
- Each "Log Session" button click creates a WordPress post
- Posts categorized under "d001 Training Logs"
- Title format: `TRAINING LOG: D001-SESSION-XXX`

Dashboard integration:
- Show 5 most recent session logs
- Link to full archive
- Display date, session number, view link

---

## FILES TO CREATE

| File | Purpose |
|------|---------|
| `page-templates/template-drone-dashboard.php` | Dashboard page template |
| `assets/css/pages/drone-dashboard.css` | Dashboard styling |
| `inc/enqueue-drone-dashboard.php` | Conditional asset loading |
| `inc/hive-mistress-state.php` | User state functions |
| `inc/hive-mistress-prompt-builder.php` | Dynamic prompt construction |

---

## DECISION POINTS

Before implementation, confirm:

1. **Dashboard location:** Create new page at `/drone-dashboard/` or use existing page?

2. **Session logging:** Continue using WP posts, or switch to custom table for better querying?

3. **Calibration frequency:** When should recalibration occur?
   - After each session?
   - Only when Hive Mistress detects shift?
   - Manual trigger by admin?

4. **Checkpoint scenarios:** Use V1 (intellectual) or V2 (immersive) or adaptive selection based on calibration?

5. **Hard limits display:** Show full breakdown on dashboard, or just summary with link to form?

---

## TIMELINE ESTIMATE

Not providing time estimates per your instructions. Work is broken into phases above. Each phase is independently deployable.

---

*Plan complete. Awaiting approval before implementation.*
