# Hive Mistress Chat Migration

## Overview

The Hive Mistress chat system has been migrated from WPCode snippet #2495 to git-controlled theme files. This provides version control, easier debugging, and proper code organization.

## Files Created/Modified

### New File

| File | Purpose |
|------|---------|
| `inc/hive-mistress-shortcode.php` | Complete system: shortcode, AJAX handlers, admin page, frontend JS |

### Modified Files

| File | Change |
|------|--------|
| `functions.php` | Added `require_once` for shortcode file |

### Existing Files (Unchanged)

| File | Purpose |
|------|---------|
| `inc/hive-mistress-state.php` | Drone state management, tolerance tracking |
| `inc/hive-mistress-prompts.php` | AI prompt construction, system voice |
| `assets/css/pages/hive-mistress-chat.css` | Chat UI styles (ethereal design) |
| `page-templates/template-hive-mistress-chat.php` | Page template for chat interface |

## How to Disable WPCode Snippet #2495

1. Go to **WP Admin > Code Snippets > WPCode**
2. Find snippet **#2495** (Hive Mistress Conditioning System V2)
3. Toggle the switch to **Inactive**
4. Save changes
5. Test the chat at https://staging4.houseofanomie.com/hive-mistress-ai/

## API Configuration

The Anthropic API key is stored in WordPress options, configurable via:

**WP Admin > Settings > Hive Mistress**

- API Key: Your Anthropic Claude API key
- Daily Limit: Max messages per user per day (default: 40)

**API Details:**
- Endpoint: `https://api.anthropic.com/v1/messages`
- Model: `claude-sonnet-4-20250514`
- Headers: `x-api-key`, `anthropic-version: 2023-06-01`

## Shortcode Usage

The page should contain:

```
[hive_mistress_chat]
```

This renders the full chat interface with:
- Message history (from localStorage per user)
- Real-time AI responses via Claude
- Save session functionality (creates WordPress posts)
- Daily limit enforcement

## AJAX Endpoints

| Action | Purpose |
|--------|---------|
| `hm_send_message` | Send user message, get Claude AI response |
| `hm_ajax_log_session` | Save session as WordPress post in "Session Logs" category |

All endpoints require user authentication.

## Data Storage

| Data | Location |
|------|----------|
| Chat messages | `localStorage` (browser, keyed by user ID) |
| Conversation logs | `wp_options` table (`hm_conversation_logs`) |
| Session logs | WordPress posts (category: "Session Logs") |
| Session count | `wp_usermeta` (`hm_session_count`) |
| API key | `wp_options` table (`hm_api_key`) |
| Daily limit | `wp_options` table (`hm_daily_limit`) |

## Rollback Instructions

If issues occur after disabling the WPCode snippet:

1. Re-enable WPCode snippet #2495
2. In `functions.php`, comment out:
   ```php
   // require_once get_stylesheet_directory() . '/inc/hive-mistress-shortcode.php';
   ```
3. Clear cache: `wp cache flush && wp sg purge`

## Testing Checklist

- [ ] Page loads without errors
- [ ] Chat input accepts text
- [ ] Messages appear correctly (user and assistant)
- [ ] Claude AI responds to messages
- [ ] Loading indicator shows during API call
- [ ] Save Session creates WordPress post
- [ ] Daily limit counter displays
- [ ] Admin page accessible at Settings > Hive Mistress
- [ ] API key saves correctly
- [ ] Conversation logs viewable in admin

## Architecture

```
User sends message
       |
       v
[inline JS] --AJAX--> [hive-mistress-shortcode.php]
                               |
                               v
                      [hive-mistress-prompts.php]
                               |
                               v
                      Anthropic Claude API
                               |
                               v
                      [hive-mistress-state.php]
                               |
                               v
                      Response + Log Conversation
```
