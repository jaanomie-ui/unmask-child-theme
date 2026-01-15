# WPCode Snippet CSS for Hive Mistress Chat

Add this CSS to your WPCode snippet (ID 2495) to style the chat interface.

## How to Add

1. Go to WP Admin > Code Snippets > Hive Mistress AI (ID 2495)
2. Find where the shortcode outputs HTML
3. Add this `<style>` block inside the shortcode output, OR
4. Add it via `wp_head` action in the snippet

## CSS Code

```html
<style>
/* ==========================================================================
   HIVE MISTRESS CHAT INTERFACE - WPCode Styles
   ========================================================================== */

/* Chat container */
#hm-chat {
    background: #111;
    border: 1px solid #333;
    font-family: 'Space Grotesk', -apple-system, sans-serif;
    -webkit-font-smoothing: antialiased;
}

/* Chat header */
.hm-header {
    background: #1a1a1a;
    padding: 10px 16px;
    border-bottom: 1px solid #333;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #888;
}

.hm-header::before {
    content: '';
    width: 6px;
    height: 6px;
    background: #00ff88;
    box-shadow: 0 0 4px #00ff88;
}

/* Message area */
.hm-messages {
    height: 400px;
    max-height: 50vh;
    min-height: 280px;
    overflow-y: auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    background: #0a0a0a;
}

.hm-messages::-webkit-scrollbar {
    width: 4px;
}

.hm-messages::-webkit-scrollbar-track {
    background: transparent;
}

.hm-messages::-webkit-scrollbar-thumb {
    background: #444;
}

/* Message bubbles */
.hm-message {
    max-width: 80%;
    padding: 12px 16px;
    line-height: 1.5;
    font-size: 11px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* HIVE MISTRESS messages - left aligned */
.hm-message.system {
    align-self: flex-start;
    background: #1a1a1a;
    color: #fff;
    border-left: 2px solid #660000;
    margin-right: auto;
}

.hm-message.system::before {
    content: 'HIVE MISTRESS';
    display: block;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #ff3333;
    margin-bottom: 6px;
    font-weight: 400;
}

/* User (D001) messages - right aligned */
.hm-message.user {
    align-self: flex-end;
    background: #660000;
    color: #fff;
    margin-left: auto;
}

.hm-message.user::before {
    content: 'D001';
    display: block;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 6px;
    font-weight: 400;
    text-align: right;
}

/* Input area */
.hm-input-area {
    padding: 16px;
    background: #111;
    border-top: 1px solid #333;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px;
    align-items: stretch;
}

.hm-input-area input {
    width: 100%;
    background: #0a0a0a;
    border: 1px solid #333;
    padding: 12px 16px;
    color: #fff;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 11px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    transition: border-color 0.2s ease;
}

.hm-input-area input::placeholder {
    color: #666;
    text-transform: uppercase;
}

.hm-input-area input:focus {
    outline: none;
    border-color: #ff3333;
}

.hm-input-area button {
    background: #660000;
    border: 1px solid #333;
    padding: 12px 20px;
    color: #fff;
    cursor: pointer;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 10px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    transition: background 0.2s ease;
}

.hm-input-area button:hover {
    background: #880000;
}

/* Typing indicator */
.hm-typing {
    align-self: flex-start;
    padding: 12px 16px;
    background: #1a1a1a;
    border-left: 2px solid #660000;
    display: flex;
    gap: 4px;
}

.hm-typing span {
    width: 4px;
    height: 4px;
    background: #666;
    animation: hm-typing-pulse 1.4s infinite ease-in-out;
}

.hm-typing span:nth-child(2) { animation-delay: 0.2s; }
.hm-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes hm-typing-pulse {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.35; }
    30% { transform: translateY(-2px); opacity: 1; }
}

/* Session save button */
.hm-save-session {
    background: transparent;
    border: 1px solid #333;
    padding: 8px 16px;
    color: #666;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    cursor: pointer;
    margin-top: 12px;
    align-self: flex-end;
}

.hm-save-session:hover {
    border-color: #ff3333;
    color: #fff;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .hm-messages {
        height: 50vh;
        max-height: none;
        min-height: 240px;
        padding: 16px;
    }

    .hm-message {
        max-width: 85%;
    }
}
</style>
```

## Alternative: PHP wp_head Hook

If your snippet is PHP-based, add this to output CSS in the head:

```php
add_action('wp_head', function() {
    if (!is_page(2496)) return;
    ?>
    <style>
    /* Paste the CSS from above here */
    </style>
    <?php
});
```

## Required HTML Structure

Make sure your WPCode shortcode outputs this HTML structure:

```html
<div id="hm-chat">
    <div class="hm-header">Hive Mistress Active</div>
    <div class="hm-messages">
        <!-- Messages appear here -->
        <div class="hm-message system">System message text</div>
        <div class="hm-message user">User message text</div>
    </div>
    <div class="hm-input-area">
        <input type="text" placeholder="Enter response...">
        <button>Send</button>
    </div>
</div>
```

## Final Step

After adding CSS to the WPCode snippet:
1. Go to Pages > Edit Page 2496 (Hive Mistress AI)
2. In Page Attributes, change Template to "Hive Mistress Chat"
3. Update the page
4. Clear cache and test
