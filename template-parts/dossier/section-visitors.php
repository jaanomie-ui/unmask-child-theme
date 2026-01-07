<?php
/**
 * Dossier Section: Visitors Grid
 *
 * Displays recent/active visitors in a grid within the dossier layout.
 * "any time another user appears on the site, you should be able to see them"
 *
 * Shows on all profiles (not just own profile).
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

$displayed_user_id = bp_displayed_user_id();
$is_own_profile = bp_is_my_profile();

// Get visitors (excluding the displayed user)
$count = 9; // 3x3 grid
$visitors = unmask_get_recent_visitors($count, $displayed_user_id);
?>

<section class="dossier-section dossier-section--visitors">
    <div class="dossier-section__head">
        <span class="dossier-section__title">visitors</span>
        <?php if (function_exists('bp_get_members_directory_permalink')) : ?>
            <a href="<?php echo esc_url(bp_get_members_directory_permalink()); ?>" class="dossier-action">view all</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($visitors)) : ?>
        <div class="dossier-visitors-grid">
            <?php foreach ($visitors as $visitor) :
                $user_id = $visitor->ID;

                // Get scene name
                $scene_name = '';
                if (function_exists('xprofile_get_field_data')) {
                    $scene_name = xprofile_get_field_data(3, $user_id);
                }
                if (empty($scene_name)) {
                    $scene_name = $visitor->display_name;
                }

                // Get designation
                $designation = function_exists('unmask_get_user_designation')
                    ? unmask_get_user_designation($user_id)
                    : 'V-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);

                // Get avatar
                $avatar_url = '';
                if (function_exists('bp_core_fetch_avatar')) {
                    $avatar_url = bp_core_fetch_avatar([
                        'item_id' => $user_id,
                        'type' => 'thumb',
                        'width' => 48,
                        'height' => 48,
                        'html' => false
                    ]);
                } else {
                    $avatar_url = get_avatar_url($user_id, ['size' => 48]);
                }

                // Profile URL
                $profile_url = function_exists('bp_core_get_user_domain')
                    ? bp_core_get_user_domain($user_id)
                    : get_author_posts_url($user_id);

                // Is drone?
                $is_drone = function_exists('unmask_user_is_drone')
                    ? unmask_user_is_drone($user_id)
                    : false;
            ?>
                <a href="<?php echo esc_url($profile_url); ?>"
                   class="dossier-visitor <?php echo $is_drone ? 'dossier-visitor--drone' : ''; ?>"
                   title="<?php echo esc_attr($scene_name); ?>">
                    <div class="dossier-visitor__avatar">
                        <?php if ($avatar_url) : ?>
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="">
                        <?php else : ?>
                            <span class="dossier-visitor__placeholder">&#9670;</span>
                        <?php endif; ?>
                    </div>
                    <span class="dossier-visitor__designation"><?php echo esc_html($designation); ?></span>
                    <span class="dossier-visitor__name"><?php echo esc_html($scene_name); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="dossier-empty">no visitors yet</div>
    <?php endif; ?>
</section>
