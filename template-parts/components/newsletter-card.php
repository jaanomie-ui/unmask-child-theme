<?php
/**
 * Newsletter Card Component
 *
 * Reusable newsletter signup card that adapts to different contexts.
 * Uses the UNMASK card design system.
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     Optional. Card configuration.
 *
 *     @type string $context   Where the card appears: homepage|archive|iso|factory|footer. Default 'general'.
 *     @type string $variant   Card style: default|compact|minimal. Default 'default'.
 *     @type string $headline  Custom headline text. Default varies by context.
 *     @type string $subhead   Custom subhead text. Default varies by context.
 *     @type array  $tags      Mailchimp tags to apply. Default includes context.
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args with defaults
$context = $args['context'] ?? 'general';
$variant = $args['variant'] ?? 'default';
$custom_headline = $args['headline'] ?? '';
$custom_subhead = $args['subhead'] ?? '';
$tags = $args['tags'] ?? array($context);

// Context-specific defaults
$defaults = array(
    'homepage' => array(
        'headline' => 'stay connected',
        'subhead'  => 'weekly dispatch from the UNMASK community',
    ),
    'archive' => array(
        'headline' => 'get notified of new records',
        'subhead'  => 'fresh content delivered weekly',
    ),
    'iso' => array(
        'headline' => 'ISO alerts in your inbox',
        'subhead'  => 'never miss a collaboration opportunity',
    ),
    'factory' => array(
        'headline' => 'studio updates',
        'subhead'  => 'availability, events, and member rates',
    ),
    'footer' => array(
        'headline' => 'join the newsletter',
        'subhead'  => 'weekly updates from UNMASK',
    ),
    'general' => array(
        'headline' => 'subscribe',
        'subhead'  => 'get the UNMASK newsletter',
    ),
);

$headline = $custom_headline ?: ($defaults[$context]['headline'] ?? $defaults['general']['headline']);
$subhead = $custom_subhead ?: ($defaults[$context]['subhead'] ?? $defaults['general']['subhead']);

// Build CSS classes
$card_classes = array('newsletter-card', 'card', 'card--newsletter');
$card_classes[] = 'card--' . $variant;
$card_classes[] = 'newsletter-card--' . $context;

if (!is_user_logged_in()) {
    $card_classes[] = 'card--stranger';
}

// Encode tags for form
$tags_json = htmlspecialchars(json_encode($tags), ENT_QUOTES, 'UTF-8');

// Check for success/error message
$show_success = isset($_GET['newsletter_status']) && $_GET['newsletter_status'] === 'success';
$show_error = isset($_GET['newsletter_status']) && $_GET['newsletter_status'] === 'error';
?>

<div class="<?php echo esc_attr(implode(' ', $card_classes)); ?>">
    <?php if ($show_success) : ?>
        <div class="newsletter-card__success">
            <span class="newsletter-card__success-icon">✓</span>
            <span class="newsletter-card__success-text">you're subscribed</span>
        </div>
    <?php elseif ($show_error) : ?>
        <div class="newsletter-card__error">
            <span class="newsletter-card__error-text">something went wrong. try again?</span>
        </div>
        <?php // Show form again after error ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="newsletter-card__form">
            <?php wp_nonce_field('unmask_newsletter', 'newsletter_nonce'); ?>
            <input type="hidden" name="action" value="unmask_newsletter_subscribe">
            <input type="hidden" name="newsletter_context" value="<?php echo esc_attr($context); ?>">
            <input type="hidden" name="newsletter_tags" value="<?php echo $tags_json; ?>">

            <input type="email"
                   name="newsletter_email"
                   class="newsletter-card__input"
                   placeholder="your email"
                   required
                   <?php echo is_user_logged_in() ? 'value="' . esc_attr(wp_get_current_user()->user_email) . '"' : ''; ?>>
            <button type="submit" class="newsletter-card__submit">[subscribe]</button>
        </form>
    <?php else : ?>
        <div class="newsletter-card__content">
            <h3 class="newsletter-card__headline"><?php echo esc_html($headline); ?></h3>
            <?php if ($variant !== 'minimal') : ?>
                <p class="newsletter-card__subhead"><?php echo esc_html($subhead); ?></p>
            <?php endif; ?>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="newsletter-card__form">
            <?php wp_nonce_field('unmask_newsletter', 'newsletter_nonce'); ?>
            <input type="hidden" name="action" value="unmask_newsletter_subscribe">
            <input type="hidden" name="newsletter_context" value="<?php echo esc_attr($context); ?>">
            <input type="hidden" name="newsletter_tags" value="<?php echo $tags_json; ?>">

            <input type="email"
                   name="newsletter_email"
                   class="newsletter-card__input"
                   placeholder="your email"
                   required
                   <?php echo is_user_logged_in() ? 'value="' . esc_attr(wp_get_current_user()->user_email) . '"' : ''; ?>>
            <button type="submit" class="newsletter-card__submit">[subscribe]</button>
        </form>
    <?php endif; ?>
</div>
