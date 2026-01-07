<?php
/**
 * Dossier Section: Creative Practice
 *
 * xProfile fields used:
 *   - What do you practice? (multiselectbox)
 *   - Portfolio (url)
 *
 * Visibility toggle: User/Visitor/Interlinked (owner can edit on own profile)
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

$displayed_user_id = bp_displayed_user_id();
$current_user_id = get_current_user_id();
$is_own_profile = bp_is_my_profile();

// Check visibility permissions
$can_view = unmask_can_view_section($displayed_user_id, $current_user_id, 'practice');
$current_visibility = unmask_get_section_visibility($displayed_user_id, 'practice');

$practice = unmask_get_creative_practice($displayed_user_id);

// Clean portfolio URL early for content check
$clean_portfolio = '';
if ($practice && !empty($practice['portfolio'])) {
    $clean_portfolio = $practice['portfolio'];
    if (strpos($clean_portfolio, '<a ') !== false) {
        preg_match('/href=["\']([^"\']+)["\']/', $clean_portfolio, $matches);
        $clean_portfolio = isset($matches[1]) ? $matches[1] : '';
    }
    $clean_portfolio = trim(strip_tags($clean_portfolio));
    if (!filter_var($clean_portfolio, FILTER_VALIDATE_URL)) {
        $clean_portfolio = '';
    }
}

// Only show if there's at least one field populated
$has_content = $practice && (!empty($practice['practices']) || !empty($clean_portfolio));

// Don't render section if viewer cannot see it and there's content
if (!$can_view && $has_content && !$is_own_profile) {
    return;
}
?>

<section class="dossier-section dossier-section--practice">
    <div class="dossier-section__head">
        <span class="dossier-section__title">creative practice</span>
        <?php if ($is_own_profile) : ?>
            <?php
            // Visibility toggle for own profile
            get_template_part('template-parts/components/visibility-toggle', null, [
                'section_id' => 'practice',
                'current' => $current_visibility,
                'size' => 'compact'
            ]);
            ?>
            <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'profile/edit/group/2/'); ?>" class="dossier-action">edit</a>
        <?php endif; ?>
    </div>

    <?php if ($has_content) : ?>
        <div class="dossier-data-list">
            <?php if (!empty($practice['practices'])) : ?>
                <div class="dossier-data-row">
                    <span class="dossier-data__key">practices</span>
                    <span class="dossier-data__val">
                        <?php echo esc_html(is_array($practice['practices']) ? implode(', ', $practice['practices']) : $practice['practices']); ?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if (!empty($clean_portfolio)) : ?>
                <div class="dossier-data-row">
                    <span class="dossier-data__key">portfolio</span>
                    <span class="dossier-data__val">
                        <a href="<?php echo esc_url($clean_portfolio); ?>" target="_blank" rel="noopener">
                            <?php echo esc_html(parse_url($clean_portfolio, PHP_URL_HOST) ?: $clean_portfolio); ?>
                        </a>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <div class="dossier-empty">
            <?php if ($is_own_profile) : ?>
                no creative practice info — <a href="<?php echo esc_url(bp_loggedin_user_domain() . 'profile/edit/group/2/'); ?>">add some</a>
            <?php else : ?>
                no creative practice info
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
