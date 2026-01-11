<?php
/**
 * UNMASK Simplified Profile Avatar Menu
 *
 * Overrides BuddyBoss theme's header-profile-menu.php
 * Only shows: My Dossier, Notifications, Settings, Log Out
 *
 * @package UNMASK
 */

if ( ! is_user_logged_in() ) {
    return;
}

// Get user's dossier (profile) URL
$dossier_url = bp_loggedin_user_domain();
$settings_url = trailingslashit( bp_loggedin_user_domain() . bp_get_settings_slug() );

// Notification count
$notif_count = 0;
if ( bp_is_active( 'notifications' ) ) {
    $notif_count = bp_notifications_get_unread_notification_count( bp_loggedin_user_id() );
}
$notif_link = trailingslashit( bp_loggedin_user_domain() . bp_get_notifications_slug() );
$notif_title = $notif_count
    ? sprintf( __( 'Notifications %s', 'buddyboss-theme' ), '<span class="count">' . bp_core_number_format( $notif_count ) . '</span>' )
    : __( 'Notifications', 'buddyboss-theme' );

?>

<!-- My Dossier -->
<li id="wp-admin-bar-my-dossier">
    <a class="ab-item" href="<?php echo esc_url( $dossier_url ); ?>">
        <i class="bb-icon-l bb-icon-user-avatar"></i>
        <?php esc_html_e( 'My Dossier', 'buddyboss-theme' ); ?>
    </a>
</li>

<!-- Notifications -->
<li id="wp-admin-bar-my-account-notifications" class="menupop parent">
    <a class="ab-item" aria-haspopup="true" href="<?php echo esc_url( $notif_link ); ?>">
        <i class="bb-icon-l bb-icon-bell"></i>
        <span class="wp-admin-bar-arrow" aria-hidden="true"></span>
        <?php echo wp_kses_post( $notif_title ); ?>
    </a>
    <div class="ab-sub-wrapper wrapper">
        <ul id="wp-admin-bar-my-account-notifications-default" class="ab-submenu">
            <li id="wp-admin-bar-my-account-notifications-unread">
                <a class="ab-item" href="<?php echo esc_url( $notif_link ); ?>">
                    <?php echo $notif_count
                        ? sprintf( __( 'Unread %s', 'buddyboss-theme' ), '<span class="count">' . bp_core_number_format( $notif_count ) . '</span>' )
                        : __( 'Unread', 'buddyboss-theme' ); ?>
                </a>
            </li>
            <li id="wp-admin-bar-my-account-notifications-read">
                <a class="ab-item" href="<?php echo esc_url( trailingslashit( $notif_link . 'read' ) ); ?>">
                    <?php esc_html_e( 'Read', 'buddyboss-theme' ); ?>
                </a>
            </li>
        </ul>
    </div>
</li>

<!-- Settings -->
<li id="wp-admin-bar-my-account-settings" class="menupop parent">
    <a class="ab-item" aria-haspopup="true" href="<?php echo esc_url( $settings_url ); ?>">
        <i class="bb-icon-l bb-icon-cog"></i>
        <span class="wp-admin-bar-arrow" aria-hidden="true"></span>
        <?php esc_html_e( 'Settings', 'buddyboss-theme' ); ?>
    </a>
    <div class="ab-sub-wrapper wrapper">
        <ul id="wp-admin-bar-my-account-settings-default" class="ab-submenu">
            <li id="wp-admin-bar-my-account-settings-general">
                <a class="ab-item" href="<?php echo esc_url( $settings_url ); ?>">
                    <?php esc_html_e( 'Login Information', 'buddyboss-theme' ); ?>
                </a>
            </li>
            <?php if ( has_action( 'bp_notification_settings' ) ) : ?>
            <li id="wp-admin-bar-my-account-settings-notifications">
                <a class="ab-item" href="<?php echo esc_url( trailingslashit( $settings_url . 'notifications' ) ); ?>">
                    <?php esc_html_e( 'Email Preferences', 'buddyboss-theme' ); ?>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</li>

<!-- Log Out -->
<li class="logout-link">
    <a href="<?php echo esc_url( wp_logout_url( bp_get_requested_url() ) ); ?>">
        <i class="bb-icon-l bb-icon-sign-out"></i>
        <?php esc_html_e( 'Log Out', 'buddyboss-theme' ); ?>
    </a>
</li>
