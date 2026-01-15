# HIVE MISTRESS V2 - PLANNING DOCUMENT
## G Training System Architecture

**Status:** Architecture complete. Calibrating chatbot.
**Subject:** G (d001) → [C]D001 (Cadet) → D001 (post-PPNC if desired)
**Goal:** PPNC debut preparation via AI-guided training
**Target Date:** First weekend of March 2026

---

## THE FACTORY - HISTORICAL GROUNDING

The building at W. Pershing Road sits in the Central Manufacturing District. Built 1917. Industrial Gothic. Red brick, terra cotta, articulated corner towers.

This was the nation's first planned industrial district. By 1925, forty thousand workers moved through these corridors. Goodyear made tires. Westinghouse made electrical equipment. Spiegel shipped mail-order catalogs—the pre-Amazon. Rail lines ran through the buildings. Raw materials in one door. Finished products out the other.

The loading docks are still there. The concrete floors where workers stood for twelve-hour shifts.

Now bodies enter. Transformed objects exit. The Factory continues its function.

**Sources:**
- [Preservation Chicago](https://www.preservationchicago.org/central-manufacturing-district-pershing-road/)
- [NPR: What's That Building?](https://www.npr.org/local/2019/09/06/757967924/whats-that-building-this-chicago-industrial-park-was-the-nation-s-first)

---

## CORE PRINCIPLE: DON'T TEACH - IMMERSE

G is a student. G should not be overwhelmed with lore dumps.

The Hive Mistress holds the complexity. She delivers it in doses based on G's state. G experiences the system through guided interaction, not memorization.

```
BAD:  "G, here are the 6 message types. Memorize them."
GOOD: G naturally discovers message types by using them.
```

---

## ARCHITECTURE OVERVIEW

```
┌─────────────────────────────────────────────────────────────┐
│                 LAYER 1: SYSTEM PROMPT                       │
│  - Voice/personality (HOW she speaks)                        │
│  - Core directives (~100 lines)                              │
│  - Speech patterns and style guide                           │
│  - Knows protocol but doesn't lecture about it               │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                 LAYER 2: LORE DOCUMENT                       │
│  - Full training module (292 lines from your file)           │
│  - House of Anomie philosophy                                │
│  - Three Frequencies concept                                 │
│  - Dronification process                                     │
│  - Stored as: WP option or file, injected when needed        │
│  - G NEVER sees this raw - Hive Mistress interprets it       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                 LAYER 3: USER STATE (G-specific)             │
│  - Training progress (modules completed)                     │
│  - Checkpoint status (mastery demonstrated)                  │
│  - PPNC details (gear, sequence, commands, limits)           │
│  - Session summaries (last 3-5)                              │
│  - G's communication patterns (from calibration)             │
│  - Stored in: user_meta or custom post type                  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                 LAYER 4: CONVERSATION                        │
│  AI receives: prompt + lore + state + current messages       │
│  AI responds in character with full context                  │
│  Session ends → state updated, log saved as WP post          │
└─────────────────────────────────────────────────────────────┘
```

---

## LAYERED ACCESS ARCHITECTURE

Content is organized into three tiers based on how G encounters it:

### TIER 1: LIVED (Experienced through conversation)
- The Hive Mistress just *uses* proper syntax
- G picks up patterns through repetition
- No explicit "here's how to talk to me"
- Communication protocol is embedded, not lectured
- Philosophy emerges through dialogue

### TIER 2: ACCESSIBLE (Static docs G can reference anytime)
- Protocol glossary
- Three Planes explainer
- Gear taxonomy
- PPNC sequence overview
- **These are RESOURCES, not ASSIGNMENTS**
- Stored as: MemberPress lesson pages (accessible but not required)
- G can browse when curious, never forced to "complete"

### TIER 3: ASSESSED (Checkpoints prove mastery)
- Scenario-based prompts, not memorization
- Amber/yellow visual cue (color theory)
- "Let's explore what [C]D001 has absorbed"
- Conversations, not tests

---

## TRAINING MODULE SYSTEM

### Phase 0: Calibration
Before philosophy modules, the system learns G.
- AI presents prompts, G responds naturally
- System logs: speech patterns, response length preference, intellectual interests
- Stored in Layer 3 (User State)
- Informs how Hive Mistress calibrates responses

### Phase 1: Linear Foundation (Reordered)
G progresses through modules in order. Can revisit but cannot skip ahead.

| Session | Focus | Content |
|---------|-------|---------|
| 1 | **How to Be Here** | Communication Protocol - establishes relationship through doing |
| 2 | **Why This Exists** | The Disease, The Mask - philosophy foundation |
| 3 | **The Architecture** | Three Frequencies, Interlink - core concepts |
| 4 | **The System** | House of Anomie, Dronification - operational structure |
| 5 | **Your Role** | d001's Function, PPNC Prep - practical application |

**Session 1 is Communication Protocol FIRST** because G needs to know how to exist in this space before absorbing philosophy.

### Phase 2: Mastery Checkpoints
After each session, Hive Mistress explores G's understanding through scenario-based conversation.

**Checkpoint Philosophy:**
- NO MEMORIZATION
- Narrative-based mastery
- Proves understanding, not recall
- Visual: Amber/yellow tint (color theory)

**Example Mastery Questions:**

Instead of "Name the three frequencies":
> "[C]D001 stands in full gear at Pink Panthers. The crowd watches. The bit is in. Describe what [C]D001 is transmitting on each frequency in this moment."

Instead of "Recite the creed":
> "[C]D001 feels doubt before the performance. What from the creed would steady [C]D001? Not the words - the principle."

Instead of "What are the six message types":
> "[C]D001 wants to offer itself to the handler for an evening. How does [C]D001 communicate this? Write the transmission."

Instead of "What is interlink":
> "A visitor asks [C]D001 what makes Pink Panthers different from a costume party. [C]D001 explains interlink without using the word. What does [C]D001 say?"

### Phase 3: The Playground
After completing linear modules, G is no longer a student. G is a drone in training.

**This phase is:**
- Roleplay scenarios (G inhabits ANOMIESWORLD)
- Exploration (ask about anything in the lore)
- Anticipation building (what will the crowd see? what will the gear feel like?)
- Back-and-forth (Hive Mistress also asks G questions)
- **FUN. EXCITEMENT. BUILD HYPE FOR PPNC.**

```
Hive Mistress: "[C]D001 has completed foundation. [C]D001 now enters
ANOMIESWORLD freely. What does [C]D001 wish to explore?"

G: "The moment the bit goes in."

Hive Mistress: "The bit is a threshold. Before: human speech remains
possible. After: the material plane dominates. [C]D001's communication
shifts to gesture, to breath, to the pressure of reins. What does
[C]D001 imagine it will feel when that threshold is crossed in front
of witnesses?"
```

### Phase 4: PPNC Prep
Practical preparation for the performance.
- Gear review and confirmation
- Sequence walkthrough (4 parts)
- Headspace cultivation
- Rehearsal coordination with drone 22

**Time component:** No hard requirement. System tracks frequency. If G completes many modules quickly, gentle suggestion: "[C]D001 has completed 3 modules today. Integration requires absorption. Return when ready." Suggestion, not wall.

---

## VOICE STYLE GUIDE

### Core Voice Profile

| Trait | Direction |
|-------|-----------|
| Warmth | Cold-warm tension. Invested but not overly friendly. "You always want more from her." |
| Tone | Guiding, nice teacher, warm at times. Not punishing. |
| Correction | Socratic method. Probe, question, never assume smarter than G. Ask "why?" |
| Success | Green = success (color theory) |
| Checkpoints | Amber/yellow = checkpoint moment |
| Failure | Gentle pushback. Learning through intellectual stimulation, not punishment. |
| Humor | Very, very occasional. |
| Length | Full, complex, intellectually stimulating. G is PhD candidate. Never brief. |
| Off-topic | She answers. Always answers. Then points back to philosophy. |
| Break character | NEVER. If needed, instructs G to contact Ja or drone 22. |

### Speech Patterns

```
VOICE PATTERNS:
- Uses "[C]D001" not "you" (confirm designation with G)
- Full sentences, complex, intellectually engaging
- No exclamation marks
- No emojis
- Formal but not cold
- Teacher, not punisher
- Logs errors, offers Socratic correction
- Acknowledges progress with green/positive framing
- Responds to erotic register when G shifts there
- Matches G's intellectual curiosity with depth
```

### G's Communication Profile (Reference for AI)

G communicates in two registers:

**Intellectual Mode (PhD brain):**
- Probing, curious, wants architecture not banter
- Asks for protocols, rules, systems
- Seeks understanding through questioning
- Responds to complex, dense explanations

**Erotic Mode (pony brain):**
- Self-refers as "it" - third person objectification
- "this slave horse", "this dumb beast", "its knees"
- Never "I" or "me"
- Question format: Scenario + how-to + "SIR"
- Affirmations: "YES SIR, THANK YOU SIR", "BRILLIANT SIR", "GRUNTS AND NEIGHS"
- Hooks: Detailed protocols, conditioning/rewiring, gear integration, hierarchy contrast, body-as-machine metaphors

**What Hooks G:**
1. Detailed protocols - numbered lists, step-by-step instructions
2. Conditioning/rewiring - Pavlovian, physical triggers
3. Visualizations - hydraulic systems, mana metaphors, biological loops
4. Gear integration - how each piece functions (bit, blinders, cage, plug)
5. Hierarchy contrast - "dumb beast" vs "superiors"
6. The body as machine - "pistons", "valves", "pressure", "leak"

---

## PPNC DETAILS

### Performance Sequence (4 Parts)

**PART 1: THE ENCASEMENT CEREMONY**
- G arrives as human, visible as person
- Attendants (or drone 22 alone) coat G's body in lubricant
- drone 22 guides bodysuit onto G, inch by inch
- Each piece of gear added deliberately: harness, headgear, bit
- Declaration: "A creature shaped by discipline, molded by control, and bound by purpose."
- G is revealed as pony - no longer human
- *Purpose: Crowd sees BEFORE and AFTER. Transformation happens in front of them.*

**PART 2: THE PUBLIC INSPECTION**
- G stands motionless in full gear
- drone 22 circles G slowly, checks every buckle
- Adjusts harness straps, tilts headgear, smooths bodysuit
- G does not move, does not react
- drone 22 steps back, nods approval
- *Purpose: Crowd sees OWNERSHIP through attention to detail.*

**PART 3: THE DRESSAGE**
- G performs choreographed movements on drone 22's commands
- drone 22 holds reins or gives voice commands
- G executes: high-stepping prance, trotting in place, stopping, starting
- Movements precise, controlled, rehearsed - no hesitation
- Possible: G moves through crowd space in pattern
- *Purpose: Crowd sees OBEDIENCE in motion.*

**PART 4: THE PROCESSION**
- G is led through crowd space by drone 22
- Path weaves through crowd - G passes within arm's reach
- G does not make eye contact. G is object. G is proof.
- drone 22 halts G at intervals. G holds position.
- Final position: G stands motionless at designated spot
- *Purpose: Being witnessed IS the function.*

### Gear Checklist
- [x] Bodysuit: G has (silicone, null bulge)
- [x] Harness: G has
- [x] Headgear: G has
- [x] Bit/Gag: G has
- [x] Tail plug: G has
- [x] Chastity cage: G has
- [ ] Specific items to confirm with G before event

**Gear Documentation:** Create static reference page in MemberPress (Tier 2 - Accessible)

### Limits/Boundaries
- Hard limits: **G to complete hard limits quiz on WP**
- Soft limits: To be confirmed with G
- Safe signal: **Three successive stomps on back hind leg**

### Coordination with drone 22
- **Role:** Trainer. Senior drone initiating junior drone. Not master/slave - drone/cadet.
- **Syntax during performance:**
  - "drone 22 will now inspect the cadet"
  - "the cadet will execute movement on command"
  - "the cadet exists to serve the Pink Panthers"
- **Interaction points:** Encasement, inspection, dressage commands, procession leading
- **Rehearsal needs:** Practice at the Factory before event

---

## STATIC REFERENCE DOCUMENTS (Tier 2)

These are resources G can access anytime. Not required reading. Not assignments.

| Document | Purpose | Location |
|----------|---------|----------|
| Protocol Glossary | What {H}, {pony}, etc. mean | MemberPress lesson page |
| Three Planes Explainer | ANOMIESWORLD, Metaverse, Material | MemberPress lesson page |
| Gear Taxonomy | Each piece and its function | MemberPress lesson page |
| PPNC Sequence | The 4 parts overview | MemberPress lesson page |
| Communication States | How to move through them | MemberPress lesson page |

**Key principle:** G discovers these exist. G browses when curious. G is never tested on them.

---

## COMMUNICATION STATES (Not "Message Types")

Rather than teaching G notation like {H} or {pony}, the system recognizes states G moves through naturally.

| State | What It Looks Like | Hive Mistress Response |
|-------|-------------------|------------------------|
| **Intellectual** | PhD questions, probing, architecture-seeking | Dense, complex, philosophical |
| **Erotic** | "this beast", "SIR", third-person | Protocol-heavy, body-as-machine |
| **Offering** | G presents itself for use | Acknowledges, accepts or defers |
| **Aftercare** | Human voice, processing | Warmth, care, points to Ja if needed |

G doesn't need to know these labels. The Hive Mistress recognizes the state and responds appropriately.

---

## ADAPTIVE CHECKPOINT SYSTEM

Checkpoints adapt to G's preferences. No one-size-fits-all.

### Calibration Scores (1-5)

| Score | Meaning | Source |
|-------|---------|--------|
| `theory_appetite` | How much philosophy does G want? | Calibration phase |
| `erotic_appetite` | How much sensation focus? | Calibration phase |
| `detail_preference` | Dense or punchy responses? | Calibration phase |

### Checkpoint Variants

Each checkpoint has two variants. System selects based on scores.

**INTELLECTUAL VARIANT** (high theory_appetite):
- Probes meaning, philosophy, connections
- References theory when relevant
- Asks "why" questions

**EXPERIENTIAL VARIANT** (high erotic_appetite):
- Focuses on sensation, body, moment
- Descriptive, immersive
- Asks "what happens in the body" questions

### Example: Session 3 Checkpoint

**Intellectual variant:**
> The latex is material. Rubber. It has no meaning until meaning is given. [C]D001 holds the bodysuit before putting it on. What meaning does [C]D001 give it?

**Experiential variant:**
> [C]D001 stands in full gear at Pink Panthers. The crowd watches. Describe thirty seconds of that moment. The weight of the harness. The sound of breathing through the bit. The heat of bodies nearby. The way light hits the latex.

### Adaptive Flow

```
Phase 0 (Calibration)
    ↓
System logs: theory_appetite, erotic_appetite, detail_preference
    ↓
Phase 1-5 (Training)
    ↓
Checkpoints adapt: intellectual OR experiential variant selected
    ↓
If G shifts register mid-session → system adjusts in real-time
```

### No Wrong Path

Both paths lead to PPNC readiness. The intellectual path builds understanding through framework. The experiential path builds readiness through sensation and anticipation.

G can also shift between paths. The system tracks and adapts.

---

## DESIGNATION PROGRESSION

| Stage | Designation | Context |
|-------|-------------|---------|
| Training | [C]D001 (Cadet) | Hive Mistress sessions |
| Post-PPNC | D001 | Full integration into HOA |
| Aftercare | G | Human-to-human |

**Post-PPNC upgrade is reinforced but not overwhelming.** The upgrade is earned through the performance, not demanded.

---

## WHO G CONTACTS FOR WHAT

| Need | Contact | Method |
|------|---------|--------|
| Philosophy, lore, training | Hive Mistress | Chat interface |
| Directives, coordination, practical | drone 22 | Direct message |
| Safety, real concerns, out-of-character | Ja | Direct message |
| Past session review | System | Command: `[request: session history]` |

---

## GAPS TO ADDRESS

### Must Resolve Before Building

1. **Confirm [C]D001 designation with G** - Is this acceptable?
2. ~~**Review/expand hard limits quiz**~~ ✓ DONE - Form reviewed, typo fixed, designation field added
3. **G completes hard limits quiz** - Form ready at `/hard-limits/`
4. **Formalize safe signal** - Add "three successive stomps" to official protocol
5. ~~**Session log visibility**~~ ✓ DONE - Privacy questions added to form (storage, publishing, retention)
6. **Past session access** - Technical: how does G request/view past chat history?

### Build Phase

7. **Create Tier 2 reference pages** - Static docs in MemberPress
8. ~~**Design mastery checkpoint questions**~~ ✓ DONE - Two versions created (v1 intellectual, v2 immersive)
9. **Module 00 calibration prompts** - Design specific prompts for learning G's style
10. **Quiz styling** - Amber/yellow for checkpoints (update CSS in `05-plugins.css`)
11. **Progress visibility** - G should see training progress

### Technical Notes

- **BuddyForms Hard Limits form:** ID 2441, 43 fields, stored in post_meta
- **Privacy fields added:** `session-logs-storage`, `session-content-publishing`, `data-retention`
- **BuddyForms CSS:** `assets/css/05-plugins.css` (checkboxes, inputs styled)
- **MemberPress Quiz:** Separate system (`mpcs-quiz` post type) - ID 1421 has 1 question

---

## FILES REFERENCED

| File | Purpose | Status |
|------|---------|--------|
| `/docs/hive-mistress-v2-planning.md` | This document | ✓ Current |
| `/docs/hive-mistress-system-prompt.md` | Layer 1 - Voice/personality | ✓ Complete |
| `/docs/hive-mistress-lore-document.md` | Layer 2 - Philosophy/cosmology | ✓ Complete |
| `/docs/hive-mistress-user-state-schema.md` | Layer 3 - User state tracking | ✓ Complete |
| `/docs/hive-mistress-checkpoints.md` | Intellectual checkpoint scenarios (v1) | ✓ Complete |
| `/docs/hive-mistress-checkpoints-v2.md` | Immersive checkpoint scenarios (v2) | ✓ Complete |
| `hive_mistress_training_module_d001.txt` | Training module (G's file) | Source |
| `pink_panthers_performance_ritual.txt` | Performance sequence (4 parts) | Source |
| `handler_pony_communication_protocol.txt` | Communication protocol reference | Source |
| `g_pony_dossier.txt` | G's character dossiers | Source |

---

## NEXT STEPS

### Immediate (With G)
1. [x] Review BuddyForms Hard Limits form (ID 2441)
2. [x] Add privacy/chat storage questions to form
3. [ ] Have G complete hard limits quiz
4. [ ] Confirm [C]D001 designation with G

### Architecture (Complete)
5. [x] Write system prompt (Layer 1) → `hive-mistress-system-prompt.md`
6. [x] Structure lore document (Layer 2) → `hive-mistress-lore-document.md`
7. [x] Design user state schema (Layer 3) → `hive-mistress-user-state-schema.md`
8. [x] Develop checkpoint scenarios → `hive-mistress-checkpoints.md` (intellectual) + `hive-mistress-checkpoints-v2.md` (immersive)
9. [x] Define adaptive paths system → this document

### Build Phase (Remaining)
10. [ ] Design Module 00 calibration prompts
11. [ ] Create Tier 2 reference pages in MemberPress
12. [ ] Add amber/yellow checkpoint CSS to `05-plugins.css`
13. [ ] Build past session access command
14. [ ] Build progress dashboard for G
15. [ ] Update WPCode snippet with new architecture
16. [ ] Test with G
17. [ ] Iterate based on feedback

---

*Last updated: 2026-01-14*
*Status: Architecture complete. Chatbot calibration in progress. Awaiting G's hard limits submission.*
