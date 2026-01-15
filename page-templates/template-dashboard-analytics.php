<?php
/**
 * Template Name: Analytics Dashboard
 *
 * Terminal-style cockpit dashboard for viewing site metrics.
 * Data populated via n8n → WordPress REST API.
 * Access restricted to administrators.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Restrict access to admins
if (!current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}

// Get stored analytics data (populated by n8n)
$analytics = get_option('unmask_analytics_data', []);
$last_updated = get_option('unmask_analytics_updated', 'never');

// Default values if no data
$defaults = [
    'visitors' => 0,
    'bounce_rate' => 0,
    'pages_per_session' => 0,
    'registrations' => 0,
    'registration_rate' => 0,
    'registrations_change' => 0,
    'profile_completions' => 0,
    'profile_completion_rate' => 0,
    'profile_change' => 0,
    'trend_visitors' => [],
    'trend_registrations' => [],
    'ab_tests' => [],
];

$data = wp_parse_args($analytics, $defaults);

// Format trend bars (7 days of data → ASCII bars)
function unmask_trend_bars($values, $max_bars = 14) {
    if (empty($values)) {
        return str_repeat('▁', $max_bars);
    }

    $max = max($values) ?: 1;
    $chars = ['▁', '▂', '▃', '▄', '▅', '▆', '▇', '█'];
    $bars = '';

    foreach (array_slice($values, -$max_bars) as $val) {
        $index = min(7, floor(($val / $max) * 7));
        $bars .= $chars[$index];
    }

    return str_pad($bars, $max_bars, '▁', STR_PAD_LEFT);
}

// Format change indicator
function unmask_change_indicator($change) {
    if ($change > 0) {
        return '<span class="dash__change dash__change--up">▲ +' . abs($change) . '%</span>';
    } elseif ($change < 0) {
        return '<span class="dash__change dash__change--down">▼ ' . abs($change) . '%</span>';
    }
    return '<span class="dash__change">— 0%</span>';
}

get_header('fullbleed');
?>

<div class="dashboard-analytics">
    <header class="dash__header">
        <div class="dash__title">
            <span class="dash__title-text">UNMASK // SYSTEM METRICS</span>
        </div>
        <div class="dash__meta">
            <span class="dash__status dash__status--live">● live</span>
            <span class="dash__date"><?php echo esc_html(date('Y.m.d')); ?></span>
        </div>
    </header>

    <main class="dash__grid">
        <!-- Column 1: Discovery -->
        <section class="dash__panel">
            <h2 class="dash__panel-title">DISCOVERY</h2>
            <div class="dash__metrics">
                <div class="dash__metric">
                    <span class="dash__metric-label">visitors</span>
                    <span class="dash__metric-value"><?php echo number_format($data['visitors']); ?></span>
                </div>
                <div class="dash__metric">
                    <span class="dash__metric-label">bounce</span>
                    <span class="dash__metric-value"><?php echo number_format($data['bounce_rate'], 1); ?>%</span>
                </div>
                <div class="dash__metric">
                    <span class="dash__metric-label">pages/sess</span>
                    <span class="dash__metric-value"><?php echo number_format($data['pages_per_session'], 1); ?></span>
                </div>
            </div>
        </section>

        <!-- Column 2: Entry -->
        <section class="dash__panel">
            <h2 class="dash__panel-title">ENTRY</h2>
            <div class="dash__metrics">
                <div class="dash__metric">
                    <span class="dash__metric-label">registrations</span>
                    <span class="dash__metric-value"><?php echo number_format($data['registrations']); ?></span>
                </div>
                <div class="dash__metric">
                    <span class="dash__metric-label">rate</span>
                    <span class="dash__metric-value"><?php echo number_format($data['registration_rate'], 1); ?>%</span>
                </div>
                <div class="dash__metric">
                    <span class="dash__metric-label">vs last week</span>
                    <?php echo unmask_change_indicator($data['registrations_change']); ?>
                </div>
            </div>
        </section>

        <!-- Column 3: Activation -->
        <section class="dash__panel">
            <h2 class="dash__panel-title">ACTIVATION</h2>
            <div class="dash__metrics">
                <div class="dash__metric">
                    <span class="dash__metric-label">profiles</span>
                    <span class="dash__metric-value"><?php echo number_format($data['profile_completion_rate']); ?>%</span>
                </div>
                <div class="dash__metric">
                    <span class="dash__metric-label">complete</span>
                    <span class="dash__metric-value"><?php echo number_format($data['profile_completions']); ?></span>
                </div>
                <div class="dash__metric">
                    <span class="dash__metric-label">vs last week</span>
                    <?php echo unmask_change_indicator($data['profile_change']); ?>
                </div>
            </div>
        </section>
    </main>

    <!-- A/B Test Status -->
    <section class="dash__section">
        <h2 class="dash__section-title">A/B STATUS</h2>
        <div class="dash__ab-tests">
            <?php if (!empty($data['ab_tests'])) : ?>
                <?php foreach ($data['ab_tests'] as $test) : ?>
                    <div class="dash__ab-test">
                        <span class="dash__ab-name"><?php echo esc_html($test['name']); ?>:</span>
                        <span class="dash__ab-result <?php echo esc_attr($test['status']); ?>">
                            <?php echo esc_html($test['result']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="dash__ab-test">
                    <span class="dash__ab-name">homepage_v2:</span>
                    <span class="dash__ab-result">collecting data...</span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Trend Charts -->
    <section class="dash__section">
        <h2 class="dash__section-title">TREND // 7 DAY</h2>
        <div class="dash__trends">
            <div class="dash__trend">
                <span class="dash__trend-bars"><?php echo esc_html(unmask_trend_bars($data['trend_visitors'])); ?></span>
                <span class="dash__trend-label">visitors</span>
            </div>
            <div class="dash__trend">
                <span class="dash__trend-bars"><?php echo esc_html(unmask_trend_bars($data['trend_registrations'])); ?></span>
                <span class="dash__trend-label">registrations</span>
            </div>
        </div>
    </section>

    <footer class="dash__footer">
        <span class="dash__updated">last sync: <?php echo esc_html($last_updated); ?></span>
        <span class="dash__refresh" data-refresh-interval="300">auto-refresh: 5m</span>
    </footer>
</div>

<script>
// Auto-refresh every 5 minutes
setTimeout(function() {
    window.location.reload();
}, 300000);
</script>

<?php
get_footer('fullbleed');
