<?php
/**
 * UNMASK Dossier v3 - Profile Loop
 *
 * Terminal aesthetic profile sections
 *
 * @package UNMASK
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

bp_nouveau_xprofile_hook('before', 'loop_content');
?>

<div class="dossier">
    <?php
    // Active ISOs
    get_template_part('template-parts/dossier/section-isos');

    // Creative Practice
    get_template_part('template-parts/dossier/section-practice');

    // Kink Profile
    get_template_part('template-parts/dossier/section-kink');

    // Credits
    get_template_part('template-parts/dossier/section-credits');

    // MY UNMASK (own profile only)
    if (bp_is_my_profile()) {
        get_template_part('template-parts/dossier/my-unmask');
    }
    ?>
</div>

<?php
bp_nouveau_xprofile_hook('after', 'loop_content');
