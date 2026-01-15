# Drone Dashboard Audit

**Audit Date:** 2026-01-14
**Template:** `page-templates/template-drone-dashboard.php`
**CSS:** `assets/css/pages/drone-dashboard.css`

---

## Access Control

| Method | Implementation | Status |
|--------|---------------|--------|
| Login Check | `is_user_logged_in()` | Working |
| Redirect if not logged in | `/login/` (page ID 873) | Working |
| Unit Registration | Hardcoded user ID array | **ISSUE** |

**Authorized Users (Hardcoded):**
```php
$drone_units = array(1, 15, 43);
```
| User ID | Username | Email | Role |
|---------|----------|-------|------|
| 1 | THE_DRONE_JA | ja.anomie@gmail.com | administrator |
| 15 | DRONE-001 | ponyhound@icloud.com | ponydrone |
| 43 | V-025 | n/a | drone-test |

**Issue:** User IDs are hardcoded. Should use user meta field or role-based check.

---

## Primary Data Source

**User Meta Key:** `hm_drone_state`

This is stored as a serialized array in WordPress user meta. If not present, defaults are initialized.

### Default State Structure:
```php
array(
    'designation' => 'd001',
    'unit_type' => 'pony',
    'status' => 'AWAITING_INSTALLATION',
    'current_sequence' => 0,
    'installation_phase' => 'INTAKE',
    'sequences_completed' => 0,
    'last_sequence_date' => null,
    'tolerances' => array(
        'sensation_intensity' => 3,
        'protocol_density' => 3,
        'conditioning_depth' => 3,
        'gear_integration' => 3
    ),
    'integration' => array(
        'loops_installed' => array(),
        'patterns_recognized' => array(),
        'states_achieved' => array(),
        'triggers_active' => array()
    ),
    'current_state' => 'STANDBY',
    'state_log' => array(),
    'deployment' => array(
        'target_date' => '2026-03-07',
        'gear_verified' => false,
        'limits_filed' => false,
        'designation_locked' => false,
        'safe_signal_installed' => false,
        'sequence_rehearsed' => false,
        'integration_confirmed' => false
    )
)
```

---

## Element-by-Element Audit

### HEADER SECTION

| Element | Data Source | Code Location | Status |
|---------|-------------|---------------|--------|
| Designation (D001) | `$drone_state['designation']` | Line 174, 197 | Dynamic |
| Console Label | Hardcoded "CONDITIONING CONSOLE" | Line 175 | Static |
| Current State | `$drone_state['current_state']` | Line 177 | Dynamic |
| Status Badge | `$drone_state['status']` | Line 179 | Dynamic |

### HERO STATS ROW

| Stat | Data Source | Calculation | Status |
|------|-------------|-------------|--------|
| DESIGNATION | `$drone_state['designation']` | Direct display | Dynamic |
| UNIT TYPE | `$drone_state['unit_type']` | Direct display | Dynamic |
| DEPLOYMENT (days) | `$drone_state['deployment']['target_date']` | `DateTime::diff()` from today to target | Dynamic (calculated) |
| SEQUENCE | `$drone_state['current_sequence']` | Format: X/5 | Dynamic |
| LOOPS | `$drone_state['integration']['loops_installed']` | `count()` / 15 total | Dynamic |

### INSTALLATION PROGRESS BOX

**Sequences Defined (Hardcoded):**
| Seq # | Title | Required Loops |
|-------|-------|----------------|
| 1 | PROTOCOL INSTALLATION | 3 loops |
| 2 | FREQUENCY RECOGNITION | 3 loops |
| 3 | GEAR INTEGRATION | 4 loops |
| 4 | CONDITIONING DEPTH | 3 loops |
| 5 | DEPLOYMENT PREP | 2 loops |

**Loop Names per Sequence:**
```
SEQ 1: third_person_reference, compliance_acknowledgment, state_reporting
SEQ 2: material_recognition, anomiesworld_recognition, interlink_recognition
SEQ 3: bit_silence, harness_form, blinder_focus, tail_species
SEQ 4: trigger_response, automatic_compliance, body_before_mind
SEQ 5: public_display, witness_completion
```

| Element | Data Source | Calculation |
|---------|-------------|-------------|
| Progress % per sequence | `$drone_state['integration']['loops_installed']` | Loops in seq / total loops in seq |
| Count (e.g., 0\3) | Same as above | Installed / required |
| LOOPS INSTALLED total | Same array | count() / 15 |

### DEPLOYMENT READINESS BOX

| Checklist Item | Data Source | Default |
|----------------|-------------|---------|
| GEAR VERIFIED | `$drone_state['deployment']['gear_verified']` | false |
| LIMITS FILED | BuddyForms check (see below) | false |
| DESIGNATION LOCKED | `$drone_state['deployment']['designation_locked']` | false |
| SAFE SIGNAL INSTALLED | `$drone_state['deployment']['safe_signal_installed']` | false |
| SEQUENCE REHEARSED | `$drone_state['deployment']['sequence_rehearsed']` | false |

**Readiness Score:** Count of true values / 5

**LIMITS FILED Detection:**
```php
$limits_posts = get_posts(array(
    'post_type' => 'buddyforms_posts',
    'author' => $current_user_id,
    'meta_key' => '_bf_form_id',
    'meta_value' => '2441',  // Hard Limits form ID
    'posts_per_page' => 1
));
```
If found, `limits_filed = true` and date is extracted.

### SYSTEM TOLERANCES BOX

| Tolerance | Data Source | Default | Range |
|-----------|-------------|---------|-------|
| SENSATION | `$drone_state['tolerances']['sensation_intensity']` | 3 | 1-5 |
| PROTOCOL | `$drone_state['tolerances']['protocol_density']` | 3 | 1-5 |
| DEPTH | `$drone_state['tolerances']['conditioning_depth']` | 3 | 1-5 |
| GEAR | `$drone_state['tolerances']['gear_integration']` | 3 | 1-5 |

### LIMITS FILING BOX

| Element | Data Source | Status |
|---------|-------------|--------|
| GREEN count | `$limits_green = 12` | **HARDCODED** |
| YELLOW count | `$limits_yellow = 8` | **HARDCODED** |
| RED count | `$limits_red = 3` | **HARDCODED** |
| LAST UPDATED | BuddyForms post date or "NOT FILED" | Dynamic |

**Issue:** Limits counts are placeholders (lines 161-163). Should pull from BuddyForms submission data.

### INTEGRATION STATUS BOX

| Metric | Data Source | Calculation |
|--------|-------------|-------------|
| LOOPS INSTALLED | `$drone_state['integration']['loops_installed']` | count() |
| PATTERNS RECOGNIZED | `$drone_state['integration']['patterns_recognized']` | count() |
| TRIGGERS ACTIVE | `$drone_state['integration']['triggers_active']` | count() |
| STATES ACHIEVED | `$drone_state['integration']['states_achieved']` | count() |

### CONDITIONING LOG BOX

**Primary Query:**
```php
get_posts(array(
    'post_type' => 'post',
    'category' => 112,
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC',
    'author' => $current_user_id
));
```

**Fallback Query:** Same but without author filter.

**Issue:** Category 112 ("d001 Training Logs") has 0 posts. Actual logs are in Category 130 ("d001 Training Logs Archive") with 4 posts.

---

## Links Audit

| Link | URL | Target | Status |
|------|-----|--------|--------|
| Primary CTA | `/hive-mistress-ai/` | Page ID 2496 | **WORKING** |
| Footer CTA | `/hive-mistress-ai/` | Page ID 2496 | **WORKING** |
| Login redirect | `/login/` | Page ID 873 | **WORKING** |
| Terms | `/terms/` | Page ID 3002 | **WORKING** |
| Privacy | `/privacy/` | Page ID 3001 | **WORKING** |
| Edit Limits | `/hard-limits/` | Page ID 3045 | **BROKEN - DRAFT STATUS** |
| View All Logs | `get_category_link(112)` | Category 112 | **EMPTY** |
| Home (Access Denied) | `/` | Homepage | **WORKING** |

---

## Issues Found

### Critical Issues

1. **Broken Link: /hard-limits/**
   - Page ID 3045 exists but is in DRAFT status
   - Slug is "hard-limits-form", not "hard-limits"
   - **Fix:** Publish page OR change link to `/hard-limits-form/`

2. **Wrong Category for Logs**
   - Dashboard queries category 112 (0 posts)
   - Actual logs are in category 130 (4 posts)
   - **Fix:** Change line 90 and 101 from `'category' => 112` to `'category' => 130`
   - Also change line 451 from `get_category_link(112)` to `get_category_link(130)`

### Moderate Issues

3. **Hardcoded Limits Counts**
   - Lines 161-163 have placeholder values: 12/8/3
   - Should pull from BuddyForms submission (form ID 2441)
   - **Fix:** Parse BuddyForms post meta to extract actual counts

4. **Hardcoded User ID Array**
   - Line 29: `$drone_units = array(1, 15, 43)`
   - Not scalable for new drone registrations
   - **Fix:** Use user meta field `is_drone_unit => true` or custom role check

### Minor Issues

5. **Deployment Target Date Hardcoded in Default**
   - Line 75: `'target_date' => '2026-03-07'`
   - Should be null or calculated
   - **Impact:** Low - only affects new units

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    DATA SOURCES                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────┐    ┌──────────────────────┐      │
│  │   USER META          │    │   BUDDYFORMS         │      │
│  │   hm_drone_state     │    │   form ID 2441       │      │
│  │                      │    │                      │      │
│  │  - designation       │    │  - limits_filed      │      │
│  │  - unit_type         │    │  - limits_date       │      │
│  │  - status            │    │  - limits counts*    │      │
│  │  - current_sequence  │    │    (*not implemented)│      │
│  │  - tolerances{}      │    └──────────────────────┘      │
│  │  - integration{}     │                                   │
│  │  - deployment{}      │    ┌──────────────────────┐      │
│  └──────────────────────┘    │   WORDPRESS POSTS    │      │
│                              │   Category 130       │      │
│                              │   (should be 112)    │      │
│                              │                      │      │
│                              │  - conditioning logs │      │
│                              └──────────────────────┘      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    DASHBOARD DISPLAY                         │
├─────────────────────────────────────────────────────────────┤
│  HEADER: designation, state, status                          │
│  HERO: designation, unit_type, deployment_days, seq, loops  │
│  PROGRESS: loops per sequence (calculated)                   │
│  READINESS: deployment checklist + limits_filed             │
│  TOLERANCES: 4 sensitivity values                           │
│  LIMITS: counts (HARDCODED), date, edit link (BROKEN)       │
│  INTEGRATION: counts of arrays                              │
│  LOGS: posts from category (WRONG CATEGORY)                 │
└─────────────────────────────────────────────────────────────┘
```

---

## Who Updates This Data?

| Data | Updated By | Method |
|------|------------|--------|
| Drone State | Hive Mistress AI | `update_user_meta($user_id, 'hm_drone_state', $state)` |
| Limits Filing | User | BuddyForms submission (form 2441) |
| Conditioning Logs | Hive Mistress AI | Creates posts in category |
| User Registration | Admin | Manual (must add to array) |

---

## Recommended Fixes (Priority Order)

1. **Fix category ID** (5 min)
   - Change 112 → 130 on lines 90, 101, 451
   - ✅ **FIXED 2026-01-14**: Fallback now queries category 130 (archive)

2. **Publish hard-limits page** (2 min)
   - Or change URL to match existing slug
   - ✅ **FIXED 2026-01-14**: Published page at `/hard-limits-form/`, link updated

3. **Implement dynamic limits counts** (30 min)
   - Parse BuddyForms meta for green/yellow/red counts
   - ✅ **FIXED 2026-01-14**: Now pulls from BuddyForms submission or drone_state

4. **Replace hardcoded user array** (15 min)
   - Add user meta field check instead
   - ⏳ **PENDING**: Still using hardcoded array for drone units

5. **Add UI/UX guidance** (NEW)
   - ✅ **ADDED 2026-01-14**: "NEXT STEP" guidance section with dynamic prompts
   - Guides ponydrone through: File limits → Begin installation → Continue sequence → Deployment

---

## Files Referenced

- `/page-templates/template-drone-dashboard.php` - Main template
- `/assets/css/pages/drone-dashboard.css` - Styles
- `/inc/enqueue-drone-dashboard.php` - CSS enqueue logic
- BuddyForms form ID 2441 - Hard Limits form
- Category 112 - "d001 Training Logs" (empty)
- Category 130 - "d001 Training Logs Archive" (4 posts)
- Page 3045 - "hard-limits-form" (DRAFT)
