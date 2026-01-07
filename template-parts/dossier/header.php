<?php
/**
 * Dossier Header Template
 *
 * Displays: Avatar, name, designation, location, functions, status, bio, actions
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

$displayed_user_id = bp_displayed_user_id();
$is_own_profile = bp_is_my_profile();

// Get profile data
$basics = unmask_get_profile_basics($displayed_user_id);
$member_type = unmask_get_member_type($displayed_user_id);
$designation = unmask_get_designation($displayed_user_id);
$designation_color = unmask_get_designation_color_class($displayed_user_id);
$functions = unmask_get_user_functions($displayed_user_id);
$status = unmask_get_user_status($displayed_user_id);
$avatar_url = unmask_get_avatar_url($displayed_user_id, 100);
?>

<?php if ($is_own_profile) : ?>
<!-- Preview Mode Bar (hidden by default) -->
<div class="dossier-preview-bar" id="preview-bar" hidden>
    <span class="dossier-preview-bar__label">previewing as:</span>
    <div class="dossier-preview-bar__options">
        <button type="button" class="dossier-preview-option" data-preview-level="visitor">
            <span class="dossier-preview-option__dot dossier-preview-option__dot--red"></span>
            visitor
        </button>
        <button type="button" class="dossier-preview-option" data-preview-level="user">
            <span class="dossier-preview-option__dot dossier-preview-option__dot--yellow"></span>
            logged-in
        </button>
        <button type="button" class="dossier-preview-option" data-preview-level="interlinked">
            <span class="dossier-preview-option__dot dossier-preview-option__dot--green"></span>
            interlinked
        </button>
    </div>
    <button type="button" class="dossier-preview-bar__close" id="preview-close">exit preview</button>
</div>
<?php endif; ?>

<header class="dossier-header">
    <div class="dossier-avatar">
        <?php if ($avatar_url) : ?>
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($basics['display_name']); ?>">
        <?php else : ?>
            <?php echo esc_html(strtoupper(substr($basics['display_name'], 0, 1))); ?>
        <?php endif; ?>

        <?php if ($is_own_profile) : ?>
            <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'profile/change-avatar/'); ?>" class="dossier-avatar__edit">
                [change]
            </a>
        <?php endif; ?>
    </div>

    <div class="dossier-identity">
        <!-- Name -->
        <div class="dossier-line">
            <span class="dossier-line__value dossier-line__value--name">
                <?php echo esc_html($basics['display_name']); ?>
            </span>
        </div>

        <!-- Designation -->
        <div class="dossier-line">
            <span class="dossier-line__key">designation</span>
            <span class="dossier-line__value designation <?php echo esc_attr($designation_color); ?> <?php echo $member_type === 'drone' ? 'designation--drone' : ''; ?>">
                <?php echo esc_html($designation); ?>
            </span>
        </div>

        <!-- Location -->
        <?php if (!empty($basics['city']) || !empty($basics['pronouns'])) : ?>
        <div class="dossier-line">
            <span class="dossier-line__key">location</span>
            <span class="dossier-line__value">
                <?php
                $location_parts = [];
                if (!empty($basics['city'])) $location_parts[] = $basics['city'];
                if (!empty($basics['pronouns'])) $location_parts[] = $basics['pronouns'];
                echo esc_html(implode(' · ', $location_parts));
                ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Functions -->
        <?php if (!empty($functions)) : ?>
        <div class="dossier-line">
            <span class="dossier-line__key">functions</span>
            <span class="dossier-fn-list">
                <?php foreach ($functions as $func) : ?>
                    <span class="dossier-fn-item"><?php echo esc_html(trim($func)); ?></span>
                <?php endforeach; ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Status -->
        <?php if ($status['bookable'] || $status['available_photographed'] || $status['available_photograph']) : ?>
        <div class="dossier-line">
            <span class="dossier-line__key">status</span>
            <span class="dossier-status-list">
                <?php if ($status['bookable']) : ?>
                <span class="dossier-status-item">
                    <span class="dossier-status-dot dossier-status-dot--red"></span>
                    <span class="dossier-status-label--red">open to bookings</span>
                </span>
                <?php endif; ?>
                <?php if ($status['available_photographed']) : ?>
                <span class="dossier-status-item">
                    <span class="dossier-status-dot dossier-status-dot--blue"></span>
                    <span class="dossier-status-label--blue">available to be photographed</span>
                </span>
                <?php endif; ?>
                <?php if ($status['available_photograph']) : ?>
                <span class="dossier-status-item">
                    <span class="dossier-status-dot dossier-status-dot--green"></span>
                    <span class="dossier-status-label--green">available to photograph</span>
                </span>
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Bio -->
        <?php if (!empty($basics['bio'])) : ?>
        <p class="dossier-bio">
            <?php echo esc_html($basics['bio']); ?>
        </p>
        <?php endif; ?>

        <!-- Actions -->
        <div class="dossier-actions">
            <?php if ($is_own_profile) : ?>
                <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'profile/edit/'); ?>" class="dossier-action">edit dossier</a>
                <button type="button" class="dossier-action dossier-action--secondary" id="preview-toggle" data-preview-active="false">
                    preview as visitor
                </button>
            <?php else : ?>
                <?php if (function_exists('bp_send_private_message_link')) : ?>
                    <a href="<?php echo esc_url(bp_get_send_private_message_link()); ?>" class="dossier-action dossier-action--primary">message</a>
                <?php endif; ?>
                <?php if (function_exists('bp_add_friend_button')) : ?>
                    <?php bp_add_friend_button($displayed_user_id); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</header>
