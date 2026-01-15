<?php
/**
 * Hard Limits Checklist Form Template Part
 *
 * Renders the traffic-light activity selection form.
 * Uses data from inc/hard-limits-data.php
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get categories data
$categories = unmask_get_limits_categories();
$total_activities = unmask_get_limits_activity_count();
?>

<div class="hard-limits-wrapper">

    <nav class="hard-limits-breadcrumb">
        <a href="/drone-dashboard/">← drone dashboard</a>
    </nav>

    <header class="hard-limits-header">
        <h1>hard limits</h1>
        <p class="hard-limits-intro">
            define boundaries. green means yes, yellow means maybe, red means no.
            limits visible to connections only.
        </p>
    </header>

    <div class="hard-limits-legend">
        <div class="legend-item">
            <span class="legend-dot green"></span>
            <span>yes / into it</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot yellow"></span>
            <span>maybe / negotiate</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot red"></span>
            <span>hard no</span>
        </div>
    </div>

    <div class="hard-limits-categories">
        <?php foreach ($categories as $cat_slug => $category) : ?>
            <section class="limits-category" data-category="<?php echo esc_attr($cat_slug); ?>">
                <header class="category-header">
                    <span class="category-toggle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </span>
                    <h2><?php echo esc_html($category['label']); ?></h2>
                    <span class="category-count">0/<?php echo count($category['activities']); ?></span>
                </header>

                <div class="category-body">
                    <div class="activities-list">
                        <?php foreach ($category['activities'] as $activity_slug => $activity_label) : ?>
                            <div class="activity-row" data-activity="<?php echo esc_attr($activity_slug); ?>">
                                <span class="activity-label"><?php echo esc_html($activity_label); ?></span>
                                <div class="traffic-light-group">
                                    <button type="button"
                                            class="traffic-btn btn-green"
                                            data-level="green"
                                            aria-label="<?php echo esc_attr($activity_label); ?> - Yes">
                                    </button>
                                    <button type="button"
                                            class="traffic-btn btn-yellow"
                                            data-level="yellow"
                                            aria-label="<?php echo esc_attr($activity_label); ?> - Maybe">
                                    </button>
                                    <button type="button"
                                            class="traffic-btn btn-red"
                                            data-level="red"
                                            aria-label="<?php echo esc_attr($activity_label); ?> - No">
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <div class="hard-limits-actions">
        <div class="hard-limits-actions-inner">
            <div class="limits-summary">
                <div class="summary-item">
                    <span class="summary-count green">0</span>
                    <span>yes</span>
                </div>
                <div class="summary-item">
                    <span class="summary-count yellow">0</span>
                    <span>maybe</span>
                </div>
                <div class="summary-item">
                    <span class="summary-count red">0</span>
                    <span>no</span>
                </div>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn-clear-limits">clear</button>
                <?php if (is_user_logged_in()) : ?>
                    <button type="button" class="btn-save-limits">file limits</button>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="btn-save-limits">
                        sign in to file
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
