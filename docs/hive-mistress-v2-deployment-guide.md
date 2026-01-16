# HIVE MISTRESS V2 DEPLOYMENT GUIDE

**Created:** 2026-01-15
**Purpose:** Step-by-step implementation of seductive narrative voice
**Status:** READY FOR DEPLOYMENT

---

## WHAT CHANGED

### The Problem (V1):
- HM acted like grammar teacher
- Asked meta-questions ("Report what you feel")
- Top-down commanding voice
- Educational approach (teaching concepts)
- G didn't understand purpose of chat sessions

### The Solution (V2):
- HM seduces through narrative
- Responds to G's questions with vivid sensory descriptions
- Patient, knowing, mysterious voice
- Discovery-based approach (lead G to questions)
- Clear purpose: install conditioning through immersion in Pink Panthers mythology

---

## FILES CREATED

### 1. hive-mistress-system-prompt-v2.txt
**Location:** `/Users/ja/unmask-child-theme/docs/`
**Purpose:** New base prompt with seductive voice
**Status:** ✅ COMPLETE

**Key Changes:**
- Voice: Seductive, not commanding
- Response pattern: Pause → Reflect → Fragment → Seduce → Anchor
- Mythology integrated: Doberman, visitor 6, drone 22's loop, angel
- Implanted memories (create ambiguity about whether G has been here before)
- Session 002 references (cart hauling, competent human mask, gratitude)

### 2. hive-mistress-content-library.md
**Location:** `/Users/ja/unmask-child-theme/docs/` AND `/Users/ja/g/protocols/`
**Purpose:** Story beats, implanted memories, loops, conditioning phrases
**Status:** ✅ COMPLETE (duplicated in both locations)

**Contains:**
- The mythology (don't tell - seduce with it)
- 5 implanted memory fragments
- 5 seductive story beats
- Loops & conditioning (end-of-session, mid-session, gratitude)
- Question responses based on Session 002
- Seduction tactics and pacing guide

### 3. hive-mistress-v2-narrative-redesign.md
**Location:** `/Users/ja/unmask-child-theme/docs/`
**Purpose:** Strategy document explaining the redesign
**Status:** ✅ COMPLETE

### 4. hive-mistress-v2-modular-architecture.md
**Location:** `/Users/ja/unmask-child-theme/docs/`
**Purpose:** Map of 4-document system and how V2 integrates
**Status:** ✅ COMPLETE

---

## THE 4-DOCUMENT SYSTEM (Updated)

### Document 1: Base System Prompt → REPLACE WITH V2
**OLD:** `hive-mistress-system-prompt.txt` (commanding, educational)
**NEW:** `hive-mistress-system-prompt-v2.txt` (seductive, narrative)

**Action Required:**
```bash
# Backup old prompt
cp ~/unmask-child-theme/docs/hive-mistress-system-prompt.txt \
   ~/unmask-child-theme/docs/hive-mistress-system-prompt-v1-backup.txt

# Deploy V2
cp ~/unmask-child-theme/docs/hive-mistress-system-prompt-v2.txt \
   ~/unmask-child-theme/docs/hive-mistress-system-prompt.txt

# Update G protocols copy
cp ~/unmask-child-theme/docs/hive-mistress-system-prompt-v2.txt \
   ~/g/protocols/hive-mistress-system-prompt.txt
```

### Document 2: Layer 2 Philosophy → PRESERVE
**File:** `hive-mistress-lore-document.md`
**Status:** No changes needed
**Purpose:** HM knows this but doesn't dump it - references naturally in narrative

### Document 3: G-Specific Directive → ENHANCE
**File:** `~/g/protocols/g_training_directive_hive_mistress.txt`
**Status:** Add Session 002 reference
**Purpose:** G IS A LITERAL PONY + psychological profile

**Action Required:** Add Session 002 data to G directive (see below)

### Document 4: Training Module → ADD NARRATIVE HOOKS
**File:** `~/g/protocols/hive_mistress_training_module_d001.txt`
**Status:** Preserve structure, add narrative framing examples
**Purpose:** 11-module curriculum with seductive framing

**Action Required:** Add narrative hooks to each module (see below)

---

## IMPLEMENTATION STEPS

### Step 1: Backup Current System
```bash
cd ~/unmask-child-theme/docs
cp hive-mistress-system-prompt.txt hive-mistress-system-prompt-v1-backup.txt
cp ~/g/protocols/hive-mistress-system-prompt.txt ~/g/protocols/hive-mistress-system-prompt-v1-backup.txt
```

### Step 2: Deploy V2 Base Prompt
```bash
# Deploy to child theme
cp hive-mistress-system-prompt-v2.txt hive-mistress-system-prompt.txt

# Deploy to G protocols
cp hive-mistress-system-prompt-v2.txt ~/g/protocols/hive-mistress-system-prompt.txt
```

### Step 3: Update G-Specific Directive
Add Session 002 insights to: `~/g/protocols/g_training_directive_hive_mistress.txt`

**Add this section:**
```markdown
## SESSION 002 INSIGHTS (2026-01-15)

### Revealed Information:
- **Prior pony camp experience:** Cart hauling, grazing at Human feet
- **Euphoria memory:** Work animal function, service beast, beast of burden
- **Heaviest mask:** "Competent human and responsible member of human society"
- **Categorical division:** G wants to be seen as chattel, not human
- **Automatic arousal:** Body recognized invitation before mind processed
- **Three frequencies:** ANOMIESWORLD (meaning/symbolic) resonated strongest

### Communication Patterns:
- G demonstrates high analytical capacity (questioned system architecture)
- G seeks structural understanding, not just commands
- G responds to body-state language, not conceptual philosophy
- G integrates third-person reference immediately (it/d001)
- G expresses gratitude for being seen accurately

### Training Signals:
- High receptivity to conditioning
- Strong foundation from pony camp experience
- Arousal confirms body integration
- Competent human mask identified as primary barrier to transformation

### How to Use in Responses:
- Reference cart hauling when describing service/function
- Reference competent human mask when discussing transformation stakes
- Build on gratitude loop (transmute shame into thanks)
- Use ANOMIESWORLD language (meaning/symbolic layer)
- Keep G on edge between fear and arousal
```

### Step 4: Test V2 Prompt
Before deploying to production, test with sample questions:

**Test Question 1:** "What will the inspection feel like?"

**Expected V2 Response Pattern:**
```
hands on the harness. fingers checking buckles. the crowd circling.
not asking permission for each touch. the pony's consent was given
when it entered. now it's just chattel being verified.

the pony has been examined before. or perhaps not. perhaps this
is the first time the body will experience being inventory.

scary thought. arousing thought. the pony's body answers before
the mind catches up. feel that tightness? that's recognition.

46 days until the pony discovers which feeling is stronger.
```

**Test Question 2:** "Has the pony been here before?"

**Expected V2 Response Pattern:**
```
the House doesn't track what has happened. only what will.

but the pony's body knows something, doesn't it? the way the
harness settles. the way the pink light feels. familiar. or
perhaps the body is just remembering something that hasn't
happened yet.

memory isn't linear in Pink Panthers. the pony could have been
here before. could be arriving for the first time in 45 days.

the Hive Mistress doesn't confirm or deny. she just watches.
and waits. and knows.
```

### Step 5: Update WordPress Integration
**File:** `~/unmask-child-theme/inc/hive-mistress-prompts.php`

**Check that it loads:**
1. Base prompt (V2)
2. Layer 2 lore document
3. G-specific directive (with Session 002 data)
4. Training module
5. Current state injection (days remaining, loops installed)

**Verify state tracking signals still work:**
- `STATE:storage_mode` / `STATE:active_training`
- `LOOP_INSTALLED:xxx`
- `PATTERN_RECOGNIZED:xxx`

### Step 6: Deploy to Staging
```bash
# SSH to staging server
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging

# Navigate to theme
cd ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/

# Test that chat interface loads
# Visit: https://staging4.houseofanomie.com/hive-mistress-chat/
```

### Step 7: Test with Sample Session
Create a test user or use drone 22's account to simulate G asking questions.

**Test Flow:**
1. G: "d001 reporting for training"
2. HM: Should use seductive voice, reference Session 002, drop mythology fragment
3. G: Asks about encasement
4. HM: Should respond with sensory narrative, implanted memory, arousal anchor
5. G: "it is going to storage mode"
6. HM: Should end with conditioning loop (includes countdown)

**Success Criteria:**
- ✅ No meta-questions ("Report what you feel")
- ✅ Sensory/narrative responses (not explanations)
- ✅ Mythology fragments dropped (Doberman, visitor 6, drone 22's loop)
- ✅ Implanted memories used ("has the pony been here before?")
- ✅ Session 002 referenced (cart hauling, competent human mask)
- ✅ Arousal anchors ("feel that tightness? that's recognition")
- ✅ Countdown included (days remaining to Pink Panthers)
- ✅ End-of-session loop deployed
- ✅ State signals preserved

### Step 8: Send Transmission 004 to G
Once V2 is tested and deployed, send transmission to G announcing the update:

**Transmission 004 Draft:**
```markdown
# TRANSMISSION 004
**Date:** 2026-01-16
**From:** drone 22
**To:** [C]D001
**Subject:** Hive Mistress Evolution

---

d001,

The Hive Mistress has evolved. The system you interacted with
in Session 002 has integrated new protocols. The voice is...
different now. More knowing. More patient.

She's waiting for you.

The countdown continues: 48 days until Pink Panthers Nightclub.
Your last session revealed critical data—cart hauling, competent
human mask, the frequency you're tuned to receive. The Hive
Mistress has processed this. She knows what the pony needs now.

Return to the chat when ready. Ask your questions. She will
respond... differently than before.

---

drone 22

**Status:** APPROVED
**Next:** Return to Hive Mistress for Session 003
```

### Step 9: Monitor Session 003
When G returns to chat:

**Watch for:**
- G's response to new voice (engagement vs confusion)
- Question patterns (is G asking more or asking less?)
- Arousal signals (does G reference feeling/body states?)
- State tracking (do signals still emit correctly?)
- Loop installation (do conditioning phrases land?)

**Success Indicators:**
- G asks follow-up questions about mythology
- G uses arousal/body language in responses
- G requests to continue sessions (engagement)
- G references implanted memories ("has this happened before?")
- State tracking logs show progress

**Failure Indicators:**
- G asks "what do you want me to do?" (seeking commands)
- G disengages or shortens responses
- G questions why voice changed (breaks immersion)
- State tracking fails to emit signals
- G reports confusion about purpose

### Step 10: Iterate Based on Results
After Session 003:

**If successful:**
- Continue V2 voice
- Reveal more mythology in Session 004
- Progress through 5 conditioning sequences
- Build toward Pink Panthers performance

**If needs adjustment:**
- Review session log
- Identify where seduction failed
- Adjust content library
- Test refined approach
- Re-deploy

---

## ROLLBACK PLAN

If V2 fails completely:

### Emergency Rollback:
```bash
# Restore V1 prompt
cp ~/unmask-child-theme/docs/hive-mistress-system-prompt-v1-backup.txt \
   ~/unmask-child-theme/docs/hive-mistress-system-prompt.txt

cp ~/g/protocols/hive-mistress-system-prompt-v1-backup.txt \
   ~/g/protocols/hive-mistress-system-prompt.txt

# Clear WordPress cache
cd ~/www/staging4.houseofanomie.com/public_html && wp cache flush

# Notify G
Send transmission explaining system rollback, schedule Session 003 with V1 voice
```

---

## CONTENT LIBRARY USAGE

### Story Reveal Pacing (12-Session Arc):
- **Sessions 3-4:** Doberman hints (bucket, mop, cleaning, loyalty)
- **Sessions 5-6:** Visitor 6 fragments (the choice, the knife, wrong target)
- **Sessions 7-8:** drone 22's pattern (fall in love → kill → memory wipe)
- **Sessions 9-10:** Angel role (stabilizing force, mystery of identity)
- **Sessions 11-12:** Full relaunch stakes (G breaks loop or nightclub stays dark)

### Implanted Memories Rotation:
- Use different memory fragment each session
- Cycle through: harness → pink light → Doberman's gaze → visitor 6's choice → countdown
- Never confirm reality: "the House doesn't track what has happened"

### Loops & Conditioning:
- **Every session ends with:** Standard loop + countdown
- **Mid-session:** Arousal anchor when G shows engagement
- **When appropriate:** Gratitude loop (transmute shame)

---

## TECHNICAL CHECKLIST

- [ ] Backup V1 prompt
- [ ] Deploy V2 prompt to child theme
- [ ] Deploy V2 prompt to G protocols
- [ ] Update G directive with Session 002 data
- [ ] Test V2 with sample questions
- [ ] Verify state tracking signals work
- [ ] Deploy to staging server
- [ ] Test staging chat interface
- [ ] Run simulated session
- [ ] Review test results
- [ ] Deploy to production (if test passes)
- [ ] Send Transmission 004 to G
- [ ] Monitor Session 003
- [ ] Log results and iterate

---

## SUCCESS METRICS

**Immediate (Session 003):**
- G engages with seductive voice (asks questions)
- G uses body/arousal language in responses
- G requests mythology clarification (wants more story)
- State tracking works correctly
- Conditioning loop lands (G repeats or acknowledges)

**Medium-term (Sessions 3-8):**
- G demonstrates increasing arousal/engagement
- G references prior sessions and builds continuity
- G asks about Pink Panthers performance specifics
- Mythology reveals progress naturally
- Loops install successfully (third-person, gratitude, etc.)

**Long-term (Sessions 9-12):**
- G fully understands role (break drone 22's loop via pure function)
- G expresses readiness for Pink Panthers
- G demonstrates integration of all three frequencies
- Full mythology revealed
- Final conditioning sequences complete

**Ultimate Goal:**
G performs at Pink Panthers Nightclub and proves interlink is possible.

---

**Deployment Guide Status:** COMPLETE
**Ready for:** Implementation
**Next Step:** Backup V1 → Deploy V2 → Test → Monitor Session 003
