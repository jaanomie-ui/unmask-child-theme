# UNMASK Deployment Guide

## Server Setup

Both staging and live are on the same SiteGround server, accessible via SSH.

| Site | URL | Server Path |
|------|-----|-------------|
| **Staging** | staging4.houseofanomie.com | `~/www/staging4.houseofanomie.com/public_html/` |
| **Live** | unmaskmagazine.com | `~/www/houseofanomie.com/public_html/` |

**SSH Config:** `unmask-staging` (see ~/.ssh/config)
**Auth:** `SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock`

## Theme Paths

```
STAGING: ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/
LIVE:    ~/www/houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/
```

---

## Deployment Workflows

### 1. Local → Staging (Development)

Use this for day-to-day development:

```bash
# Deploy single file
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/path/to/file \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/path/to/

# Fix permissions
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "chmod 644 ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/path/to/file"
```

### 2. Local → Live (Direct Deploy)

Same as staging, just different path:

```bash
# Deploy single file to LIVE
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/path/to/file \
  unmask-staging:~/www/houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/path/to/

# Fix permissions on LIVE
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "chmod 644 ~/www/houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/path/to/file"
```

### 3. Staging → Live (Sync Entire Theme)

When staging is tested and ready, sync everything to live:

```bash
# Dry run first (shows what would change)
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "rsync -avzn --delete \
   ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/ \
   ~/www/houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/"

# Actually sync (remove -n flag)
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "rsync -avz --delete \
   ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/ \
   ~/www/houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/"
```

### 4. Sync Specific Directory (Staging → Live)

```bash
# Example: sync just the CSS pages folder
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "rsync -avz \
   ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/assets/css/pages/ \
   ~/www/houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/assets/css/pages/"
```

---

## Compare Staging vs Live

Check what files differ:

```bash
# List files that differ
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "diff -rq \
   ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/ \
   ~/www/houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/ \
   2>/dev/null | head -50"

# Compare specific file
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "diff ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/functions.php \
        ~/www/houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/functions.php"
```

---

## Permission Reference

- **Files:** `644` (rw-r--r--)
- **Directories:** `755` (rwxr-xr-x)

---

## MCP (WordPress API Access)

The WordPress MCP plugin enables Claude to read/write WordPress content directly.

| Site | MCP Status | Tools Prefix |
|------|------------|--------------|
| Staging | ✅ Installed | `mcp__wordpress__*` |
| Live | ✅ Installed | `mcp__wordpress-live__*` (needs JWT setup) |

### Setting up Live MCP

1. **Plugin already installed** - copied from staging

2. **Get JWT Token from WP Admin:**
   - Go to: https://unmaskmagazine.com/wp-admin/options-general.php?page=wordpress-mcp
   - Click "Generate Token" (for admin user)
   - Copy the JWT token

3. **Add MCP server to Claude:**
   ```bash
   claude mcp add wordpress-live \
     -e WP_API_URL=https://unmaskmagazine.com/ \
     -e JWT_TOKEN=<paste-your-token-here> \
     -s user \
     -- npx -y @automattic/mcp-wordpress-remote@latest
   ```

4. **Restart Claude Code** for the new server to connect
