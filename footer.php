<?php
/**
 * UNMASK Footer
 *
 * Clean minimal footer - overrides BuddyBoss parent theme footer.
 * Removes widget areas and extra markup.
 *
 * @package unmask-child-theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

</div><!-- .bb-grid -->
</div><!-- .container -->
</div><!-- #content -->

<footer class="unmask-footer">
    <div class="unmask-footer__inner">
        <span class="unmask-footer__copyright">&copy; <?php echo date('Y'); ?> UNMASK</span>
        <span class="unmask-footer__separator">|</span>
        <a href="/terms" class="unmask-footer__link">terms</a>
        <a href="/privacy" class="unmask-footer__link">privacy</a>
    </div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
