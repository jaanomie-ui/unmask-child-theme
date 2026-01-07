# UNMASK Child Theme - Claude Code Instructions

## Deployment

When deploying files to staging, use rsync then fix permissions via SSH (macOS rsync doesn't support --chmod syntax):

```bash
# Step 1: Deploy files
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock rsync -avz \
  /Users/ja/unmask-child-theme/path/to/file \
  unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/path/to/

# Step 2: Fix permissions on deployed files
SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock ssh unmask-staging \
  "chmod 644 ~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/path/to/file"
```

### Permission Reference
- Files: `644` (rw-r--r--) - owner read/write, world read
- Directories: `755` (rwxr-xr-x) - owner full, world read/execute

### Staging Server
- Host alias: `unmask-staging`
- Theme path: `~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child/`
- SSH auth: `SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock`

## File Structure

```
unmask-child-theme/
├── assets/
│   ├── css/
│   │   ├── 00-design-system.css    ← Design tokens (source of truth)
│   │   ├── 01-base.css             ← Base styles
│   │   ├── 02-components.css       ← UI components
│   │   ├── ...
│   │   └── pages/                  ← Page-specific CSS
│   │       ├── factory.css
│   │       ├── factory-book.css
│   │       └── archive-magazine.css
│   └── js/
│       ├── unmask-gallery.js
│       └── archive-magazine.js
├── inc/                            ← Conditional enqueue files
│   ├── enqueue-factory.php
│   ├── enqueue-factory-book.php
│   └── enqueue-archive-magazine.php
├── page-templates/                 ← Page templates (template-*.php)
│   ├── template-homepage.php
│   ├── template-archive-magazine.php
│   ├── page-register-visitor.php
│   └── page-welcome.php
├── template-parts/                 ← Reusable template parts
│   ├── components/
│   ├── homepage/
│   └── global/
├── includes/                       ← PHP includes (shortcodes, etc.)
└── functions.php                   ← Main functions file
```

## CSS Architecture

- Use design tokens from `00-design-system.css` (e.g., `var(--bg-card)`, `var(--text-primary)`)
- Page-specific CSS goes in `assets/css/pages/`
- Enqueue files go in `inc/` following the pattern in `enqueue-factory.php`
- Always add BuddyBoss CSS dependency for proper cascade

## Adding New Page Templates

1. Create template in `page-templates/template-{name}.php`
2. Create CSS in `assets/css/pages/{name}.css`
3. Create enqueue in `inc/enqueue-{name}.php` (copy factory pattern)
4. Add `require_once` in `functions.php` under INCLUDES section
5. Deploy with rsync, then chmod 644 on deployed files
6. Assign template to page in WP Admin
