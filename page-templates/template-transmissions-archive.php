<?php
/**
 * Template Name: Transmissions Archive
 *
 * Archive page for d001 session logs / transmissions.
 * Shows all training session logs in chronological order.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get current user ID
$current_user_id = get_current_user_id();

// Query session logs for current user (category 112 = d001-training-logs)
$session_logs = get_posts(array(
    'post_type' => 'post',
    'category' => 112,
    'author' => $current_user_id,
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'ASC',
    'post_status' => 'publish'
));

?>

<div id="primary" class="content-area transmissions-archive">
    <main id="main" class="site-main">

        <div class="transmissions-archive__container">

            <!-- Header -->
            <header class="transmissions-archive__header">
                <h1 class="transmissions-archive__title">TRANSMISSIONS ARCHIVE</h1>
                <div class="transmissions-archive__subtitle">
                    d001 Training Session Logs
                </div>
                <div class="transmissions-archive__count">
                    <?php echo count($session_logs); ?> sessions logged
                </div>
            </header>

            <!-- Session List -->
            <?php if (!empty($session_logs)) : ?>
                <div class="transmissions-archive__list">
                    <?php foreach ($session_logs as $log) :
                        $log_date = get_the_date('Y-m-d H:i', $log);
                        $log_url = get_permalink($log);
                        $excerpt = has_excerpt($log) ? get_the_excerpt($log) : wp_trim_words(get_the_content(null, false, $log), 30, '...');
                    ?>
                    <article class="transmissions-archive__item">
                        <div class="transmissions-archive__item-header">
                            <h2 class="transmissions-archive__item-title">
                                <a href="<?php echo esc_url($log_url); ?>">
                                    <?php echo esc_html(get_the_title($log)); ?>
                                </a>
                            </h2>
                            <time class="transmissions-archive__item-date" datetime="<?php echo esc_attr($log_date); ?>">
                                <?php echo esc_html($log_date); ?>
                            </time>
                        </div>
                        <div class="transmissions-archive__item-excerpt">
                            <?php echo wp_kses_post($excerpt); ?>
                        </div>
                        <a href="<?php echo esc_url($log_url); ?>" class="transmissions-archive__item-link">
                            VIEW FULL TRANSMISSION →
                        </a>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="transmissions-archive__empty">
                    <p>No transmission logs found.</p>
                    <p>Session logs will appear here after Hive Mistress conditioning sessions.</p>
                </div>
            <?php endif; ?>

            <!-- Back to Dashboard -->
            <div class="transmissions-archive__footer">
                <a href="<?php echo esc_url(home_url('/drone-dashboard/')); ?>" class="transmissions-archive__back-link">
                    ← RETURN TO DASHBOARD
                </a>
            </div>

        </div>

    </main>
</div>

<?php
get_footer();
