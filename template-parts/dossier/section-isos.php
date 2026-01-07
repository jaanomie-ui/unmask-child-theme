<?php
/**
 * Dossier Section: Active ISOs
 *
 * Displays user's active ISO listings with type, title, location, factory pref, expiration
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

$displayed_user_id = bp_displayed_user_id();
$is_own_profile = bp_is_my_profile();

$isos = unmask_get_user_isos($displayed_user_id);
?>

<section class="dossier-section">
    <div class="dossier-section__head">
        <span class="dossier-section__title">active ISOs</span>
        <?php if ($is_own_profile) : ?>
            <a href="<?php echo esc_url(home_url('/iso-board/')); ?>" class="dossier-action">post new</a>
        <?php endif; ?>
    </div>

    <?php if ($isos->have_posts()) : ?>
        <div class="dossier-iso-list">
            <?php while ($isos->have_posts()) : $isos->the_post();
                $iso_id = get_the_ID();
                $iso_type = get_field('iso_type', $iso_id) ?: get_post_meta($iso_id, 'iso_type', true);
                $iso_category = get_field('iso_category', $iso_id) ?: get_post_meta($iso_id, 'iso_category', true);
                $iso_location = get_field('iso_location', $iso_id) ?: get_post_meta($iso_id, 'iso_location', true);
                $iso_factory = get_field('iso_factory', $iso_id) ?: get_post_meta($iso_id, 'iso_factory', true);
                $iso_expiration = get_field('iso_expiration', $iso_id) ?: get_post_meta($iso_id, 'iso_expiration', true);
            ?>
                <div class="dossier-iso-row" data-iso-id="<?php echo esc_attr($iso_id); ?>">
                    <span class="dossier-iso__type dossier-iso__type--<?php echo esc_attr($iso_type); ?>">
                        <?php echo esc_html($iso_type); ?>
                    </span>

                    <span class="dossier-iso__title">
                        <?php the_title(); ?>
                    </span>

                    <span class="dossier-iso__loc">
                        <?php echo esc_html(ucfirst($iso_location ?: 'Chicago')); ?>
                    </span>

                    <?php if ($iso_factory === 'yes' || $iso_factory === 'preferred') : ?>
                        <span class="dossier-iso__tag">&#9670; Factory pref</span>
                    <?php else : ?>
                        <span class="dossier-iso__exp">
                            <?php if ($iso_expiration) : ?>
                                exp: <?php echo esc_html(unmask_format_date($iso_expiration)); ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>

                    <span class="dossier-iso__actions">
                        <?php if ($is_own_profile) : ?>
                            <button type="button" class="dossier-iso__expire" data-iso-id="<?php echo esc_attr($iso_id); ?>">expire</button>
                        <?php else : ?>
                            <?php if (function_exists('bp_get_send_private_message_link')) : ?>
                                <a href="<?php echo esc_url(add_query_arg('subject', urlencode('RE: ' . get_the_title()), bp_get_send_private_message_link())); ?>">respond</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <div class="dossier-empty">
            <?php if ($is_own_profile) : ?>
                no active ISOs — <a href="<?php echo esc_url(home_url('/iso-board/')); ?>">post one</a>
            <?php else : ?>
                no active ISOs
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</section>
