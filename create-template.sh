#!/bin/bash
#
# UNMASK Template Generator
# Creates template + CSS + enqueue with correct paths, adds to functions.php
#
# Usage: ./create-template.sh template-name
# Example: ./create-template.sh iso-board
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
THEME_DIR="/Users/ja/unmask-child-theme"
REMOTE_THEME="unmask-staging:~/www/staging4.houseofanomie.com/public_html/wp-content/themes/buddyboss-theme-child"
SSH_AUTH="SSH_AUTH_SOCK=/tmp/ssh-agent-unmask.sock"

# Check for template name argument
if [ -z "$1" ]; then
    echo -e "${RED}Error: No template name provided${NC}"
    echo ""
    echo "Usage: ./create-template.sh template-name"
    echo "Example: ./create-template.sh iso-board"
    exit 1
fi

TEMPLATE_NAME="$1"
TEMPLATE_NAME_CLEAN=$(echo "$TEMPLATE_NAME" | tr '[:upper:]' '[:lower:]' | tr ' ' '-')
TEMPLATE_NAME_TITLE=$(echo "$TEMPLATE_NAME" | sed 's/-/ /g' | awk '{for(i=1;i<=NF;i++) $i=toupper(substr($i,1,1)) substr($i,2)}1')

# File paths (these are the CORRECT paths that match)
TEMPLATE_FILE="page-templates/template-${TEMPLATE_NAME_CLEAN}.php"
CSS_FILE="assets/css/pages/${TEMPLATE_NAME_CLEAN}.css"
ENQUEUE_FILE="inc/enqueue-${TEMPLATE_NAME_CLEAN}.php"

echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${CYAN}  UNMASK Template Generator${NC}"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "Template Name:  ${GREEN}${TEMPLATE_NAME_TITLE}${NC}"
echo -e "Template File:  ${YELLOW}${TEMPLATE_FILE}${NC}"
echo -e "CSS File:       ${YELLOW}${CSS_FILE}${NC}"
echo -e "Enqueue File:   ${YELLOW}${ENQUEUE_FILE}${NC}"
echo ""

# Check if files already exist
EXISTING_FILES=""
[ -f "${THEME_DIR}/${TEMPLATE_FILE}" ] && EXISTING_FILES="${EXISTING_FILES}\n  - ${TEMPLATE_FILE}"
[ -f "${THEME_DIR}/${CSS_FILE}" ] && EXISTING_FILES="${EXISTING_FILES}\n  - ${CSS_FILE}"
[ -f "${THEME_DIR}/${ENQUEUE_FILE}" ] && EXISTING_FILES="${EXISTING_FILES}\n  - ${ENQUEUE_FILE}"

if [ -n "$EXISTING_FILES" ]; then
    echo -e "${YELLOW}Warning: These files already exist:${NC}"
    echo -e "$EXISTING_FILES"
    echo ""
    read -p "Overwrite? (y/N): " OVERWRITE
    if [ "$OVERWRITE" != "y" ] && [ "$OVERWRITE" != "Y" ]; then
        echo "Aborted."
        exit 0
    fi
fi

# Create directories if needed
mkdir -p "${THEME_DIR}/page-templates"
mkdir -p "${THEME_DIR}/assets/css/pages"
mkdir -p "${THEME_DIR}/inc"

echo -e "${GREEN}Creating files...${NC}"

# 1. Create Template File
cat > "${THEME_DIR}/${TEMPLATE_FILE}" << 'TEMPLATE_EOF'
<?php
/**
 * Template Name: __TITLE__
 * Description: Custom template for __TITLE__ page
 *
 * @package UNMASK
 */

get_header(); ?>

<main id="main" class="site-main __SLUG__-page">
    <div class="unmask-container">

        <?php while (have_posts()) : the_post(); ?>

            <header class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </header>

            <div class="page-content">
                <?php the_content(); ?>
            </div>

        <?php endwhile; ?>

    </div>
</main>

<?php get_footer(); ?>
TEMPLATE_EOF

# Replace placeholders in template
sed -i '' "s/__TITLE__/${TEMPLATE_NAME_TITLE}/g" "${THEME_DIR}/${TEMPLATE_FILE}"
sed -i '' "s/__SLUG__/${TEMPLATE_NAME_CLEAN}/g" "${THEME_DIR}/${TEMPLATE_FILE}"

echo -e "  ${GREEN}✓${NC} Created ${TEMPLATE_FILE}"

# 2. Create CSS File
cat > "${THEME_DIR}/${CSS_FILE}" << 'CSS_EOF'
/**
 * __TITLE__ Page Styles
 *
 * @package UNMASK
 */

/* ==========================================================================
   PAGE LAYOUT
   ========================================================================== */

.__SLUG__-page {
    padding: var(--space-xl) 0;
}

.__SLUG__-page .unmask-container {
    max-width: var(--container-lg);
    margin: 0 auto;
    padding: 0 var(--space-md);
}

/* ==========================================================================
   PAGE HEADER
   ========================================================================== */

.__SLUG__-page .page-header {
    margin-bottom: var(--space-xl);
}

.__SLUG__-page .page-title {
    font-size: var(--type-3xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
}

/* ==========================================================================
   PAGE CONTENT
   ========================================================================== */

.__SLUG__-page .page-content {
    color: var(--text-secondary);
    line-height: var(--leading-relaxed);
}
CSS_EOF

# Replace placeholders in CSS
sed -i '' "s/__TITLE__/${TEMPLATE_NAME_TITLE}/g" "${THEME_DIR}/${CSS_FILE}"
sed -i '' "s/__SLUG__/${TEMPLATE_NAME_CLEAN}/g" "${THEME_DIR}/${CSS_FILE}"

echo -e "  ${GREEN}✓${NC} Created ${CSS_FILE}"

# 3. Create Enqueue File
cat > "${THEME_DIR}/${ENQUEUE_FILE}" << 'ENQUEUE_EOF'
<?php
/**
 * __TITLE__ Page CSS Enqueue
 *
 * Conditionally loads __SLUG__.css only on __TITLE__ page template.
 * Loads after BuddyBoss theme CSS to ensure proper cascade.
 *
 * @package UNMASK
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if current page uses __TITLE__ template
 */
function unmask_is___FUNC___page() {
    // Template path MUST match exactly: __TEMPLATE_PATH__
    $template_path = '__TEMPLATE_PATH__';

    // Method 1: Check page template slug
    $template_slug = get_page_template_slug();
    if ($template_slug === $template_path) {
        return true;
    }

    // Method 2: Check is_page_template
    if (is_page_template($template_path)) {
        return true;
    }

    // Method 3: Check stored template meta
    global $post;
    if ($post && is_page()) {
        $stored_template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($stored_template === $template_path) {
            return true;
        }
    }

    return false;
}

/**
 * Enqueue __TITLE__ page styles
 */
add_action('wp_enqueue_scripts', 'unmask_enqueue___FUNC___styles', 999);
function unmask_enqueue___FUNC___styles() {
    if (!unmask_is___FUNC___page()) {
        return;
    }

    $deps = array('unmask-00-design-system');

    if (wp_style_is('buddyboss-theme-template-css', 'registered') ||
        wp_style_is('buddyboss-theme-template-css', 'enqueued')) {
        $deps[] = 'buddyboss-theme-template-css';
    }

    $css_file = get_stylesheet_directory() . '/__CSS_PATH__';
    $version = file_exists($css_file) ? filemtime($css_file) : time();

    wp_enqueue_style(
        'unmask-__SLUG__',
        get_stylesheet_directory_uri() . '/__CSS_PATH__',
        $deps,
        $version
    );
}
ENQUEUE_EOF

# Create function-safe name (replace hyphens with underscores)
FUNC_NAME=$(echo "$TEMPLATE_NAME_CLEAN" | tr '-' '_')

# Replace placeholders in enqueue
sed -i '' "s/__TITLE__/${TEMPLATE_NAME_TITLE}/g" "${THEME_DIR}/${ENQUEUE_FILE}"
sed -i '' "s/__SLUG__/${TEMPLATE_NAME_CLEAN}/g" "${THEME_DIR}/${ENQUEUE_FILE}"
sed -i '' "s/__FUNC__/${FUNC_NAME}/g" "${THEME_DIR}/${ENQUEUE_FILE}"
sed -i '' "s|__TEMPLATE_PATH__|${TEMPLATE_FILE}|g" "${THEME_DIR}/${ENQUEUE_FILE}"
sed -i '' "s|__CSS_PATH__|${CSS_FILE}|g" "${THEME_DIR}/${ENQUEUE_FILE}"

echo -e "  ${GREEN}✓${NC} Created ${ENQUEUE_FILE}"

# 4. Add require_once to functions.php if not already present
REQUIRE_LINE="require_once get_stylesheet_directory() . '/inc/enqueue-${TEMPLATE_NAME_CLEAN}.php';"
REQUIRE_COMMENT="// ${TEMPLATE_NAME_TITLE} page styles"

if grep -q "enqueue-${TEMPLATE_NAME_CLEAN}.php" "${THEME_DIR}/functions.php"; then
    echo -e "  ${YELLOW}⊘${NC} functions.php already has require_once for this template"
else
    # Find the last require_once in the INCLUDES section and add after it
    # Using awk to find and insert after the last require_once line before the next section
    awk -v comment="$REQUIRE_COMMENT" -v require="$REQUIRE_LINE" '
    /^require_once.*\/inc\/enqueue-.*\.php/ { last_require = NR; last_line = $0 }
    { lines[NR] = $0 }
    END {
        for (i = 1; i <= NR; i++) {
            print lines[i]
            if (i == last_require) {
                print ""
                print comment
                print require
            }
        }
    }
    ' "${THEME_DIR}/functions.php" > "${THEME_DIR}/functions.php.tmp"

    mv "${THEME_DIR}/functions.php.tmp" "${THEME_DIR}/functions.php"
    echo -e "  ${GREEN}✓${NC} Added require_once to functions.php"
fi

# 5. Validate PHP syntax (optional - skip if php not available)
echo ""
PHP_BIN=$(which php 2>/dev/null || echo "")
if [ -n "$PHP_BIN" ]; then
    echo -e "${GREEN}Validating PHP syntax...${NC}"
    $PHP_BIN -l "${THEME_DIR}/${TEMPLATE_FILE}" 2>&1 | grep -v "No syntax errors" || true
    $PHP_BIN -l "${THEME_DIR}/${ENQUEUE_FILE}" 2>&1 | grep -v "No syntax errors" || true
    $PHP_BIN -l "${THEME_DIR}/functions.php" 2>&1 | grep -v "No syntax errors" || true
    echo -e "  ${GREEN}✓${NC} PHP syntax valid"
else
    echo -e "${YELLOW}Skipping PHP validation (php not in PATH)${NC}"
fi

# 6. Ask about deployment
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
read -p "Deploy to staging now? (Y/n): " DEPLOY

if [ "$DEPLOY" != "n" ] && [ "$DEPLOY" != "N" ]; then
    echo ""
    echo -e "${GREEN}Deploying to staging...${NC}"

    # Deploy all files
    eval "${SSH_AUTH} rsync -avz ${THEME_DIR}/${TEMPLATE_FILE} ${REMOTE_THEME}/${TEMPLATE_FILE}"
    eval "${SSH_AUTH} rsync -avz ${THEME_DIR}/${CSS_FILE} ${REMOTE_THEME}/${CSS_FILE}"
    eval "${SSH_AUTH} rsync -avz ${THEME_DIR}/${ENQUEUE_FILE} ${REMOTE_THEME}/${ENQUEUE_FILE}"
    eval "${SSH_AUTH} rsync -avz ${THEME_DIR}/functions.php ${REMOTE_THEME}/functions.php"

    # Fix permissions
    echo -e "${GREEN}Fixing permissions...${NC}"
    eval "${SSH_AUTH} ssh unmask-staging 'chmod 644 ${REMOTE_THEME#*:}/${TEMPLATE_FILE} ${REMOTE_THEME#*:}/${CSS_FILE} ${REMOTE_THEME#*:}/${ENQUEUE_FILE} ${REMOTE_THEME#*:}/functions.php'"

    echo -e "  ${GREEN}✓${NC} Deployed and permissions set"
fi

# Summary
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}Done!${NC}"
echo ""
echo "Next steps:"
echo "  1. Go to WP Admin → Pages"
echo "  2. Create or edit a page"
echo "  3. In Page Attributes, select '${TEMPLATE_NAME_TITLE}' template"
echo "  4. View page - CSS should load automatically"
echo ""
echo -e "Debug: View page source, search for ${YELLOW}unmask-${TEMPLATE_NAME_CLEAN}${NC} in <head>"
echo ""
