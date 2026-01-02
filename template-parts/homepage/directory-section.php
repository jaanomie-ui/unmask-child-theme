<?php
/**
 * UNMASK Directory Section
 *
 * 4-column grid of directory links to main site areas.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Directory items - adjust URLs as needed
$directory_items = [
    [
        'label' => 'studio',
        'value' => 'the factory',
        'meta'  => 'back of the yards',
        'href'  => '/studio/',
    ],
    [
        'label' => 'community',
        'value' => 'iso board',
        'meta'  => '8 active listings',
        'href'  => '/iso/',
    ],
    [
        'label' => 'events',
        'value' => 'pink panthers',
        'meta'  => 'next: tba',
        'href'  => '/events/',
    ],
    [
        'label' => 'map',
        'value' => 'the web',
        'meta'  => 'explore connections',
        'href'  => '/map/',
    ],
];
?>

<section class="unmask-directory">
    <header class="unmask-section-header">
        <span class="unmask-section-header__title">directory</span>
    </header>

    <div class="unmask-directory__grid">
        <?php foreach ($directory_items as $item) : ?>
            <a href="<?php echo esc_url($item['href']); ?>" class="unmask-directory__cell">
                <span class="unmask-directory__label"><?php echo esc_html($item['label']); ?></span>
                <span class="unmask-directory__value"><?php echo esc_html($item['value']); ?></span>
                <span class="unmask-directory__meta"><?php echo esc_html($item['meta']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
