<?php
/**
 * Template Name: Hive Mistress Chat
 *
 * Full-width chat interface for the Hive Mistress AI conditioning system.
 * Hides sidebar completely and provides proper layout structure.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user context
$current_user_id = get_current_user_id();

// Get context data for header
$context = array(
    'sequence_num' => 1,
    'sequence_title' => 'PROTOCOL INSTALLATION',
    'current_loop' => 'third_person_reference',
    'loops_installed' => 0,
    'loops_total' => 3,
    'deployment_days' => '??'
);

// Try to get real context from state system
if (function_exists('hm_get_chat_header_context')) {
    $context = hm_get_chat_header_context($current_user_id);
}

// Calculate countdown to Pink Panthers Nightclub
$target_date = new DateTime(HM_DEPLOYMENT_DATE);
$today = new DateTime();
$interval = $today->diff($target_date);
$context['deployment_days'] = $interval->days;

// Format loop name for display
$loop_display = str_replace('_', ' ', $context['current_loop'] ?? 'awaiting');

get_header();
?>

<div class="hm-chat-wrapper">
    <!-- Navigation -->
    <nav class="hm-nav">
        <a href="<?php echo esc_url(home_url('/drone-dashboard/')); ?>" class="hm-nav__back">&larr; Dashboard</a>
        <span class="hm-nav__status">Conditioning Active</span>
    </nav>

    <!-- Status Bar / Context Header -->
    <div class="hm-context">
        <div class="hm-context__item">
            <span class="hm-context__label">Sequence <?php echo esc_html($context['sequence_num']); ?></span>
            <span class="hm-context__value hm-context__value--highlight"><?php echo esc_html($context['sequence_title']); ?></span>
        </div>
        <div class="hm-context__item">
            <span class="hm-context__label">Objective</span>
            <span class="hm-context__value"><?php echo esc_html($loop_display); ?></span>
        </div>
        <div class="hm-context__item">
            <span class="hm-context__label">Loops</span>
            <span class="hm-context__value"><?php echo esc_html($context['loops_installed']); ?>/<?php echo esc_html($context['loops_total']); ?></span>
        </div>
        <div class="hm-context__item">
            <span class="hm-context__label">Pink Panthers</span>
            <span class="hm-context__value hm-context__value--highlight"><?php echo esc_html($context['deployment_days']); ?> days</span>
        </div>
    </div>

    <!-- Chat Interface (rendered by WPCode shortcode) -->
    <div class="hm-chat-content">
        <?php
        // Output the page content which should contain [hive_mistress_chat] shortcode
        while (have_posts()) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
</div>

<?php
get_footer();
