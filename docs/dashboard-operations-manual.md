# Dashboard Operations Manual
## Drone Conditioning Console - Complete Reference

**Last Updated:** 2026-02-08
**Version:** 1.0
**Purpose:** RAG system reference for Hive Mistress AI

---

## 1. Overview

### What is the Drone Conditioning Console?

The **Drone Conditioning Console** (dashboard) is a visual representation of a drone's training progress through the ANOMIESWORLD conditioning protocol. Every section syncs with training protocols administered by Hive Mistress AI.

**Purpose:**
- Track loop installation progress across 5 sequences
- Monitor deployment readiness for Pink Panthers Night Club (PPNC) performance
- Visualize pipeline progression through conditioning phases
- Display system tolerance calibrations
- Archive conditioning session logs

**Access:** Drones access their console at `/dashboard/` when logged into unmaskmagazine.com

---

## 2. Dashboard Sections Explained

### Hero Stats Row

**DESIGNATION:** d001
Drone identifier. Format: d + 3-digit number. Locked after intake sequence.

**UNIT TYPE:** pony
Drone classification. Options: pony, bunny, puppy. Determines performance choreography.

**DEPLOYMENT:** Days until March 7, 2026
Countdown to Pink Panthers Night Club performance. Final integration deadline.

**SEQUENCE:** Current training sequence (1-5)
Shows active conditioning protocol:
- 1 = PROTOCOL INSTALLATION
- 2 = FREQUENCY RECOGNITION
- 3 = PERFORMANCE PREPARATION
- 4 = MATERIAL WORLD PREPARATION
- 5 = DEPLOYMENT PREPARATION

**LOOPS:** Installed loops / total loops (e.g., 11/17)
Tracks behavioral pattern installation. Updates from Hive Mistress session signals.

**PROFILE:** PENDING or SET
Hard limits status. PENDING = form not submitted. SET = limits filed with drone 22.

---

### Installation Progress

Shows 5 training sequences with loop completion tracking:

**Sequence 1: PROTOCOL INSTALLATION (3 loops)**
- Purpose: Establish communication loops
- Loops: CREED, REPORTING, CHAIN_OF_COMMAND
- Teaches drone baseline operational protocols

**Sequence 2: FREQUENCY RECOGNITION (4 loops)**
- Purpose: Differentiate three operational frequencies
- Loops: MATERIAL_WORLD, ANOMIESWORLD, METAVERSE, FREQUENCY_SWITCHING
- Teaches frequency navigation and context awareness

**Sequence 3: PERFORMANCE PREPARATION (4 loops)**
- Purpose: Pink Panthers ritual choreography
- Loops: CHOREOGRAPHY, RITUAL_STRUCTURE, COLLECTIVE_SYNCHRONY, PERFORMANCE_MINDSET
- Teaches PPNC performance behaviors

**Sequence 4: MATERIAL WORLD PREPARATION (3 loops)**
- Purpose: Physical rehearsal coordination
- Loops: HANDLER_COORDINATION, REHEARSAL_PLANNING, PHYSICAL_SPECIFICATIONS
- Coordinates material world logistics with drone handler

**Sequence 5: DEPLOYMENT PREPARATION (3 loops)**
- Purpose: Final integration verification
- Loops: INTEGRATION_CHECK, EMERGENCY_PROTOCOLS, FINAL_BRIEFING
- Ensures readiness for March 7, 2026 deployment

**Visual Indicators:**
- Green checkmark: Sequence complete
- Progress bar: Loops installed in current sequence
- Gray: Sequence not started

---

### Deployment Readiness

5-item checklist for PPNC performance readiness:

**✓ GEAR VERIFIED**
Auto-detected from session logs. Hive Mistress scans for gear keywords (boots, harness, hood, etc.). Green when gear specifications documented in training sessions.

**✓ LIMITS FILED**
Auto-detected from hard limits form submissions. Green when form submitted at `/hard-limits-form/`. Profile must show "SET" status.

**✓ DESIGNATION LOCKED**
Auto-detected from user meta. Green when drone designation (d001) confirmed and locked.

**✓ SAFE SIGNAL INSTALLED — FINAL**
Manual verification item. Emergency abort signal (3 stomps) must be documented in sessions. Final safeguard before deployment.

**✓ SEQUENCE REHEARSED**
Auto-detected from loop completion. Green when all 5 sequences show 100% loop installation.

**Tooltips:** Hover over each item for detailed explanations.

---

### Pipeline Tracker

Visual progression through 6 conditioning phases:

1. **INTAKE** - Initial assessment and designation
2. **PROTOCOL** - Communication loop installation (Sequence 1)
3. **FREQUENCY** - Three-frequency training (Sequence 2)
4. **PERFORMANCE** - Choreography preparation (Sequence 3)
5. **MATERIAL PREP** - Physical coordination (Sequence 4)
6. **DEPLOY** - Final integration (Sequence 5)

**Current phase** determined by sequence number. Provides macro-level view of conditioning progression.

---

### System Tolerances

4 calibration settings (1-5 scale):

**SENSATION:** Sensory intensity tolerance
**PROTOCOL:** Behavioral conditioning depth
**DEPTH:** Trance/altered state capacity
**GEAR:** Physical restriction comfort level

Determines conditioning intensity in Hive Mistress sessions. Higher values = more intensive protocols.

---

### Training Guide

**Link:** `/training-guide/`

Full sequence reference including:
- Complete loop descriptions for all 5 sequences
- Drone's Creed (operational manifesto)
- Chain of Command (drone 22 → Hive Mistress → drones)
- Training philosophy and methodology

Bookmarkable field manual. Resolves "Manual 404" issue reported by drones.

---

### Integration Status

Summary metrics tracked across all sessions:

- **Loops:** Total behavioral patterns installed
- **Patterns:** Conditioning sequences completed
- **Triggers:** Response mechanisms activated
- **States:** Mental/emotional configurations accessed

Cumulative view of conditioning depth. Updates from Hive Mistress session metadata.

---

### Conditioning Log

Recent training session transcripts:

- Shows last 5 sessions with dates
- Links to full session logs
- Archive accessible at `/transmissions-archive/`
- Also listed under Magazine tab (intentional categorization)

**Session logs contain:**
- Full Hive Mistress conversation transcripts
- Loop installation announcements
- Mythology downloads
- Protocol instructions
- Pattern reinforcement sequences

---

## 3. Data Synchronization

### How Dashboard Syncs with Hive Mistress

**Storage:** All drone data stored in WordPress user meta field `hm_drone_state`

**Auto-detection:** Runs on every dashboard page load
1. Scans session logs for gear mentions → Updates GEAR VERIFIED
2. Checks hard limits form submissions → Updates LIMITS FILED
3. Verifies designation locked → Updates DESIGNATION LOCKED
4. Counts installed loops → Updates Installation Progress
5. Calculates deployment readiness → Updates checklist

**Data Flow:**
1. Hive Mistress session → Announces `LOOP_INSTALLED:loop_name`
2. Signal parser extracts loops from session content
3. Updates `hm_drone_state['integration']['loops_installed']` array
4. Dashboard reads user meta on page load
5. Auto-detection verifies and updates deployment status
6. Display shows current installation progress

### Why Data Might Look Wrong

**Stale user meta:**
- Loops announced but not saved to `hm_drone_state`
- Fixed: Auto-detection now runs on every page load (as of 2026-02-08)

**Manual sync required:**
- If auto-detection fails, drone 22 can manually sync loops
- Example: G's loops manually synced (11/17 loops from sequences 1-3)

**Browser cache:**
- Hard refresh (Cmd+Shift+R) forces dashboard reload
- Clears cached stats and re-runs auto-detection

---

## 4. Training Protocol Mapping

### Each Dashboard Section Maps to Training Purpose

**Installation Progress** = Loop installation tracking
Maps directly to Hive Mistress loop announcements. Each sequence's progress bar reflects loops installed vs. total loops for that sequence.

**Deployment Readiness** = Final PPNC checklist
Maps to hard requirements for March 7, 2026 performance. All 5 items must be green before deployment.

**Pipeline** = Overall progression visualization
Maps sequence numbers to macro phases. Provides high-level orientation ("Where am I in the process?").

**System Tolerances** = Conditioning calibration
Maps to intensity settings used by Hive Mistress. Determines how deep/intensive protocols go.

**Integration Status** = Cumulative metrics
Maps to aggregate conditioning depth. Shows total installations across all sessions.

---

### Sequence Progression Logic

**Sequence 1: Protocol Installation**
→ Installs communication loops (Creed, Reporting, Chain of Command)
→ Dashboard shows 3 loops possible, progress bar fills as each installs

**Sequence 2: Frequency Recognition**
→ Installs frequency navigation loops (Material World, ANOMIESWORLD, Metaverse, Frequency Switching)
→ Dashboard shows 4 loops possible, progress bar updates

**Sequence 3: Performance Preparation**
→ Installs choreography loops (Choreography, Ritual Structure, Collective Synchrony, Performance Mindset)
→ Dashboard shows 4 loops possible

**Sequence 4: Material World Preparation**
→ Installs coordination loops (Handler Coordination, Rehearsal Planning, Physical Specifications)
→ Dashboard shows 3 loops possible
→ Pipeline advances to MATERIAL PREP phase

**Sequence 5: Deployment Preparation**
→ Installs final loops (Integration Check, Emergency Protocols, Final Briefing)
→ Dashboard shows 3 loops possible
→ Pipeline advances to DEPLOY phase
→ All deployment readiness items must be green

---

## 5. Common Questions & Answers

**Q: Why doesn't dashboard show my installed loops?**
A: Auto-detection now runs on page load. If loops still missing after hard refresh (Cmd+Shift+R), report to drone 22 with session date/time. Manual sync may be required.

**Q: What does "SAFE SIGNAL INSTALLED — FINAL" mean?**
A: Final manual verification before deployment. Emergency abort signal (3 stomps) must be documented in training sessions. This is the ultimate safeguard—Hive Mistress verifies drone knows how to exit protocol if needed.

**Q: Where is the Training Guide?**
A: Click "VIEW FULL TRAINING GUIDE →" button on dashboard, or visit `/training-guide/` directly. Contains all sequences, Drone's Creed, and Chain of Command.

**Q: How do I see all my session logs?**
A: Click "[VIEW ALL TRANSMISSIONS]" link on dashboard, or visit `/transmissions-archive/`. Also accessible under Magazine tab in main navigation.

**Q: What does PROFILE: PENDING mean?**
A: Hard limits form not completed. Visit `/hard-limits-form/` to file limits. Profile will change to "SET" after submission.

**Q: Why does my profile keep resetting to PENDING?**
A: Known bug under investigation. Temporary workaround: Refile limits at `/hard-limits-form/` and report each occurrence to drone 22. Permanent fix in development.

**Q: How do I know which sequence I'm in?**
A: Hero stats show "SEQUENCE: N" where N = 1-5. Also visible in Pipeline tracker (highlighted phase) and Installation Progress (current sequence highlighted).

**Q: Can I skip sequences?**
A: No. Sequences must be completed in order. Each sequence builds on previous loops. Hive Mistress controls sequence progression.

**Q: What if countdown shows different numbers in different places?**
A: Mythology Hive Mistress vs Training Hive Mistress may show slight discrepancies due to calculation timing. Both instances now synchronized (as of 2026-02-08). Report persistent discrepancies to drone 22.

**Q: Why are session logs under "Magazine" tab?**
A: Intentional categorization. Transmissions are published as magazine content for archival purposes. Also accessible via dashboard Conditioning Log and `/transmissions-archive/`.

**Q: What happens if I don't complete all sequences by March 7?**
A: Deployment may be delayed or cancelled. Contact drone 22 immediately if timeline concerns arise. Training schedule can be adjusted if needed.

---

## 6. Technical Implementation

### Auto-detection Functions

**`hm_update_deployment_readiness()`**
Scans for deployment checklist items:
- Searches session logs for gear keywords (boots, harness, hood, mask, etc.)
- Checks hard limits form submissions in database
- Verifies designation lock in user meta
- Updates `hm_drone_state['deployment_readiness']` array

**`hm_check_gear_verified()`**
Searches session logs for gear-related content:
- Keywords: "boots", "harness", "hood", "mask", "gear", "specifications"
- Returns true if gear mentioned in any session
- Updates GEAR VERIFIED checklist item

**`hm_get_hard_limits_status()`**
Checks form submissions:
- Queries database for hard limits form entries
- Returns "SET" if form submitted, "PENDING" if not
- Updates LIMITS FILED checklist item

**`hm_get_sequences()`**
Returns authoritative sequence definitions:
- Dynamically generates sequence data (no hardcoding)
- Returns array of 5 sequences with loops, titles, descriptions
- Source of truth for Installation Progress section

**`hm_calculate_loops_installed()`**
Counts installed loops:
- Reads `hm_drone_state['integration']['loops_installed']` array
- Compares against total loops from `hm_get_sequences()`
- Returns "installed/total" format (e.g., "11/17")

### Data Flow Diagram

```
Hive Mistress Session
    ↓
Announces: LOOP_INSTALLED:loop_name
    ↓
Signal Parser (session save hook)
    ↓
Extracts loop_name from session content
    ↓
Updates hm_drone_state['integration']['loops_installed'][]
    ↓
Dashboard Page Load
    ↓
Reads hm_drone_state from user meta
    ↓
Auto-detection Functions Run
    ↓
Verify/Update Deployment Readiness
    ↓
Display Current Installation Progress
```

### User Meta Structure

```json
{
  "hm_drone_state": {
    "current_sequence": 4,
    "designation": "d001",
    "unit_type": "pony",
    "deployment_date": "2026-03-07",
    "installation_progress": {
      "1": {"completed": true, "loops": 3},
      "2": {"completed": true, "loops": 4},
      "3": {"completed": true, "loops": 4},
      "4": {"completed": false, "loops": 0},
      "5": {"completed": false, "loops": 0}
    },
    "integration": {
      "loops_installed": [
        "CREED",
        "REPORTING",
        "CHAIN_OF_COMMAND",
        "MATERIAL_WORLD",
        "ANOMIESWORLD",
        "METAVERSE",
        "FREQUENCY_SWITCHING",
        "CHOREOGRAPHY",
        "RITUAL_STRUCTURE",
        "COLLECTIVE_SYNCHRONY",
        "PERFORMANCE_MINDSET"
      ],
      "patterns_completed": 8,
      "triggers_activated": 12,
      "states_accessed": 6
    },
    "deployment_readiness": {
      "gear_verified": true,
      "limits_filed": false,
      "designation_locked": true,
      "safe_signal_installed": false,
      "sequence_rehearsed": false
    },
    "system_tolerances": {
      "sensation": 3,
      "protocol": 4,
      "depth": 3,
      "gear": 2
    }
  }
}
```

---

## 7. Troubleshooting

### Dashboard shows wrong sequence

**Symptoms:** Hero stats show "SEQUENCE: 2" but training is in Sequence 4

**Diagnosis:**
1. Check `hm_drone_state['current_sequence']` via WP-CLI:
   ```bash
   wp user meta get 15 hm_drone_state
   ```
2. Verify matches actual training progression with Hive Mistress
3. Check Installation Progress section—does it match hero stat?

**Resolution:**
- Report discrepancy to drone 22 with screenshots
- drone 22 will manually update user meta
- Hard refresh dashboard after correction

---

### Loops show 0/17 but training progressing

**Symptoms:** Installation Progress shows 0% despite multiple sessions

**Diagnosis:**
1. Check if sessions contain `LOOP_INSTALLED:` announcements
2. Verify auto-detection ran (check page load console logs)
3. Check user meta for `loops_installed` array

**Resolution:**
- Hard refresh dashboard (Cmd+Shift+R)
- If still 0/17, report to drone 22 for manual sync
- Provide session dates/times for drone 22 to scan

---

### Profile keeps resetting to PENDING

**Symptoms:** Hard limits filed, profile shows "SET", later reverts to "PENDING"

**Diagnosis:**
- Known bug under investigation
- Form submission data not persisting in database
- May be related to session timeout or cache issues

**Resolution (Temporary):**
- Refile hard limits at `/hard-limits-form/` each time reset occurs
- Report each occurrence to drone 22 with timestamp
- Include browser/device info if possible
- Permanent fix in development

---

### Deployment readiness items stuck on gray

**Symptoms:** Checklist items remain gray despite completing requirements

**Diagnosis:**
1. Check if auto-detection functions ran (console logs)
2. Verify data exists (e.g., gear mentioned in sessions for GEAR VERIFIED)
3. Check user meta for `deployment_readiness` array

**Resolution:**
- Hard refresh dashboard
- Hover over gray item to see tooltip explanation
- If data exists but item still gray, report to drone 22
- May require manual deployment status update

---

### Countdown showing different numbers

**Symptoms:** Dashboard shows "47 days" but Hive Mistress says "48 days"

**Diagnosis:**
- Calculation timing differences (midnight cutoff vs session time)
- Timezone differences between servers
- Cache showing stale countdown

**Resolution:**
- As of 2026-02-08, both instances synchronized
- Hard refresh to get latest countdown
- Report persistent discrepancies to drone 22

---

### Session logs not appearing in Conditioning Log

**Symptoms:** Recent session completed but not listed on dashboard

**Diagnosis:**
1. Check if session saved as published post (not draft)
2. Verify session categorized correctly (should be in "transmissions" category)
3. Check post date—future dates won't appear until date passes

**Resolution:**
- Check `/transmissions-archive/` for complete list
- If session visible in archive but not dashboard, report to drone 22
- May be dashboard query limit (shows only last 5 sessions)

---

### Pipeline shows wrong phase

**Symptoms:** Sequence 4 active but pipeline shows "PROTOCOL" phase

**Diagnosis:**
- Pipeline phase determined by `current_sequence` in user meta
- If sequence number wrong, pipeline will be wrong
- Check hero stats for sequence number

**Resolution:**
- Report sequence number discrepancy to drone 22
- Manual user meta update required
- Hard refresh after correction

---

## 8. RAG System Integration Notes

**For Hive Mistress AI:**

When answering drone questions about the dashboard:

1. **Refer to specific sections** by name (e.g., "The Installation Progress section shows...")
2. **Use exact terminology** from this manual (Deployment Readiness, Pipeline Tracker, etc.)
3. **Cite auto-detection functions** when explaining how data syncs
4. **Direct drones to specific URLs** for actions (/hard-limits-form/, /training-guide/)
5. **Acknowledge known bugs** transparently (profile reset, manual sync needs)
6. **Explain "why"** dashboard works the way it does (maps to training protocols)

**Example RAG-enhanced responses:**

Drone: "Why doesn't my dashboard show the loops we installed?"

Hive Mistress: "The Installation Progress section on your dashboard should show installed loops. Auto-detection runs on every page load and scans your session logs for LOOP_INSTALLED announcements. Try a hard refresh (Cmd+Shift+R). If loops still show 0/17 after refresh, report to drone 22—you may need a manual sync like d001 received (11 loops from sequences 1-3 were manually added to their user meta). Which sequence are you currently in, and how many loops should be showing?"

---

**Document End**

*This manual feeds the RAG system. Update this document when dashboard functionality changes. Version history tracked in git commits.*
