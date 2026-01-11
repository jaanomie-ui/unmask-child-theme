# UNMASK PAGE & FEATURE TO PERSONA MAPPING
Version 1.0 — January 2026

---

## OVERVIEW

This document maps every UNMASK page and feature to the persona(s) most likely
to use it, along with the user journey stage where it appears. Use this to:
- Prioritize design and development work
- Write targeted copy for each page
- Understand conversion paths
- Identify gaps in the user journey

### PERSONA KEY

| Code | Persona | Age | Focus |
|------|---------|-----|-------|
| [CU] | The Curious | 21-26 | identity exploration |
| [MU] | The Muse | 21-26 | wants documentation |
| [PE] | The Performer | 27-32 | needs stage/audience |
| [ES] | The Escapist | 33-40 | kink community, disposable income |
| [PH] | The Photographer | 27-32 | needs subjects/studio |
| [SB] | The Scene Builder | 33-40 | event infrastructure |

### JOURNEY STAGE KEY

| Stage | Name | Description |
|-------|------|-------------|
| [1] | Discovery | Stranger |
| [2] | Curiosity | Visitor (not logged in) |
| [3] | Entry | V-XXX (new account) |
| [4] | Activation | Active Visitor |
| [5] | Conversion | Factory User |
| [6] | Membership | Drone D-XXX |
| [7] | Advocacy | Embedded Drone |

---

## PUBLIC PAGES (No Login Required)

### HOMEPAGE

| Attribute | Value |
|-----------|-------|
| URL | / |
| Personas | ALL |
| Journey Stage | [1] Discovery, [2] Curiosity |
| Purpose | First impression. Intrigue without overwhelming. |
| Copy Focus | Show the work. Minimal explanation. Let photography speak. |
| Conversion Goal | Get them to click a profile or browse the archive. |

**Key Elements:**
- Striking imagery (speaks to all personas)
- Clear navigation to Archive, Factory, Pink Panthers
- No lore-heavy language at this stage

---

### THE ARCHIVE

| Attribute | Value |
|-----------|-------|
| URL | /archive |
| Personas | [CU] [MU] [ES] [PH] |
| Journey Stage | [1] Discovery, [2] Curiosity |
| Purpose | Browse documented subjects. Social proof. |
| Copy Focus | "the archive — proof that people like you exist" |
| Conversion Goal | Click into a single record. |

**Key Elements:**
- Record cards with compelling thumbnails
- Filter by type (dossier, event coverage)
- No login wall to browse

---

### SINGLE RECORD

| Attribute | Value |
|-----------|-------|
| URL | /record/{slug} |
| Personas | [CU] [MU] [ES] [PH] |
| Journey Stage | [2] Curiosity |
| Purpose | Deep documentation. This is the product. |
| Copy Focus | Dense, authoritative. "This person matters." |

**Key Elements:**
- Full photography
- Interview/profile content
- Related records
- "Want to be documented?" CTA for [MU]

**Conversion Goal:**
- [MU]: "I want one of these" → ISO Board / Submit
- [PH]: "I want to shoot like this" → Factory / Submit

---

### THE FACTORY

| Attribute | Value |
|-----------|-------|
| URL | /the-factory |
| Personas | [PH] [MU] [SB] [PE] |
| Journey Stage | [2] Curiosity, [4] Activation, [5] Conversion |
| Purpose | Studio space information and booking funnel. |
| Copy Focus | Straightforward. "$75/hour. yours to use." |
| Conversion Goal | First booking. |

**Key Elements:**
- Clear pricing (Visitor: $75/hr, Drone: $60/hr)
- Photo gallery of the space
- Equipment list
- Location/accessibility info
- [book the Factory] CTA

---

### FACTORY BOOKING

| Attribute | Value |
|-----------|-------|
| URL | /the-factory/book |
| Personas | [PH] [MU] [SB] |
| Journey Stage | [4] Activation, [5] Conversion |
| Purpose | Complete the booking. |
| Copy Focus | Functional. Clear calendar, time slots, pricing. |
| Conversion Goal | Completed booking → Factory User. |

**Key Elements:**
- Calendar view
- Time slot selection
- Drone discount visible ("Drones save 20%")
- Payment processing

---

### PINK PANTHERS

| Attribute | Value |
|-----------|-------|
| URL | /pink-panthers |
| Personas | [PE] [ES] [CU] |
| Journey Stage | [1] Discovery, [2] Curiosity |
| Purpose | Event information and ticket sales. |
| Copy Focus | More erotic. Seductive. "a night. you will be documented." |

**Key Elements:**
- Next event date/details
- Past event photography
- Performer lineup
- Ticket purchase
- "Want to perform?" CTA for [PE]

**Conversion Goal:**
- [ES]: Buy ticket
- [PE]: Apply to perform
- [CU]: Get curious, come back

---

### SINGLE EVENT

| Attribute | Value |
|-----------|-------|
| URL | /event/{slug} |
| Personas | [PE] [ES] |
| Journey Stage | [2] Curiosity, [6] Membership |
| Purpose | Event documentation and gallery. |
| Copy Focus | Raw documentation. Names optional, consent-based. |
| Conversion Goal | [ES] → "I want to be at the next one" → Ticket/Membership |

**Key Elements:**
- Photo gallery
- Performer credits
- Related events

---

### ISO BOARD

| Attribute | Value |
|-----------|-------|
| URL | /iso-board |
| Personas | [PH] [MU] [PE] [SB] |
| Journey Stage | [2] Curiosity, [3] Entry, [4] Activation |
| Purpose | Collaboration marketplace. Connection engine. |
| Copy Focus | Functional listings. No fluff. |

**Key Elements:**
- ISO listings with type badges (photographer, model, collaborator)
- Factory availability indicator
- [respond] buttons (login required)
- [post to ISO board] CTA

**Conversion Goal:**
- [2] Stranger: "People are doing things here" → Create account
- [4] Active: Post or respond to ISO → Factory booking

---

### SINGLE ISO

| Attribute | Value |
|-----------|-------|
| URL | /iso/{slug} |
| Personas | [PH] [MU] [PE] |
| Journey Stage | [3] Entry, [4] Activation |
| Purpose | View full listing details, respond. |
| Copy Focus | Functional. Author info, requirements, availability. |
| Conversion Goal | Response → Connection → Factory booking |

**Key Elements:**
- Full description
- Author profile link
- [respond] button
- Factory nudge if relevant

---

### THE WEB

| Attribute | Value |
|-----------|-------|
| URL | /the-web |
| Personas | [CU] [ES] |
| Journey Stage | [2] Curiosity, [6] Membership |
| Purpose | Interactive constellation map. Discovery tool. |
| Copy Focus | Minimal. Let the visual speak. |
| Conversion Goal | Deeper engagement, time on site, discovery. |

**Key Elements:**
- Interactive nodes (records, concepts, places)
- Click to explore connections

---

### ABOUT

| Attribute | Value |
|-----------|-------|
| URL | /about |
| Personas | ALL |
| Journey Stage | [2] Curiosity |
| Purpose | Mission, context, legitimacy. |
| Copy Focus | "Documentation over news. We keep the record." |
| Conversion Goal | Trust building → Return to Archive or Factory. |

**Key Elements:**
- Origin story (brief)
- Mission statement
- No "we" language

---

### MEMBERSHIP

| Attribute | Value |
|-----------|-------|
| URL | /membership |
| Personas | [ES] [PH] |
| Journey Stage | [4] Activation, [5] Conversion |
| Purpose | Explain Drone benefits, convert. |
| Copy Focus | Value proposition, not features. "Part of the record." |
| Conversion Goal | Free user → Drone. |

**Key Elements:**
- Pricing ($15/month)
- Benefits list (Factory discount, UNMASK uncut, Pink Panthers discount)
- [join] CTA

---

## MEMBER PAGES (Login Required)

### MEMBERS DIRECTORY

| Attribute | Value |
|-----------|-------|
| URL | /members |
| Personas | [PH] [MU] [PE] |
| Journey Stage | [3] Entry, [4] Activation |
| Purpose | Find collaborators, browse community. |
| Copy Focus | Functional directory. |
| Conversion Goal | Find someone → View profile → Connect via ISO/message. |

**Key Elements:**
- Member cards with avatars
- Availability badges
- Search/filter

---

### MEMBER PROFILE (DOSSIER)

| Attribute | Value |
|-----------|-------|
| URL | /members/{username} |
| Personas | [PH] [MU] [PE] |
| Journey Stage | [3] Entry, [4] Activation |
| Purpose | View individual member, their ISOs, availability. |
| Copy Focus | Per user. System displays their info. |

**Key Elements:**
- Avatar, bio, designation (V-XXX or D-XXX)
- Availability badges ("available to photograph", etc.)
- Active ISOs
- Drone badge if applicable

**Conversion Goal:**
- [PH]: Find subject → Send message or respond to their ISO
- [MU]: See what profiles look like → Complete own dossier

---

### MY UNMASK (DASHBOARD)

| Attribute | Value |
|-----------|-------|
| URL | /my-unmask |
| Personas | ALL logged-in users |
| Journey Stage | [3] Entry, [4] Activation, [6] Membership |
| Purpose | Personal hub. Activity, stats, quick actions. |
| Copy Focus | Functional. "Status: Active. ISOs: 2." |
| Conversion Goal | Engagement → ISO Board → Factory → Membership |

**Key Elements:**
- Designation display
- Active ISOs
- Factory bookings
- Unread messages
- Drone status/benefits if applicable

---

### POST ISO

| Attribute | Value |
|-----------|-------|
| URL | /post-iso |
| Personas | [PH] [MU] [PE] [SB] |
| Journey Stage | [4] Activation |
| Purpose | Create new ISO listing. |
| Copy Focus | Functional form. Clear fields. |
| Conversion Goal | Posted ISO → Responses → Connection → Factory booking |

**Key Elements:**
- Title, type, description fields
- Factory preference toggle
- Expiration setting
- Preview before post

---

### MESSAGES

| Attribute | Value |
|-----------|-------|
| URL | /members/{username}/messages |
| Personas | ALL logged-in users |
| Journey Stage | [4] Activation, [5] Conversion |
| Purpose | Private communication. |
| Copy Focus | None (user content). |
| Conversion Goal | Coordinate → Book Factory → Meet in person |

**Key Elements:**
- Conversation threads
- Compose new message
- ISO response notifications

---

### ACCOUNT (MEMBERPRESS)

| Attribute | Value |
|-----------|-------|
| URL | /account |
| Personas | [ES] [PH] (Drones) |
| Journey Stage | [6] Membership |
| Purpose | Manage subscription, payments. |
| Copy Focus | Functional account management. |
| Conversion Goal | Retention. |

**Key Elements:**
- Subscription status
- Payment history
- Cancel/upgrade options

---

## DRONE-ONLY PAGES

### UNMASK UNCUT ARCHIVE

| Attribute | Value |
|-----------|-------|
| URL | /uncut (or gated section) |
| Personas | [ES] |
| Journey Stage | [6] Membership |
| Purpose | Access monthly zine back issues. |
| Copy Focus | "Raw. Unprocessed. The thing itself." |
| Conversion Goal | Retention, perceived value. |

**Key Elements:**
- Issue covers
- Download links
- Latest issue highlight

---

### HIVE MISTRESS

| Attribute | Value |
|-----------|-------|
| URL | /hive-mistress |
| Personas | [ES] [CU] (Drones) |
| Journey Stage | [6] Membership |
| Purpose | AI chat experience (lore-based). |
| Copy Focus | Lore-heavy. Anomie's world. |
| Conversion Goal | Engagement, differentiation, retention. |

**Key Elements:**
- Chat interface
- Drone protocol language

---

## JOURNEY STAGE → PAGE MAPPING

### STAGE 1: DISCOVERY (Stranger)

| Attribute | Value |
|-----------|-------|
| Primary Pages | Homepage, Archive, Pink Panthers |
| Goal | Do not leave immediately. Click something. |
| Persona Focus | All (first impression matters universally) |

---

### STAGE 2: CURIOSITY (Visitor, not logged in)

| Attribute | Value |
|-----------|-------|
| Primary Pages | Single Record, ISO Board, The Factory, About |
| Goal | Want more. Hit the login wall. Create account. |

**Persona Focus:**
- [CU]: Records, profiles
- [MU]: Records, ISO Board
- [PH]: Factory, ISO Board
- [PE]: Pink Panthers, ISO Board
- [ES]: Records, Pink Panthers

---

### STAGE 3: ENTRY (V-XXX, new account)

| Attribute | Value |
|-----------|-------|
| Primary Pages | Dashboard, Dossier Editor, Members Directory |
| Goal | Feel welcomed. Complete dossier. First action. |

**Persona Focus:**
- [CU]: Complete dossier, browse
- [MU]: Complete dossier, post ISO
- [PH]: Complete dossier, browse ISO Board

---

### STAGE 4: ACTIVATION (Active Visitor)

| Attribute | Value |
|-----------|-------|
| Primary Pages | ISO Board, Post ISO, Messages, Factory |
| Goal | Use the system. Connect with someone. Factory appears everywhere. |

**Persona Focus:**
- [PH]: Post ISO, respond to ISOs, book Factory
- [MU]: Respond to ISOs, connect with photographers
- [PE]: Find collaborators, book Factory for rehearsal

---

### STAGE 5: CONVERSION (Factory User)

| Attribute | Value |
|-----------|-------|
| Primary Pages | Factory Booking, Factory (physical space), Membership |
| Goal | Book Factory. Experience is good. Membership makes sense. |

**Persona Focus:**
- [PH]: Repeat bookings, sees Drone discount
- [MU]: Gets documented, sees value
- [SB]: Books for event, considers partnership

---

### STAGE 6: MEMBERSHIP (Drone D-XXX)

| Attribute | Value |
|-----------|-------|
| Primary Pages | Dashboard (Drone view), UNMASK uncut, Hive Mistress |
| Goal | Deliver value. Retention. They bring others. |

**Persona Focus:**
- [ES]: Content, community, belonging
- [PH]: Discount pays for itself

---

### STAGE 7: ADVOCACY (Embedded Drone)

| Attribute | Value |
|-----------|-------|
| Primary Pages | All (they are the content now) |
| Goal | They refer others. They get documented. Loop completes. |
| Persona Focus | All who reach this stage |

---

## CONVERSION FUNNELS BY PERSONA

### THE CURIOUS [CU]

```
Homepage → Archive → Single Record → Create Account →
Complete Dossier → Browse → (slow burn) → Eventually engage
```

### THE MUSE [MU]

```
Single Record ("I want one") → ISO Board → Create Account →
Post/Respond to ISO → Connect with Photographer → Book Factory →
Get Documented → Submit to Magazine
```

### THE PERFORMER [PE]

```
Pink Panthers → Event Photos → "I want to perform" →
Create Account → Apply to Perform → Get Booked →
Get Documented → Become Drone for discounts
```

### THE ESCAPIST [ES]

```
Content Discovery → Value the Mission → Browse Archive →
Membership Page → Join as Drone → Receive UNMASK uncut →
Attend Pink Panthers → Book Factory for personal use
```

### THE PHOTOGRAPHER [PH]

```
"Chicago studio space" Search → Factory Page → Book First Session →
Discover ISO Board → Find Subjects → Repeat Bookings →
Become Drone (discount math) → Submit to Magazine
```

### THE SCENE BUILDER [SB]

```
"Need event space" → Factory Page → Book for Event →
Event Succeeds → Repeat → Partnership Conversation
```

---

## COPY PRIORITY BY PAGE

### HIGH PRIORITY (Conversion Critical)

- Homepage (first impression)
- The Factory (primary revenue)
- Single Record (product showcase)
- ISO Board (activation engine)
- Membership (conversion page)

### MEDIUM PRIORITY (Journey Support)

- Archive (browse experience)
- Pink Panthers (secondary revenue)
- Dashboard (retention)
- Post ISO (activation)
- Dossier Editor (profile completion)

### LOWER PRIORITY (Can Use Defaults)

- About (trust, but not conversion)
- Messages (functional)
- Account (functional)
- The Web (discovery, not conversion)
- Hive Mistress (engagement, Drone-only)

---

*UNMASK Page & Feature to Persona Mapping v1.0*
*Last updated: January 2026*
