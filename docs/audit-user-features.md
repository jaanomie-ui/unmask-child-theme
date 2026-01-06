# User Features Audit

Last updated: 2025-01-05

## Overview

Everything on the UNMASK site that a user can "own" or claim as unique to their account.

---

## Profile/Dossier

### Core Elements
| Feature | Location | Notes |
|---------|----------|-------|
| Avatar | BuddyBoss | Uploadable, shown on profile + cards |
| Cover Photo | BuddyBoss | Profile header background |
| Display Name | xProfile field | Public |
| Bio/About | xProfile field | Public |
| Designation | `unmask_get_designation()` | V-001, D-042, etc. |

### xProfile Groups
| Group | Visibility | Fields |
|-------|------------|--------|
| Base | Public | Display name, bio |
| Details | Public | Location, pronouns, links |
| Kink | Drone-only* | Roles, interests, limits |

*Visitors can see their own kink section but not others'.

### User Meta
| Key | Set When | Purpose |
|-----|----------|---------|
| `unmask_functions` | Registration | Array of creative functions |
| `unmask_account_created` | Registration | Account creation timestamp |

---

## ISO Board

### User Can:
- **Create** ISO listings (seeking/offering)
- **View** their listings on the board
- **Be contacted** via BuddyBoss messages

### User Cannot (missing features):
- Edit their own ISOs
- Delete/expire ISOs early
- See "My ISOs" dashboard
- Get notified when someone messages about their ISO

### ISO Fields Owned by User
| Field | Type | Options |
|-------|------|---------|
| `iso_type` | ACF Select | seeking, offering |
| `iso_category` | ACF Select | photographer, model, collaborator, connection |
| `iso_location` | ACF Select | chicago, remote, other |
| `iso_factory` | ACF Select | yes, preferred, open, no |
| `iso_expiration` | ACF Date | Max 30 days from creation |

---

## The Factory

### Booking
- Uses `[factory_booking]` shortcode (external plugin)
- Booking tied to user account
- $75/hr (Drones: $60/hr)

### User Should Be Able To:
- View their bookings
- Cancel bookings
- See booking history

---

## Pink Panthers

### Performer Submission
- Submit act for consideration
- Form sends email to admin
- No user dashboard for submissions

### Volunteer Submission
- Form exists but is **broken** (no handler)

---

## Magazine / Content

### User Contributions
| Content Type | Can Create? | Can Edit? | Notes |
|--------------|-------------|-----------|-------|
| ISO Listings | Yes | No | Auto-expires |
| Comments | Yes | Limited | BuddyBoss handles |
| Activity Posts | Yes | Yes | BuddyBoss activity |
| Forum Topics | If enabled | Yes | BuddyBoss forums |

---

## Social / BuddyBoss

### Connections
- Send/receive friend requests
- View connections list
- Remove connections

### Messages
- Private messaging
- Message threads
- Used for ISO contact

### Activity
- Post updates
- Like/comment on others' activity
- Activity visible on profile

### Notifications
- In-app notifications
- Email notifications (if enabled)

---

## Membership

### Member Types
| Type | Access | Price |
|------|--------|-------|
| Visitor | Limited (no kink on others, basic features) | Free |
| Drone | Full access (all content, discounts) | Paid |

### Managed By
- MemberPress plugin
- Membership ID: 2093 (Visitor)

### User Can:
- View membership status
- Upgrade (Visitor → Drone)
- View billing history (if paid)

---

## WooCommerce

### Products Available
- UNMASK Magazine (Issue 001)

### User Can:
- Purchase products
- View order history
- Download digital products

---

## Account Management

### WordPress Core
| Feature | Location |
|---------|----------|
| Email change | BuddyBoss settings |
| Password change | BuddyBoss settings |
| Account deletion | Not visible (admin only?) |

### Privacy
- Export data (GDPR)
- Delete account (GDPR)

---

## Feature Matrix by Member Type

| Feature | Visitor | Drone |
|---------|---------|-------|
| Create profile | ✓ | ✓ |
| Post ISOs | ✓ | ✓ |
| View others' kink section | ✗ | ✓ |
| Factory discount | ✗ | ✓ ($60 vs $75) |
| All magazine content | ✓ | ✓ |
| BuddyBoss messaging | ✓ | ✓ |
| Purchase products | ✓ | ✓ |

---

## Recommended New Features

### High Priority
1. **My ISOs Dashboard** — View, edit, delete own listings
2. **Fix volunteer form** — Currently broken
3. **ISO expiration notifications** — Email before listing expires

### Medium Priority
4. **Profile function editor** — Update creative functions post-registration
5. **Booking history** — View past Factory bookings
6. **Performer submission status** — See if act was accepted

### Low Priority
7. **Saved/bookmarked ISOs** — Save interesting listings
8. **ISO match notifications** — Alert when matching ISO posted
