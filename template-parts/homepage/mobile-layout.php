<?php
/**
 * UNMASK Mobile Homepage Layout
 *
 * Vertical feed layout with carousel for mobile devices.
 * Includes sticky bottom navigation.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user info
$current_user_id = get_current_user_id();
$user_designation = '';

if ($current_user_id && function_exists('unmask_get_user_designation')) {
    $user_designation = unmask_get_user_designation($current_user_id);
} elseif ($current_user_id) {
    $user_designation = 'USER-' . $current_user_id;
}

// Get records for carousel
$records_query = new WP_Query([
    'posts_per_page' => 5,
    'post_status'    => 'publish',
    'category__not_in' => [get_cat_ID('d001')],
    'meta_query'     => [
        [
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS',
        ],
    ],
]);

$records_count = $records_query->found_posts;
?>

<div class="unmask-mobile">

    <!-- System Bar -->
    <div class="unmask-mobile__system-bar">
        <div class="unmask-mobile__status">
            <span class="unmask-mobile__status-dot"></span>
            <span>sys.online</span>
        </div>
        <span><?php echo esc_html($records_count); ?> watching</span>
    </div>

    <!-- Site Header -->
    <header class="unmask-mobile__header">
        <div class="unmask-mobile__logo">UNMASK</div>
        <div class="unmask-mobile__tagline">documentation over news</div>
    </header>

    <!-- Welcome Module -->
    <?php if (is_user_logged_in()) : ?>
        <div class="unmask-mobile__welcome">
            <div>
                <div class="unmask-mobile__welcome-label">session</div>
                <div class="unmask-mobile__welcome-msg">
                    welcome, <span class="unmask-mobile__designation"><?php echo esc_html($user_designation); ?></span>
                </div>
            </div>
            <div class="unmask-mobile__viewing">3 viewing</div>
        </div>
    <?php endif; ?>

    <!-- Records Carousel -->
    <section class="unmask-mobile__section">
        <div class="unmask-mobile__section-header">
            <span class="unmask-mobile__section-title">latest records</span>
            <span class="unmask-mobile__section-meta"><?php echo esc_html($records_count); ?> entries</span>
        </div>

        <?php if ($records_query->have_posts()) : ?>
            <div class="unmask-mobile__carousel" role="region" aria-label="Latest records carousel">
                <?php $slide_index = 0; ?>
                <?php while ($records_query->have_posts()) : $records_query->the_post(); ?>
                    <?php
                    $file_id = get_post_meta(get_the_ID(), 'unmask_file_id', true) ?: sprintf('UM-%03d', get_the_ID());
                    $record_type = get_post_meta(get_the_ID(), 'unmask_type', true) ?: 'record';
                    ?>
                    <article class="unmask-mobile__card" data-slide="<?php echo $slide_index; ?>">
                        <a href="<?php the_permalink(); ?>" class="unmask-mobile__card-link">
                            <div class="unmask-mobile__card-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('large', [
                                        'class'   => 'unmask-mobile__card-img',
                                        'loading' => $slide_index === 0 ? 'eager' : 'lazy',
                                    ]); ?>
                                <?php endif; ?>
                            </div>
                            <div class="unmask-mobile__card-meta">
                                <div class="unmask-mobile__card-info">
                                    <div class="unmask-mobile__card-id"><?php echo esc_html($file_id); ?></div>
                                    <div class="unmask-mobile__card-subject"><?php the_title(); ?></div>
                                    <div class="unmask-mobile__card-type"><?php echo esc_html($record_type); ?></div>
                                </div>
                                <div class="unmask-mobile__card-date"><?php echo esc_html(strtolower(get_the_date('M Y'))); ?></div>
                            </div>
                        </a>
                    </article>
                    <?php $slide_index++; ?>
                <?php endwhile; ?>
            </div>

            <!-- Carousel Indicators -->
            <div class="unmask-mobile__carousel-dots">
                <?php for ($i = 0; $i < min($slide_index, 5); $i++) : ?>
                    <span class="unmask-mobile__dot<?php echo $i === 0 ? ' unmask-mobile__dot--active' : ''; ?>" data-slide="<?php echo $i; ?>"></span>
                <?php endfor; ?>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
    </section>

    <!-- Activity Section -->
    <section class="unmask-mobile__section">
        <div class="unmask-mobile__section-header">
            <span class="unmask-mobile__section-title">activity</span>
            <span class="unmask-mobile__section-meta">live</span>
        </div>
        <div class="unmask-mobile__feed">
            <div class="unmask-mobile__feed-item">
                <span class="unmask-mobile__feed-action">
                    <span class="unmask-mobile__feed-user">USER-3201</span> viewed <span class="unmask-mobile__feed-target">Magnus</span>
                </span>
                <span class="unmask-mobile__feed-time">2m ago</span>
            </div>
            <div class="unmask-mobile__feed-item">
                <span class="unmask-mobile__feed-action">
                    <span class="unmask-mobile__feed-user">D-012</span> posted ISO
                </span>
                <span class="unmask-mobile__feed-time">8m ago</span>
            </div>
            <div class="unmask-mobile__feed-item">
                <span class="unmask-mobile__feed-action">
                    <span class="unmask-mobile__feed-user">USER-4511</span> viewed <span class="unmask-mobile__feed-target">Velvet</span>
                </span>
                <span class="unmask-mobile__feed-time">12m ago</span>
            </div>
        </div>
    </section>

    <!-- Directory Section -->
    <section class="unmask-mobile__section">
        <div class="unmask-mobile__section-header">
            <span class="unmask-mobile__section-title">directory</span>
        </div>
        <div class="unmask-mobile__data-grid">
            <a href="/archive/" class="unmask-mobile__data-cell">
                <span class="unmask-mobile__cell-label">magazine</span>
                <span class="unmask-mobile__cell-value">the archive</span>
                <span class="unmask-mobile__cell-meta"><span class="unmask-mobile__num"><?php echo esc_html($records_count); ?></span> records</span>
            </a>
            <a href="/studio/" class="unmask-mobile__data-cell">
                <span class="unmask-mobile__cell-label">studio</span>
                <span class="unmask-mobile__cell-value">the factory</span>
                <span class="unmask-mobile__cell-meta">back of the yards</span>
            </a>
            <a href="/iso/" class="unmask-mobile__data-cell">
                <span class="unmask-mobile__cell-label">community</span>
                <span class="unmask-mobile__cell-value">iso board</span>
                <span class="unmask-mobile__cell-meta"><span class="unmask-mobile__num">8</span> active</span>
            </a>
            <a href="/events/" class="unmask-mobile__data-cell">
                <span class="unmask-mobile__cell-label">events</span>
                <span class="unmask-mobile__cell-value">pink panthers</span>
                <span class="unmask-mobile__cell-meta">next: tba</span>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="unmask-mobile__footer">
        <p>unmask is a queer documentation project</p>
        <p>chicago, illinois · est. 2024</p>
        <p class="unmask-mobile__footer-links">
            <a href="/about/">about</a> · <a href="/privacy/">privacy</a>
        </p>
    </footer>

    <!-- Spacer for bottom nav -->
    <div class="unmask-mobile__nav-spacer"></div>

    <!-- Sticky Bottom Navigation -->
    <nav class="unmask-mobile__nav" aria-label="Main navigation">
        <a href="/" class="unmask-mobile__nav-item unmask-mobile__nav-item--active">
            <span class="unmask-mobile__nav-label">home</span>
        </a>
        <a href="/archive/" class="unmask-mobile__nav-item">
            <span class="unmask-mobile__nav-label">records</span>
        </a>
        <a href="/iso/" class="unmask-mobile__nav-item">
            <span class="unmask-mobile__nav-label">iso</span>
        </a>
        <a href="/members/me/" class="unmask-mobile__nav-item">
            <span class="unmask-mobile__nav-label">dossier</span>
        </a>
    </nav>

</div>
