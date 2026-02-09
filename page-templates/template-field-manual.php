<?php
/**
 * Template Name: Field Manual
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Read manual content from docs directory
$manual_path = get_stylesheet_directory() . '/docs/pink-panthers-field-manual.md';
$manual_content = file_exists($manual_path) ? file_get_contents($manual_path) : 'Manual not found.';

// Basic markdown to HTML conversion (preserves structure without heavy parsing)
$manual_html = '<div class="field-manual-content" style="white-space: pre-wrap; font-family: var(--font-mono); line-height: 1.6;">'
    . esc_html($manual_content)
    . '</div>';
?>

<div class="field-manual" style="max-width: 900px; margin: 2rem auto; padding: 2rem;">
    <h1 style="font-family: var(--font-heading); margin-bottom: 2rem;">Pink Panthers Field Manual</h1>
    <?php echo $manual_html; ?>
</div>

<?php
get_footer();
