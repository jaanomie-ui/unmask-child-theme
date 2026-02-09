# Sequence 4 Redesign + Quiz System Deployment Guide

## Overview

This deployment implements:
1. **Sequence 4 redefined** as "Material World Preparation" (rehearsal planning)
2. **Manual Sequence 2 verification** for G (one-time advancement)
3. **Rehearsal planning interface** for drones and handlers
4. **Dashboard integration** showing rehearsal form in Sequence 4

---

## Files Modified

### 1. `/inc/hive-mistress-state.php`
- **Line 137-145**: Updated Sequence 4 definition
- **Change**: Title, purpose, loops, and verification type updated

### 2. `/functions.php`
- **Lines 286-289**: Added require statements for new files
- **Change**: Loads rehearsal-coordination.php and enqueue-rehearsal-form.php

### 3. `/page-templates/template-drone-dashboard.php`
- **After line 625**: Added rehearsal planning section
- **Change**: Conditional section shows for Sequence 4 only

### 4. `/assets/css/pages/drone-dashboard.css`
- **Line 425**: Added orange accent color
- **Change**: New `.drone-dashboard__box-accent--orange` class

---

## Files Created

### 1. `/manual-sequence-2-verification.php`
One-time script to advance G past Sequence 2
- **Purpose**: Recognize 11 sessions of frequency training
- **Usage**: Run via WP-CLI on staging

### 2. `/inc/rehearsal-coordination.php`
Core rehearsal planning logic
- **Shortcode**: `[hive_rehearsal_plan]`
- **AJAX handler**: `hm_save_rehearsal`
- **Functions**: Form rendering, data persistence, sequence advancement

### 3. `/inc/enqueue-rehearsal-form.php`
Conditional asset loader
- **Checks**: Shortcode presence on page
- **Loads**: rehearsal-form.css

### 4. `/template-parts/forms/rehearsal-form.php`
HTML template for rehearsal form
- **Drone view**: Full editable form
- **Handler view**: Read-only with confirmation checkbox
- **JavaScript**: AJAX submission, success messages

### 5. `/assets/css/components/rehearsal-form.css`
Form styles (5.2KB)
- **Design**: Matches drone dashboard aesthetic
- **States**: Read-only, focused, disabled
- **Messages**: Success/error feedback

---

## Deployment Steps

### Phase 1: Deploy Code Changes

```bash
# Step 1: Deploy modified files to staging
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/inc/hive-mistress-state.php \
  /Users/ja/unmask-child-theme/functions.php \
  /Users/ja/unmask-child-theme/page-templates/template-drone-dashboard.php \
  /Users/ja/unmask-child-theme/assets/css/pages/drone-dashboard.css \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/

# Step 2: Deploy new files
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/manual-sequence-2-verification.php \
  /Users/ja/unmask-child-theme/inc/rehearsal-coordination.php \
  /Users/ja/unmask-child-theme/inc/enqueue-rehearsal-form.php \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/

# Step 3: Deploy template and CSS
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/template-parts/forms/ \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/template-parts/forms/

SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/assets/css/components/rehearsal-form.css \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/assets/css/components/

# Step 4: Fix permissions
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging << 'EOF'
cd ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/
chmod 644 inc/hive-mistress-state.php
chmod 644 inc/rehearsal-coordination.php
chmod 644 inc/enqueue-rehearsal-form.php
chmod 644 functions.php
chmod 644 page-templates/template-drone-dashboard.php
chmod 644 assets/css/pages/drone-dashboard.css
chmod 644 manual-sequence-2-verification.php
chmod 644 template-parts/forms/rehearsal-form.php
chmod 644 assets/css/components/rehearsal-form.css
EOF
```

### Phase 2: Run Manual Sequence 2 Verification

```bash
# SSH into staging and run verification script
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging

# Once connected, run:
cd ~/www/staging4.houseofanomie.com/public_html/
wp eval-file wp-content/themes/buddyboss-theme-child/manual-sequence-2-verification.php

# Expected output:
# ✅ SUCCESS: D001 advanced to Sequence 3: PERFORMANCE PREPARATION
```

### Phase 3: Create Rehearsal Planning Page

**In WordPress Admin:**
1. Go to Pages → Add New
2. Title: "Rehearsal Planning"
3. URL slug: `rehearsal-planning`
4. Content: Add shortcode `[hive_rehearsal_plan]`
5. Template: Default Template
6. Visibility: Private (only logged-in drones can access)
7. Publish

**Or via WP-CLI:**
```bash
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging

wp post create \
  --post_type=page \
  --post_title='Rehearsal Planning' \
  --post_name='rehearsal-planning' \
  --post_content='[hive_rehearsal_plan]' \
  --post_status=publish \
  --comment_status=closed
```

### Phase 4: Test the System

**Test 1: Verify Sequence Update**
1. Log in as G (d001, user ID 15)
2. Visit `/drone-dashboard/`
3. Check "Training Guide" shows:
   - **SEQUENCE 3: PERFORMANCE PREPARATION** (after manual verification)
   - Purpose: "Prepare for Pink Panthers performance through the four ritual parts"
   - Loops: gear_acceptance, inspection_stillness, movement_precision, witnessed_objectification

**Test 2: Manually Advance to Sequence 4 (for testing)**
```bash
# SSH into staging
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging

# Advance G to Sequence 4
cd ~/www/staging4.houseofanomie.com/public_html/
wp shell

# In WP-CLI shell:
>>> $state = hm_get_state(15);
>>> $state['current_sequence'] = 4;
>>> update_user_meta(15, 'hm_drone_state', $state);
>>> exit
```

**Test 3: Rehearsal Form (Drone View)**
1. Log in as G (d001)
2. Visit `/drone-dashboard/`
3. Should see "REHEARSAL PLANNING" section with orange accent
4. Fill out form:
   - Date: 2026-03-01
   - Time: 14:00
   - Location: Handler's studio
   - Check all 4 performance parts
   - Add gear notes
5. Click "Submit Rehearsal Plan"
6. Should see: "Rehearsal plan submitted"

**Test 4: Rehearsal Form (Handler View)**
1. Log in as drone 22 (user ID 1)
2. Visit `/rehearsal-planning/` or `/drone-dashboard/`
3. Should see G's submitted plan (read-only)
4. Check "HANDLER CONFIRMS" checkbox
5. Click "Confirm Rehearsal Plan"
6. Should see:
   - "Rehearsal plan confirmed by handler"
   - "SEQUENCE 4 COMPLETE: Advanced to Sequence 5"
7. Page reloads, G now in Sequence 5

**Test 5: Verify Sequence Advancement**
```bash
wp shell

# Check G's state
>>> $state = hm_get_state(15);
>>> echo "Sequence: " . $state['current_sequence'];
>>> echo "\nLoops: " . implode(', ', $state['integration']['loops_installed']);

# Expected:
# Sequence: 5
# Loops: ... rehearsal_commitment, material_coordination, handler_alignment
```

---

## Verification Checklist

- [ ] Code deployed to staging
- [ ] Permissions set (644 for all files)
- [ ] Manual Sequence 2 verification script run
- [ ] G advanced to Sequence 3
- [ ] Rehearsal planning page created
- [ ] Sequence 4 shows on Training Guide correctly
- [ ] Rehearsal form appears on dashboard in Sequence 4
- [ ] Orange accent color displays correctly
- [ ] Drone can submit rehearsal plan
- [ ] Handler can confirm rehearsal plan
- [ ] Sequence advances to 5 on confirmation
- [ ] All 3 Sequence 4 loops install correctly

---

## Rollback Plan

If issues occur, revert these files:

```bash
# SSH into staging
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging

cd ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/

# Restore from git (if tracked)
git checkout inc/hive-mistress-state.php
git checkout functions.php
git checkout page-templates/template-drone-dashboard.php
git checkout assets/css/pages/drone-dashboard.css

# Remove new files
rm manual-sequence-2-verification.php
rm inc/rehearsal-coordination.php
rm inc/enqueue-rehearsal-form.php
rm template-parts/forms/rehearsal-form.php
rm assets/css/components/rehearsal-form.css
```

---

## Future Enhancements

**Optional additions (not implemented yet):**
1. **Email notifications** - Notify handler when drone submits plan
2. **Calendar integration** - Sync rehearsal date to Google Calendar
3. **Photo upload** - Gear verification via image upload
4. **Post-rehearsal feedback** - Form to log rehearsal results
5. **Automated Sequence 2 verification** - Update measurement-algorithms.php to auto-advance

---

## Success Criteria

✅ This deployment succeeds when:

1. G can be manually advanced past Sequence 2
2. Sequence 4 definition shows "Material World Preparation"
3. Rehearsal planning form works on drone dashboard
4. Handler can confirm rehearsal plan
5. System auto-advances to Sequence 5 on confirmation
6. All data persists correctly in user meta
7. Form only shows in Sequence 4
8. CSS styling matches dashboard aesthetic

---

## Support

**If issues arise:**
- Check error logs: `wp-content/debug.log`
- Verify functions loaded: `function_exists('hm_rehearsal_form_shortcode')`
- Check user meta: `get_user_meta(15, 'hm_rehearsal_plan', true)`
- Check drone state: `get_user_meta(15, 'hm_drone_state', true)`

**Contact:**
- System: Hive Mistress AI Conditioning System
- Developer: Claude Code
- Deployment Date: 2026-02-06
