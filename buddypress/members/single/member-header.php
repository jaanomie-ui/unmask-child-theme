<?php
/**
 * UNMASK Dossier v3 - Member Header
 *
 * Terminal aesthetic profile header
 *
 * @package UNMASK
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Skip on non-profile pages AND on profile view (dossier-view handles its own header)
if (bp_is_user_messages() || bp_is_user_settings() || bp_is_user_notifications() ||
    bp_is_user_profile_edit() || bp_is_user_change_avatar() || bp_is_user_change_cover_image() ||
    (bp_is_user_profile() && bp_current_action() === 'public')) {
    return;
}
?>

<div class="dossier">
    <?php get_template_part('template-parts/dossier/header'); ?>
</div>

<?php
// Remove Connection confirmation popup (required by BuddyBoss)
?>
<div class="bb-remove-connection bb-action-popup" style="display: none">
    <transition name="modal">
        <div class="modal-mask bb-white bbm-model-wrap">
            <div class="modal-wrapper">
                <div class="modal-container">
                    <header class="bb-model-header">
                        <h4><span class="target_name"><?php esc_html_e('Remove Connection', 'buddyboss'); ?></span></h4>
                        <a class="bb-close-remove-connection bb-model-close-button" href="#">
                            <span class="bb-icon-l bb-icon-times"></span>
                        </a>
                    </header>
                    <div class="bb-remove-connection-content bb-action-popup-content">
                        <p>
                            <?php
                            printf(
                                esc_html__('Are you sure you want to remove %s from your connections?', 'buddyboss'),
                                '<span class="bb-user-name"></span>'
                            );
                            ?>
                        </p>
                    </div>
                    <footer class="bb-model-footer flex align-items-center">
                        <a class="bb-close-remove-connection bb-close-action-popup" href="#"><?php esc_html_e('Cancel', 'buddyboss'); ?></a>
                        <a class="button push-right bb-confirm-remove-connection" href="#"><?php esc_html_e('Confirm', 'buddyboss'); ?></a>
                    </footer>
                </div>
            </div>
        </div>
    </transition>
</div>
