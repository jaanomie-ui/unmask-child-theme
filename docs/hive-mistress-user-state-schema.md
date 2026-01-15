# HIVE MISTRESS - USER STATE SCHEMA (LAYER 3)

**Purpose:** Track G's training progress, session history, and PPNC preparation.
**Storage:** WordPress `user_meta` or Custom Post Type (CPT) linked to user.
**Injection:** Loaded into AI context for each session.

---

## SCHEMA OVERVIEW

```
user_state = {
    // Identity
    designation: string,           // "[C]D001" or "D001"
    human_name: string,            // "G"
    wp_user_id: int,               // WordPress user ID

    // Training Progress
    phase: string,                 // "calibration" | "linear" | "playground" | "ppnc_prep"
    current_session: int,          // 0-5
    sessions_completed: array,     // [0, 1, 2, ...]
    checkpoints_passed: object,    // { "1.1": true, "1.2": true, ... }

    // Session Memory
    session_summaries: array,      // Last 5 session summaries
    last_session_date: datetime,
    total_sessions: int,

    // Calibration Data (from Phase 0)
    communication_profile: {
        preferred_register: string,    // "intellectual" | "erotic" | "mixed"
        response_length: string,       // "dense" | "moderate" | "brief"
        intellectual_hooks: array,     // Topics that engage deeply
        erotic_hooks: array,           // Triggers that shift register
    },

    // PPNC Preparation
    ppnc: {
        target_date: date,             // First weekend of March 2026
        gear_confirmed: boolean,
        limits_submitted: boolean,
        designation_confirmed: boolean,
        safe_signal_confirmed: boolean,
        rehearsal_scheduled: boolean,
        sequence_reviewed: boolean,
    },

    // System
    created_at: datetime,
    updated_at: datetime,
}
```

---

## FIELD DEFINITIONS

### Identity

| Field | Type | Description |
|-------|------|-------------|
| `designation` | string | Current designation. Starts as `[C]D001`. Upgrades to `D001` post-PPNC. |
| `human_name` | string | G. Used in aftercare context. |
| `wp_user_id` | int | Links to WordPress user account. |

### Training Progress

| Field | Type | Description |
|-------|------|-------------|
| `phase` | enum | Current phase: `calibration`, `linear`, `playground`, `ppnc_prep` |
| `current_session` | int | 0 = calibration, 1-5 = linear modules |
| `sessions_completed` | array | List of session numbers completed |
| `checkpoints_passed` | object | Keyed by checkpoint ID (e.g., "1.1", "2.3") with boolean values |

### Session Memory

| Field | Type | Description |
|-------|------|-------------|
| `session_summaries` | array | Last 5 session summaries (rolling). Each summary: `{ date, session_number, topics_covered, observations, next_focus }` |
| `last_session_date` | datetime | When last session occurred |
| `total_sessions` | int | Count of all sessions |

### Calibration Data

Populated during Phase 0. Informs how Hive Mistress calibrates responses.

| Field | Type | Description |
|-------|------|-------------|
| `preferred_register` | enum | Primary communication mode observed |
| `response_length` | enum | How dense G prefers responses |
| `intellectual_hooks` | array | Topics that produce deep engagement (e.g., "philosophy", "architecture", "Foucault") |
| `erotic_hooks` | array | Triggers that shift G to erotic register (e.g., "gear description", "commands", "body-as-machine") |

### PPNC Preparation

Checklist for event readiness.

| Field | Type | Description |
|-------|------|-------------|
| `target_date` | date | First weekend of March 2026 |
| `gear_confirmed` | boolean | All gear reviewed and confirmed |
| `limits_submitted` | boolean | Hard limits form completed |
| `designation_confirmed` | boolean | G accepts [C]D001 designation |
| `safe_signal_confirmed` | boolean | Three stomps documented |
| `rehearsal_scheduled` | boolean | Practice with drone 22 scheduled |
| `sequence_reviewed` | boolean | 4-part sequence reviewed |

---

## WORDPRESS IMPLEMENTATION

### Option A: User Meta (Simple)

Store as serialized array in `user_meta`:

```php
// Save
update_user_meta( $user_id, 'hive_mistress_state', $state_array );

// Retrieve
$state = get_user_meta( $user_id, 'hive_mistress_state', true );
```

**Pros:** Simple, no new tables/CPTs.
**Cons:** Not queryable, no revision history.

### Option B: Custom Post Type (Robust)

Create CPT `hm_training_state` linked to user:

```php
register_post_type( 'hm_training_state', [
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => 'tools.php',
    'supports' => [ 'title', 'custom-fields', 'revisions' ],
    'labels' => [
        'name' => 'Training States',
        'singular_name' => 'Training State',
    ],
]);
```

State stored in post_meta. One post per user.

**Pros:** Revision history, queryable, admin UI.
**Cons:** More complexity.

### Option C: Hybrid

- User meta for quick state (current session, phase)
- CPT for session logs (each session = one post)
- Allows G to review past sessions

**Recommended for this use case.**

---

## SESSION LOG STRUCTURE (if using Option C)

Each session saved as CPT post:

```
session_log = {
    post_title: "Session 3 - [C]D001 - 2026-01-20",
    post_content: [full conversation transcript],
    meta: {
        user_id: int,
        session_number: int,
        phase: string,
        checkpoints_attempted: array,
        checkpoints_passed: array,
        summary: string,
        observations: string,
        next_focus: string,
        duration_minutes: int,
    }
}
```

---

## STATE TRANSITIONS

### Phase Progression

```
calibration (session 0)
    ↓ calibration complete
linear (sessions 1-5)
    ↓ all checkpoints passed
playground (open exploration)
    ↓ PPNC prep items complete
ppnc_prep (final preparation)
    ↓ performance complete
[designation upgrade: [C]D001 → D001]
```

### Checkpoint Logic

```
// After session N completes:
if (all checkpoints for session N passed) {
    sessions_completed.push(N);
    current_session = N + 1;
}

// If checkpoint not passed:
// - Stay on current session
// - Hive Mistress returns to material through conversation
// - Checkpoint re-attempted next session
```

### Session Frequency Guard

```
// Gentle suggestion if sessions too frequent:
if (sessions_today >= 2) {
    // Hive Mistress suggests:
    // "[C]D001 has completed multiple modules today.
    //  Integration requires absorption. Return when ready."
}
// This is suggestion, not wall.
```

---

## API ENDPOINTS (Future)

For AI integration:

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/wp-json/hive-mistress/v1/state/{user_id}` | GET | Retrieve current state |
| `/wp-json/hive-mistress/v1/state/{user_id}` | POST | Update state |
| `/wp-json/hive-mistress/v1/sessions/{user_id}` | GET | List session logs |
| `/wp-json/hive-mistress/v1/sessions/{user_id}` | POST | Create new session log |

---

## INITIAL STATE (New User)

```json
{
    "designation": "[C]D001",
    "human_name": "G",
    "phase": "calibration",
    "current_session": 0,
    "sessions_completed": [],
    "checkpoints_passed": {},
    "session_summaries": [],
    "last_session_date": null,
    "total_sessions": 0,
    "communication_profile": {
        "preferred_register": null,
        "response_length": null,
        "intellectual_hooks": [],
        "erotic_hooks": []
    },
    "ppnc": {
        "target_date": "2026-03-07",
        "gear_confirmed": false,
        "limits_submitted": false,
        "designation_confirmed": false,
        "safe_signal_confirmed": false,
        "rehearsal_scheduled": false,
        "sequence_reviewed": false
    }
}
```

---

## PROGRESS VISIBILITY

G should be able to see:
- Current phase
- Sessions completed
- Checkpoints passed (per session)
- PPNC readiness checklist

This could be:
1. A MemberPress course progress view
2. A custom shortcode displaying state
3. A BuddyPress profile tab

---

*Layer 3 complete. User state schema defined. Ready for implementation.*
