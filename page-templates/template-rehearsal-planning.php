<?php
/**
 * Template Name: Rehearsal Planning
 * Description: Sequence 4 rehearsal coordination form for material world preparation
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user ID
$current_user_id = get_current_user_id();

// Get saved rehearsal data
$rehearsal_data = get_user_meta($current_user_id, 'hm_rehearsal_planning', true);
if (!is_array($rehearsal_data)) {
    $rehearsal_data = array(
        'selected_dates' => array(),
        'gear_bringing' => array(),
        'notes' => ''
    );
}

// Get available dates from options (admin-configurable)
$available_dates = get_option('hm_rehearsal_dates', array(
    '2026-02-24' => 'February 24, 2026 (Tuesday)',
    '2026-02-26' => 'February 26, 2026 (Thursday)',
    '2026-03-03' => 'March 3, 2026 (Tuesday)',
    '2026-03-05' => 'March 5, 2026 (Thursday)',
));

// Define gear checklist
$gear_items = array(
    'cage' => 'Chastity Cage',
    'bit' => 'Bit Gag',
    'harness' => 'Body Harness',
    'tail' => 'Tail Plug',
    'hooves' => 'Hoof Mitts / Boots',
    'blinders' => 'Blinders / Hood',
    'collar' => 'Collar',
    'leash' => 'Leash',
    'bridle' => 'Bridle',
    'other' => 'Other (specify in notes)'
);

get_header();
?>

<main class="rehearsal-planning-page">

    <div class="rehearsal-planning-wrapper">

        <nav class="rehearsal-breadcrumb">
            <a href="/drone-dashboard/">← drone dashboard</a>
        </nav>

        <header class="rehearsal-header">
            <h1>rehearsal planning</h1>
            <p class="rehearsal-intro">
                sequence 4: material world preparation<br>
                coordinate rehearsal dates and gear with drone handler (drone 22)
            </p>
        </header>

        <!-- AVAILABLE DATES -->
        <section class="rehearsal-section">
            <h2 class="section-title">available rehearsal dates</h2>
            <p class="section-intro">Two preliminary rehearsal days are required before the Pink Panthers nightclub performance. Select all dates that work for you. Drone handler will finalize schedule.</p>

            <div class="dates-list">
                <?php foreach ($available_dates as $date_key => $date_label) :
                    $is_selected = in_array($date_key, $rehearsal_data['selected_dates']);
                ?>
                <div class="date-item <?php echo $is_selected ? 'selected' : ''; ?>" data-date="<?php echo esc_attr($date_key); ?>">
                    <div class="date-checkbox">
                        <input type="checkbox"
                               id="date-<?php echo esc_attr($date_key); ?>"
                               value="<?php echo esc_attr($date_key); ?>"
                               <?php checked($is_selected); ?>>
                        <label for="date-<?php echo esc_attr($date_key); ?>">
                            <span class="checkbox-box"></span>
                        </label>
                    </div>
                    <label for="date-<?php echo esc_attr($date_key); ?>" class="date-label">
                        <?php echo esc_html($date_label); ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- GEAR CHECKLIST -->
        <section class="rehearsal-section">
            <h2 class="section-title">gear bringing</h2>
            <p class="section-intro">Select all gear you will bring to rehearsal.</p>

            <div class="gear-list">
                <?php foreach ($gear_items as $gear_key => $gear_label) :
                    $is_checked = in_array($gear_key, $rehearsal_data['gear_bringing']);
                ?>
                <div class="gear-item <?php echo $is_checked ? 'checked' : ''; ?>" data-gear="<?php echo esc_attr($gear_key); ?>">
                    <div class="gear-checkbox">
                        <input type="checkbox"
                               id="gear-<?php echo esc_attr($gear_key); ?>"
                               value="<?php echo esc_attr($gear_key); ?>"
                               <?php checked($is_checked); ?>>
                        <label for="gear-<?php echo esc_attr($gear_key); ?>">
                            <span class="checkbox-box"></span>
                        </label>
                    </div>
                    <label for="gear-<?php echo esc_attr($gear_key); ?>" class="gear-label">
                        <?php echo esc_html($gear_label); ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ADDITIONAL NOTES -->
        <section class="rehearsal-section">
            <h2 class="section-title">additional notes</h2>
            <p class="section-intro">Logistics, questions, or special considerations for Drone Handler.</p>

            <textarea id="rehearsal-notes"
                      class="rehearsal-notes"
                      rows="6"
                      placeholder="Additional gear you would like to bring..."><?php echo esc_textarea($rehearsal_data['notes']); ?></textarea>
        </section>

        <!-- ACTIONS -->
        <div class="rehearsal-actions">
            <div class="rehearsal-actions-inner">
                <div class="rehearsal-summary">
                    <div class="summary-item">
                        <span class="summary-count dates-count"><?php echo count($rehearsal_data['selected_dates']); ?></span>
                        <span>dates selected</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-count gear-count"><?php echo count($rehearsal_data['gear_bringing']); ?></span>
                        <span>gear items</span>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-clear-rehearsal">clear</button>
                    <?php if (is_user_logged_in()) : ?>
                        <button type="button" class="btn-save-rehearsal">submit plan</button>
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="btn-save-rehearsal">
                            sign in to submit
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</main>

<?php get_footer(); ?>
