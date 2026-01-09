<?php
/**
 * UNMASK Footer
 *
 * Clean minimal footer - overrides BuddyBoss parent theme footer.
 * Removes widget areas and extra markup.
 *
 * @package unmask-child-theme
 *
 * ACF Fields (non-repeating):
 * - footer_site_name: Text (default: "UNMASK")
 * - footer_terms_text: Text (default: "terms")
 * - footer_terms_url: URL (default: "/terms")
 * - footer_privacy_text: Text (default: "privacy")
 * - footer_privacy_url: URL (default: "/privacy")
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get ACF field values with fallbacks
$footer_site_name = unmask_get_field('footer_site_name', 'UNMASK');
$footer_terms_text = unmask_get_field('footer_terms_text', 'terms');
$footer_terms_url = unmask_get_field('footer_terms_url', '/terms');
$footer_privacy_text = unmask_get_field('footer_privacy_text', 'privacy');
$footer_privacy_url = unmask_get_field('footer_privacy_url', '/privacy');
?>

</div><!-- .bb-grid -->
</div><!-- .container -->
</div><!-- #content -->

<footer class="unmask-footer">
    <div class="unmask-footer__inner">
        <span class="unmask-footer__copyright">&copy; <?php echo date('Y'); ?> <?php echo esc_html($footer_site_name); ?></span>
        <span class="unmask-footer__separator">|</span>
        <a href="<?php echo esc_url($footer_terms_url); ?>" class="unmask-footer__link"><?php echo esc_html($footer_terms_text); ?></a>
        <a href="<?php echo esc_url($footer_privacy_url); ?>" class="unmask-footer__link"><?php echo esc_html($footer_privacy_text); ?></a>
    </div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
