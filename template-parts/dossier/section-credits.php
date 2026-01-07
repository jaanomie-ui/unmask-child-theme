<?php
/**
 * Dossier Section: Magazine Credits
 *
 * Displays user's credits with role, title (linked), issue
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

$displayed_user_id = bp_displayed_user_id();
$is_own_profile = bp_is_my_profile();

$credits = unmask_get_user_credits($displayed_user_id, 10);
?>

<section class="dossier-section">
    <div class="dossier-section__head">
        <span class="dossier-section__title">credits</span>
    </div>

    <?php if (!empty($credits)) : ?>
        <div class="dossier-credits-list">
            <?php foreach ($credits as $credit) : ?>
                <div class="dossier-credit-row">
                    <span class="dossier-credit__role"><?php echo esc_html($credit['role']); ?></span>
                    <span class="dossier-credit__title">
                        <a href="<?php echo esc_url($credit['url']); ?>">
                            <?php echo esc_html($credit['title']); ?>
                        </a>
                    </span>
                    <span class="dossier-credit__issue">issue <?php echo esc_html($credit['issue']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="dossier-empty">
            no magazine credits yet
        </div>
    <?php endif; ?>
</section>
