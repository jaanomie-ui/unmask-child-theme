<?php
/**
 * Full-Bleed Footer
 *
 * Minimal footer for full-bleed pages.
 * Closes markup opened by header-fullbleed.php.
 *
 * Usage: get_footer('fullbleed');
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

    </main><!-- #main -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
