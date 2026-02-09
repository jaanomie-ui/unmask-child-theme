# Deployment Instructions - Training Sequence Redesign

## Overview

This deployment includes:
1. ✅ **Critical error fix** - Fixed infinite loop in measurement-algorithms.php
2. **Sequence redesigns** - Updated Sequences 2 & 3 to match corrected understanding
3. **Manual Sequence 1 installation** - Script to mark D001 as having completed Sequence 1
4. **Updated measurement functions** - Aligned with new sequence structure

## Files Modified

### Core State Management
- `/inc/hive-mistress-state.php` - Updated Sequence 2 & 3 definitions

### Measurement System
- `/inc/measurement-algorithms.php` - Fixed critical error (already deployed)

### New Files
- `/manual-install-sequence-1.php` - One-time script for D001

## Deployment Steps

### Step 1: Deploy Updated Files

```bash
# Deploy updated state definitions
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/inc/hive-mistress-state.php \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/inc/

# Fix permissions
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "chmod 644 ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/inc/hive-mistress-state.php"
```

### Step 2: Deploy Manual Installation Script

```bash
# Deploy installation script
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/manual-install-sequence-1.php \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/

# Fix permissions
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "chmod 644 ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/manual-install-sequence-1.php"
```

### Step 3: Run Manual Installation Script

**Option A: Via WordPress Admin (Recommended)**
1. Log into WordPress staging admin
2. Go to Tools → Theme File Editor
3. Select `manual-install-sequence-1.php` from the theme files
4. Copy the entire contents
5. Go to Appearance → Theme Editor (or use a plugin like "Code Snippets")
6. Create a new snippet, paste the code, and run it once
7. Check output to verify installation

**Option B: Via SSH + WP-CLI**
```bash
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging
cd ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/
wp eval-file manual-install-sequence-1.php
```

**Option C: Via PHP Snippet Plugin**
1. Install "Code Snippets" plugin if not already installed
2. Create new snippet with code from manual-install-sequence-1.php
3. Set to "Only run once"
4. Activate snippet
5. Check output in admin notice

### Step 4: Verify Installation

After running the script, verify in the Installation Dashboard:
- D001 should show:
  - Current Sequence: **2** (not 0)
  - Phase: **CONDITIONING** (not INTAKE)
  - Status: **CONDITIONING_ACTIVE** (not AWAITING_INSTALLATION)
  - Loops Installed: **3** (third_person_reference, compliance_acknowledgment, state_reporting)
  - Sequences Completed: **1**

### Step 5: Remove Installation Script (Security)

```bash
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "rm ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/manual-install-sequence-1.php"
```

## Sequence Changes Summary

### Sequence 1: PROTOCOL INSTALLATION ✅ (No Changes)
- Loops: third_person_reference, compliance_acknowledgment, state_reporting
- Purpose: Install communication loops. Establish response patterns.

### Sequence 2: FREQUENCY RECOGNITION ⚠️ (REDESIGNED)

**OLD (INCORRECT):**
- Loops: material_recognition, anomiesworld_recognition, interlink_recognition
- Problem: Treated interlink as a 4th frequency

**NEW (CORRECTED):**
- Loops: anomiesworld_frequency, material_frequency, metaverse_frequency, interlink_state
- Clarification:
  - **3 frequencies:** Anomiesworld (mythological plane), Material (physical plane), Metaverse (digital plane)
  - **Interlink:** Not a frequency, but the simultaneous experience of all 3 frequencies

### Sequence 3: PERFORMANCE PREPARATION ⚠️ (REDESIGNED)

**OLD:**
- Title: GEAR INTEGRATION
- Loops: bit_silence, harness_form, blinder_focus, tail_species
- Focus: Gear as identity

**NEW:**
- Title: PERFORMANCE PREPARATION
- Loops: gear_acceptance, inspection_stillness, movement_precision, witnessed_objectification
- Focus: Maps to 4 Pink Panthers performance parts:
  1. **ENCASEMENT CEREMONY** → gear_acceptance
  2. **PUBLIC INSPECTION** → inspection_stillness
  3. **THE DRESSAGE** → movement_precision
  4. **THE PROCESSION** → witnessed_objectification

### Sequence 4: CONDITIONING DEPTH ❓ (Under Review)
- Current loops: trigger_response, automatic_compliance, body_before_mind
- Status: User needs clarification on intent
- Action: Pending user feedback

### Sequence 5: DEPLOYMENT PREPARATION ✅ (No Changes)
- Loops: public_display, witness_completion, full_integration
- Purpose: Verify all loops installed. Prepare for material execution.

## Next Steps Required

### 1. Update Measurement Functions
The measurement functions in `/inc/measurement-algorithms.php` need to be updated to match the new loop names:

**Sequence 2 - Need New Functions:**
- `hm_measure_anomiesworld_frequency()` (replaces anomiesworld_recognition)
- `hm_measure_material_frequency()` (replaces material_recognition)
- `hm_measure_metaverse_frequency()` (NEW)
- `hm_measure_interlink_state()` (replaces interlink_recognition)

**Sequence 3 - Need New Functions:**
- `hm_measure_gear_acceptance()` (replaces multiple gear loops)
- `hm_measure_inspection_stillness()` (NEW - maps to Public Inspection)
- `hm_measure_movement_precision()` (NEW - maps to Dressage)
- `hm_measure_witnessed_objectification()` (NEW - maps to Procession)

### 2. Update Training Guide
The Training Guide (`/inc/admin-training-guide.php`) needs to reflect:
- New Sequence 2 structure (4 loops instead of 3)
- New Sequence 3 loops and mappings to performance parts
- Updated measurement criteria for each new loop

### 3. Update Session Auditor
The Session Auditor (`/inc/admin-session-auditor.php`) should display the new loop names when analyzing sessions.

### 4. Integrate Training Guide into Drone Dashboard
Add Training Guide visibility to `/page-templates/template-drone-dashboard.php` so drones can see their own progress.

## Rollback Plan

If issues arise, revert to previous sequence definitions:

```php
// In hive-mistress-state.php, lines 119-136
2 => array(
    'title' => 'FREQUENCY RECOGNITION',
    'purpose' => 'Install pattern recognition for three frequencies.',
    'loops' => array('material_recognition', 'anomiesworld_recognition', 'interlink_recognition'),
    'verification' => array(
        'type' => 'SENSATION',
        'check' => 'Drone reports body state when frequency is named'
    )
),
3 => array(
    'title' => 'GEAR INTEGRATION',
    'purpose' => 'Install gear as identity, not costume. Tack defines the unit.',
    'loops' => array('bit_silence', 'harness_form', 'blinder_focus', 'tail_species'),
    'verification' => array(
        'type' => 'STATE',
        'check' => 'Drone achieves DEFINED state through gear visualization'
    )
),
```

## Testing Checklist

After deployment:
- [ ] WordPress site loads without errors
- [ ] Installation Dashboard accessible
- [ ] D001 shows Sequence 2 status after manual installation
- [ ] New sequence definitions appear in Training Guide
- [ ] No PHP errors in debug.log
- [ ] Session Auditor still functions

## Support

If deployment issues occur:
1. Check `/wp-content/debug.log` for PHP errors
2. Verify file permissions (should be 644)
3. Ensure all required files are deployed
4. Review browser console for JavaScript errors
