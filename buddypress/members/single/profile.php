<?php
/**
 * UNMASK - Users Profile
 *
 * Child theme override that removes extra wrappers on profile edit pages
 * to allow our split panel design to render cleanly.
 *
 * @since BuddyPress 3.0.0
 * @package UNMASK
 */

$profile_link = trailingslashit( bp_displayed_user_domain() . bp_get_profile_slug() );

$bp_is_user_profile_edit       = bp_is_user_profile_edit();
$bp_is_user_change_avatar      = bp_is_user_change_avatar();
$bp_is_user_change_cover_image = bp_is_user_change_cover_image();

// For profile EDIT pages, render our clean split panel template without wrappers
if ( $bp_is_user_profile_edit && bp_is_my_profile() ) {
    bp_get_template_part( 'members/single/profile/edit' );
    return;
}

// For profile VIEW pages, render clean dossier view without wrappers
if ( bp_current_action() === 'public' ) {
    bp_get_template_part( 'members/single/profile/profile-view' );
    return;
}

// For avatar/cover changes, keep minimal wrapper
if ( $bp_is_user_change_avatar || $bp_is_user_change_cover_image ) { ?>
    <header class="profile-header flex align-items-center">
        <h1 class="entry-title bb-profile-title"><?php esc_attr_e( 'Edit Profile', 'buddyboss-theme' ); ?></h1>
        <a href="<?php echo esc_url( $profile_link ); ?>" class="push-right button outline small">
            <i class="bb-icon-f bb-icon-user"></i> <?php esc_attr_e( 'View Profile', 'buddyboss-theme' ); ?>
        </a>
    </header>

    <div class="bp-profile-wrapper">
        <?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>
        <div class="bp-profile-content">
            <div class="profile <?php echo esc_attr( bp_current_action() ); ?>">
                <?php
                switch ( bp_current_action() ) :
                    case 'change-avatar':
                        bp_get_template_part( 'members/single/profile/change-avatar' );
                        break;
                    case 'change-cover-image':
                        bp_get_template_part( 'members/single/profile/change-cover-image' );
                        break;
                endswitch;
                ?>
            </div>
        </div>
    </div>
<?php
    return;
}

// For profile VIEW pages, use normal display
?>
<div class="bp-profile-wrapper <?php echo bp_is_user_profile() ? 'need-separator' : ''; ?>">
    <div class="bp-profile-content">

        <?php bp_nouveau_member_hook( 'before', 'profile_content' ); ?>

        <div class="profile <?php echo esc_attr( bp_current_action() ); ?>">
            <?php
            switch ( bp_current_action() ) :
                case 'public':
                    // Use clean UNMASK profile view template
                    bp_get_template_part( 'members/single/profile/profile-view' );
                    break;
                default:
                    bp_get_template_part( 'members/single/plugins' );
                    break;
            endswitch;
            ?>
        </div>

        <?php bp_nouveau_member_hook( 'after', 'profile_content' ); ?>

    </div>
</div>
