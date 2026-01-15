# HIVE MISTRESS - CHATBOT IMPLEMENTATION

**Purpose:** Technical documentation of current AI chatbot implementation.
**Location:** WPCode Snippet ID 2495 "Hive Mistress AI"
**Status:** Live but not integrated with V2 architecture docs.

---

## OVERVIEW

The Hive Mistress chatbot is implemented as a WPCode snippet (not in theme files). It provides:
- Real-time chat with Claude AI (Sonnet 4)
- Session logging to WordPress posts
- Admin settings panel for prompt configuration
- Basic session count tracking per user

---

## WORDPRESS COMPONENTS

### Pages

| Page ID | Slug | Purpose |
|---------|------|---------|
| 2496 | `/hive-mistress-ai/` | Chat interface (contains `[hive_mistress_chat]` shortcode) |
| 2520 | (access log) | View session logs |
| 2441 | `/hard-limits/` | BuddyForms hard limits quiz |

### WP Options

| Option Key | Purpose |
|------------|---------|
| `hm_system_prompt` | System prompt text for Claude API |
| `hm_conversation_logs` | Stored conversation history (serialized) |

### User Meta

| Meta Key | Purpose |
|----------|---------|
| `hm_session_count` | Number of sessions completed by user |

### Post Category

- **"d001 Training Logs"** - Session logs saved as posts in this category

---

## CODE ARCHITECTURE

### WPCode Snippet ID 2495 Structure

```
1. Admin Settings Page (Settings → Hive Mistress)
   - Edit system prompt
   - View/clear conversation logs

2. AJAX Handlers
   - wp_ajax_hm_send_message - Send message to Claude API
   - wp_ajax_hm_log_session - Save session log as WP post

3. Frontend Shortcode
   - [hive_mistress_chat] - Renders chat interface

4. CSS Styling
   - Inline styles for chat bubbles, input, scrolling
```

### API Integration

```php
// Current implementation
$response = wp_remote_post('https://api.anthropic.com/v1/messages', [
    'headers' => [
        'Content-Type' => 'application/json',
        'x-api-key' => $api_key,
        'anthropic-version' => '2023-06-01'
    ],
    'body' => json_encode([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'system' => $system_prompt,  // From WP option
        'messages' => $messages      // Conversation history
    ]),
    'timeout' => 30
]);
```

### Session Logging

```php
// Creates WordPress post for each session
$post_id = wp_insert_post([
    'post_title' => 'TRAINING LOG: D001-SESSION-' . str_pad($session_count, 3, '0', STR_PAD_LEFT),
    'post_content' => $content,  // Full conversation transcript
    'post_status' => 'publish',
    'post_type' => 'post',
    'post_author' => 1,
]);
// Assigns to "d001 Training Logs" category
```

---

## CURRENT LIMITATIONS

### No User State Tracking
- Only tracks `hm_session_count`
- No phase tracking (calibration, linear, playground, ppnc_prep)
- No checkpoint completion tracking
- No calibration data storage (theory_appetite, etc.)

### Single Static Prompt
- System prompt stored as single WP option
- No dynamic prompt injection based on user state
- No lore document injection
- No checkpoint scenario selection

### No Progress Visibility
- User cannot see their training progress
- No dashboard for completed sessions
- No checkpoint status display
- No PPNC readiness checklist

### No Recalibration System
- Cannot adjust communication style mid-training
- No mechanism to update calibration scores
- No adaptive content delivery

---

## INTEGRATION GAP

The V2 architecture documents define:
- Layer 1: System Prompt (`hive-mistress-system-prompt.md`)
- Layer 2: Lore Document (`hive-mistress-lore-document.md`)
- Layer 3: User State Schema (`hive-mistress-user-state-schema.md`)
- Layer 4: Conversation History (exists in current implementation)

**Current state:** Only Layer 4 is implemented. Layers 1-3 exist as documentation but are not connected to the live chatbot.

---

## RECOMMENDED UPGRADES

### Phase 1: User State Storage
- Implement `hm_training_state` user_meta with full schema
- Track phase, current_session, checkpoints_passed
- Store calibration data (theory_appetite, erotic_appetite, detail_preference)

### Phase 2: Dynamic Prompt Construction
- Build system prompt dynamically from:
  - Base prompt (Layer 1)
  - Relevant lore excerpts (Layer 2)
  - Current user state (Layer 3)
  - Current checkpoint scenario
- Inject calibration data into prompt

### Phase 3: Dashboard
- Consolidated view of:
  - Training progress (phase, session, checkpoints)
  - Hard limits summary
  - Session log access
  - PPNC readiness checklist

### Phase 4: Adaptive Content
- Select checkpoint variants based on calibration scores
- Adjust response density based on detail_preference
- Track and update calibration through conversation

---

## ADMIN ACCESS

1. **Edit System Prompt:** WP Admin → Settings → Hive Mistress
2. **View Session Logs:** Posts → Category "d001 Training Logs"
3. **Edit WPCode Snippet:** WP Admin → Code Snippets → ID 2495

---

*Documentation complete. Chatbot functional but not integrated with V2 architecture.*
