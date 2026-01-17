<?php
/**
 * Single Session Log Template
 *
 * Berkeley Mono terminal aesthetic for session log posts.
 * Uses design system tokens with blue/red accents.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    // Get post data
    $post_id = get_the_ID();
    $title = get_the_title();
    $date = get_the_date('Y.m.d');
    $content = get_the_content();

    // Extract exchange count from content (looking for "exchanges:" pattern)
    $exchange_count = 0;
    if (preg_match('/(\d+)\s+exchanges/i', $content, $matches)) {
        $exchange_count = $matches[1];
    }

    // Extract participants (looking for patterns in content)
    $participants = 'd001, Hive Mistress'; // Default

    // Get session number from title
    $session_number = '';
    if (preg_match('/Session Log (\d+)/i', $title, $matches)) {
        $session_number = str_pad($matches[1], 3, '0', STR_PAD_LEFT);
    }
    $file_id = $session_number ? 'SESS-' . $session_number : sprintf('SESS-%03d', $post_id);

    // Get protocol/state from content
    $protocol = 'active_training'; // Default

    // Get category for navigation
    $session_category = 130; // Session Logs category ID
?>

<div class="unmask-session-log">

    <!-- System Bar -->
    <div class="session-system-bar">
        <a href="<?php echo esc_url(get_category_link($session_category)); ?>" class="session-system-bar__back">← back to session logs</a>
        <span class="session-system-bar__center">UNMASK / session logs</span>
        <span class="session-system-bar__id"><?php echo esc_html($file_id); ?></span>
    </div>

    <!-- Hero Section -->
    <section class="session-hero">
        <div class="session-hero__container">
            <div class="session-hero__label">
                <span class="session-hero__status-dot"></span>
                <span>logged / archived</span>
            </div>

            <h1 class="session-hero__title"><?php echo esc_html($title); ?></h1>

            <!-- Metadata Grid -->
            <div class="session-meta-grid">
                <div class="session-meta-item">
                    <div class="session-meta-label">date</div>
                    <div class="session-meta-value"><?php echo esc_html($date); ?></div>
                </div>

                <?php if ($exchange_count > 0) : ?>
                <div class="session-meta-item">
                    <div class="session-meta-label">exchanges</div>
                    <div class="session-meta-value session-meta-value--highlight"><?php echo esc_html($exchange_count); ?></div>
                </div>
                <?php endif; ?>

                <div class="session-meta-item">
                    <div class="session-meta-label">participants</div>
                    <div class="session-meta-value"><?php echo esc_html($participants); ?></div>
                </div>

                <div class="session-meta-item">
                    <div class="session-meta-label">protocol</div>
                    <div class="session-meta-value"><?php echo esc_html($protocol); ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Article Body -->
    <article class="session-body">
        <div class="session-body__container">
            <div class="session-body__content">
                <?php
                // Apply WordPress content filters
                echo apply_filters('the_content', $content);
                ?>
            </div>
        </div>
    </article>

    <!-- Post Navigation -->
    <?php
    // Get adjacent posts within same category
    $prev_post = get_previous_post(true, '', 'category');
    $next_post = get_next_post(true, '', 'category');

    // If no previous, loop to newest in category
    if (!$prev_post) {
        $newest = get_posts([
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'category' => $session_category,
            'exclude' => [$post_id],
        ]);
        if ($newest) $prev_post = $newest[0];
    }

    // If no next, loop to oldest in category
    if (!$next_post) {
        $oldest = get_posts([
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'ASC',
            'category' => $session_category,
            'exclude' => [$post_id],
        ]);
        if ($oldest) $next_post = $oldest[0];
    }
    ?>
    <nav class="session-nav">
        <?php if ($prev_post) : ?>
            <a href="<?php echo get_permalink($prev_post); ?>" class="session-nav__link session-nav__link--prev">
                <span class="session-nav__label">previous session</span>
                <span class="session-nav__title"><?php echo get_the_title($prev_post); ?></span>
            </a>
        <?php endif; ?>

        <?php if ($next_post) : ?>
            <a href="<?php echo get_permalink($next_post); ?>" class="session-nav__link session-nav__link--next">
                <span class="session-nav__label">next session</span>
                <span class="session-nav__title"><?php echo get_the_title($next_post); ?></span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Related Sessions -->
    <?php
    // Get related posts from same category
    $related_args = [
        'post__not_in'   => [$post_id],
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'category__in'   => [$session_category],
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    $related_query = new WP_Query($related_args);

    if ($related_query->have_posts()) :
    ?>
    <section class="session-related">
        <header class="session-related__header">
            <span class="session-related__label">related sessions</span>
            <span class="session-related__count"><?php echo $related_query->found_posts; ?> in archive</span>
        </header>

        <div class="session-related__grid">
            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                <?php
                // Extract session number
                $related_session_number = '';
                if (preg_match('/Session Log (\d+)/i', get_the_title(), $matches)) {
                    $related_session_number = str_pad($matches[1], 3, '0', STR_PAD_LEFT);
                }
                $related_file_id = $related_session_number ? 'SESS-' . $related_session_number : sprintf('SESS-%03d', get_the_ID());

                // Extract exchange count
                $related_content = get_the_content();
                $related_exchanges = 0;
                if (preg_match('/(\d+)\s+exchanges/i', $related_content, $matches)) {
                    $related_exchanges = $matches[1];
                }
                $related_date = get_the_date('Y.m.d');
                ?>
                <a href="<?php the_permalink(); ?>" class="session-card">
                    <div class="session-card__id"><?php echo esc_html($related_file_id); ?></div>
                    <div class="session-card__title"><?php the_title(); ?></div>
                    <div class="session-card__meta"><?php echo esc_html($related_exchanges); ?> exchanges • <?php echo esc_html($related_date); ?></div>
                </a>
            <?php endwhile; ?>
        </div>
        <?php wp_reset_postdata(); ?>
    </section>
    <?php endif; ?>

</div>

<?php
endwhile;

get_footer();
