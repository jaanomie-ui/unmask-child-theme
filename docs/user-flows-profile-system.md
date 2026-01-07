# UNMASK Profile System - User Flows & Visibility Matrix

> Documentation for profile completion workflows, visibility states, and social interaction patterns.

---

## Terminology

| Term | Definition |
|------|------------|
| **User** | A logged-out visitor (stranger, no account or not signed in) |
| **Visitor** | A logged-in user (has account, signed in) |
| **Interlinked** | Two visitors who are connected (BuddyBoss friends relationship) |
| **Connection** | Visibility level, not the relationship itself |

### Visibility Levels

| Level | Who Can See |
|-------|-------------|
| **User** | Everyone including logged-out strangers (public) |
| **Visitor** | Only logged-in members |
| **Interlinked** | Only connected visitors (friends) |

---

## Profile Field Groups & Completion States

| Group | Fields | Required | Completion Trigger |
|-------|--------|----------|-------------------|
| **Visitor Profile** | Scene Name, Pronouns, City, Bio | Scene Name at signup | Basic identity |
| **Creative Practice** | Practices, Portfolio, 3 availability checkboxes | None | Professional visibility |
| **Kink Profile** | D/s Orientation, Interests | None | Intimate disclosure |

---

## 1. Profile Completion Workflows

### Current Touchpoints Leading to Profile Completion

```
┌─────────────────────────────────────────────────────────────────────┐
│                        REGISTRATION FLOW                             │
├─────────────────────────────────────────────────────────────────────┤
│  /register/visitor/                                                  │
│  ├── Scene Name (required)                                          │
│  ├── Pronouns (optional dropdown)                                   │
│  └── Function Selection: Subject / Operator / Performer             │
│                              ↓                                       │
│  /welcome/                                                           │
│  ├── Shows: Designation (V-XXX), Selected Functions                 │
│  └── 3 Cards → Archive | Your Dossier | The Factory                 │
└─────────────────────────────────────────────────────────────────────┘
                               ↓
┌─────────────────────────────────────────────────────────────────────┐
│                     POST-REGISTRATION STATE                          │
├─────────────────────────────────────────────────────────────────────┤
│  Profile has:                                                        │
│  ├── ✓ Scene Name                                                   │
│  ├── ✓ Functions (practices)                                        │
│  ├── ? Pronouns (if selected)                                       │
│  └── ✗ City, Bio, Portfolio, Status, Kink (all empty)              │
└─────────────────────────────────────────────────────────────────────┘
```

### Current Completion Triggers (Passive)

| Location | Trigger | Action |
|----------|---------|--------|
| Welcome Page | "Your Dossier" card | Links to `/members/[username]/profile/edit/` |
| Own Profile Header | `[edit dossier]` link | Links to profile edit |
| Creative Practice Section | "add some" link (empty state) | Links to `/profile/edit/group/2/` |
| Kink Section | "create" button (empty state) | Links to `/profile/edit/group/3/` |

---

## 2. Visibility Matrix

### Profile Element Visibility by Viewer State

| Element | Self | Interlinked Visitor | Visitor | User |
|---------|------|---------------------|---------|------|
| **Avatar** | ✓ + [change] | ✓ | ✓ | ✓ |
| **Scene Name** | ✓ | ✓ | ✓ | ✓ |
| **Designation** | ✓ | ✓ | ✓ | ✓ |
| **Location** | ✓ + toggle | ✓ | configurable | configurable |
| **Functions** | ✓ + toggle | ✓ | configurable | configurable |
| **Status Indicators** | ✓ + toggle | ✓ | configurable | configurable |
| **Bio** | ✓ + toggle | ✓ | configurable | configurable |
| **Active ISOs** | ✓ + [expire] | ✓ + [respond] | ✓ + [respond] | ✓ (view only) |
| **Creative Practice** | ✓ + toggle | ✓ | configurable | configurable |
| **Kink Profile** | ✓ + toggle | ✓ | 🔒 default hidden | 🔒 always hidden |
| **Credits** | ✓ | ✓ | ✓ | ✓ |
| **MY UNMASK** | ✓ | ✗ | ✗ | ✗ |

### Per-Section Visibility Toggle

Each section can have a visibility toggle with three states:
- **User** (public) - Everyone can see, including logged-out strangers
- **Visitor** - Only logged-in members can see
- **Interlinked** - Only connected visitors can see

### Actions Available by Viewer State

| Action | Self | Interlinked | Visitor | User |
|--------|------|-------------|---------|------|
| **[edit dossier]** | ✓ | ✗ | ✗ | ✗ |
| **[message]** | ✗ | ✓ | ✓ | ✗ |
| **[interlink]** | ✗ | ✗ (already) | ✓ | ✗ |
| **[respond to ISO]** | ✗ | ✓ | ✓ | ✗ |
| **[expire ISO]** | ✓ | ✗ | ✗ | ✗ |
| **[change avatar]** | ✓ | ✗ | ✗ | ✗ |

---

## 3. User Interaction Touchpoints

### Stranger → Connection Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                     STRANGER → CONNECTION FLOW                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  DISCOVERY                                                           │
│  ├── Member Directory (BuddyBoss native)                            │
│  ├── ISO Board (/iso-board/)                                        │
│  ├── Magazine Credits (linked from records)                         │
│  └── Activity Stream (site-wide)                                    │
│                              ↓                                       │
│  PROFILE VIEW                                                        │
│  ├── Read dossier (public info)                                     │
│  ├── See active ISOs                                                │
│  └── View creative portfolio                                        │
│                              ↓                                       │
│  INITIAL CONTACT                                                     │
│  ├── [message] → Private message                                    │
│  ├── [respond] → Pre-filled ISO response                            │
│  └── [add connection] → Friend request                              │
│                              ↓                                       │
│  CONNECTION                                                          │
│  ├── Mutual friend acceptance                                       │
│  ├── Ongoing messaging                                              │
│  └── Kink profile visibility unlocked                               │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### Interest → Match → Connection Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│  INTEREST → INTRODUCTION → CONNECTION                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  1. ANONYMOUS INTEREST                                               │
│     └── "X people viewed your profile this week"                    │
│                                                                      │
│  2. EXPRESSED INTEREST (new action)                                  │
│     └── [interested] → Logs interest, notifies user                 │
│     └── "Y showed interest in your profile"                         │
│                                                                      │
│  3. MUTUAL INTEREST                                                  │
│     └── Both parties interested → Unlock messaging                  │
│     └── "You matched with Z"                                        │
│                                                                      │
│  4. CONNECTION                                                       │
│     └── After conversation → [add connection]                       │
│     └── Unlocks kink profile visibility                             │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 4. Match Indicators

### ISO Board Match Indicators

```
┌─────────────────────────────────────────────────────────────────────┐
│  ISO BOARD ENHANCEMENTS                                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  SEEKING photographer for editorial shoot                           │
│  ├── poster: scenename (V-047)                                      │
│  ├── location: Chicago                                              │
│  ├── factory: preferred ◆                                           │
│  └── [view dossier] [respond]                                       │
│                                                                      │
│  Match indicators:                                                   │
│  ├── "You're both in Chicago"                                       │
│  ├── "You practice: photographer"                                   │
│  └── "3 mutual connections"                                         │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### Profile Match Context

```php
// Connection Context Component
<div class="connection-context">
    <?php if ($mutual_connections > 0) : ?>
        <span class="mutual-badge"><?php echo $mutual_connections; ?> mutual</span>
    <?php endif; ?>

    <?php if ($shared_functions) : ?>
        <span class="shared-functions">also practices: <?php echo implode(', ', $shared_functions); ?></span>
    <?php endif; ?>

    <?php if ($same_city) : ?>
        <span class="location-match">◆ local</span>
    <?php endif; ?>
</div>
```

---

## 5. Privacy Controls

### Kink Profile Privacy

The kink profile is:
- **Optional** - Users choose whether to create one
- **Connection-gated** - Only visible to connected users
- **Deletable** - Users can remove at any time
- **Reminder displayed**: "Your kink profile is private. Only your connections can see it. You never have to share this with anyone."

### Per-Field Visibility (Future)

| Field | Default | Options |
|-------|---------|---------|
| Location | Members | Public / Members / Connections / Private |
| Bio | Members | Public / Members / Connections / Private |
| Functions | Public | Public / Members / Connections / Private |
| Portfolio | Public | Public / Members / Connections / Private |
| Status | Members | Public / Members / Connections / Private |
| Kink Profile | Connections | Connections / Private |

---

## 6. Implementation Status

| Feature | Status | Priority |
|---------|--------|----------|
| Profile completeness indicator | Planned | 1 |
| Connection-gated kink profile | Planned | 2 |
| Mutual connection count on profiles | Planned | 3 |
| Match indicators on ISO board | Planned | 4 |
| Interest/match system | Planned | 5 |
| Per-field visibility controls | Planned | 6 |

---

## 7. xProfile Field Reference

**Visitor Profile Group (ID 1)**
- Scene Name: Field ID 3
- Pronouns: Field ID 85
- City: Field ID 87
- Bio: Field ID 88

**Creative Practice Group (ID 2)**
- What do you practice?: Field ID 21
- Portfolio: Field ID 103
- Available to be photographed: Field ID 104
- Available to photograph: Field ID 108
- Open to bookings: Field ID 111

**Kink Profile Group (ID 3)**
- D/s Orientation: Field ID 57
- Kink interests: Field ID 71

---

*Last updated: 2025-01-05*
