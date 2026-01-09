<?php
/**
 * Unified ISO Card Component
 *
 * Shared card component for ISO listings across homepage rails and ISO board grid.
 * Uses ISO Board design as source of truth.
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type int    $post_id      ISO post ID (required)
 *     @type string $context      'rail' | 'grid' - affects sizing and features (default: 'grid')
 *     @type bool   $clickable    Whether card opens detail modal (default: false for rail, true for grid)
 *     @type bool   $is_blurred   Whether to blur for strangers (default: false)
 *     @type bool   $show_matches Whether to show match indicators (default: false for rail, true for grid)
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// PARAMETERS
// =============================================================================

$post_id     = $args['post_id'] ?? get_the_ID();
$context     = $args['context'] ?? 'grid';
$clickable   = $args['clickable'] ?? ($context === 'grid');
$is_blurred  = $args['is_blurred'] ?? false;
$show_matches = $args['show_matches'] ?? ($context === 'grid');

// =============================================================================
// DATA RETRIEVAL
// =============================================================================

// Get ACF fields
$iso_type       = get_field('iso_type', $post_id) ?: 'seeking';
$iso_category   = get_field('iso_category', $post_id) ?: '';
$iso_factory    = get_field('iso_factory', $post_id);
$iso_location   = get_field('iso_location', $post_id);
$iso_expiration = get_field('iso_expiration', $post_id);

// Get author info
$author_id = get_post_field('post_author', $post_id);

// Designation
$designation = function_exists('unmask_get_designation')
    ? unmask_get_designation($author_id)
    : (function_exists('unmask_get_user_designation')
        ? unmask_get_user_designation($author_id)
        : 'V-' . str_pad($author_id, 3, '0', STR_PAD_LEFT));

// Drone status
$is_drone = function_exists('unmask_get_member_type')
    ? (unmask_get_member_type($author_id) === 'drone')
    : false;

// Designation color
$designation_color = function_exists('unmask_get_designation_color_class')
    ? unmask_get_designation_color_class($author_id)
    : 'designation--yellow';

// Avatar
$avatar_url = '';
if (function_exists('bp_core_fetch_avatar')) {
    $avatar_url = bp_core_fetch_avatar([
        'item_id' => $author_id,
        'type'    => 'thumb',
        'html'    => false
    ]);
} else {
    $avatar_url = get_avatar_url($author_id, ['size' => 96]);
}

// Calculate days until expiration
$days_left = 0;
if ($iso_expiration) {
    $expiration_date = DateTime::createFromFormat('Ymd', $iso_expiration);
    if (!$expiration_date) {
        $expiration_date = DateTime::createFromFormat('Y-m-d', $iso_expiration);
    }
    if ($expiration_date) {
        $now = new DateTime();
        $diff = $now->diff($expiration_date);
        $days_left = $diff->invert ? 0 : $diff->days;
    }
}
$is_expiring_soon = ($days_left <= 7);

// Factory preferred?
$is_factory_preferred = in_array($iso_factory, ['yes', 'preferred']);

// Get content excerpt
$description = get_the_excerpt($post_id);
if (empty($description)) {
    $description = wp_trim_words(get_the_content(null, false, $post_id), 25, '...');
}

// =============================================================================
// MATCH INDICATORS (Grid context only)
// =============================================================================

$match_indicators = [];
$has_matches = false;

if ($show_matches && !$is_blurred) {
    $current_user_id = get_current_user_id();
    if ($current_user_id > 0 && $current_user_id !== (int) $author_id) {
        // Check if same city
        if (function_exists('unmask_same_city') && unmask_same_city($current_user_id, $author_id)) {
            $match_indicators['local'] = true;
        }

        // Check shared creative practices
        if (function_exists('unmask_get_shared_functions')) {
            $shared = unmask_get_shared_functions($current_user_id, $author_id);
            if (!empty($shared)) {
                $match_indicators['practices'] = $shared;
            }
        }

        // Check mutual connections
        if (function_exists('unmask_get_mutual_connections')) {
            $mutual = unmask_get_mutual_connections($current_user_id, $author_id);
            if ($mutual > 0) {
                $match_indicators['mutual'] = $mutual;
            }
        }
    }
    $has_matches = !empty($match_indicators);
}

// =============================================================================
// BUILD CLASSES
// =============================================================================

$card_classes = ['iso-card', 'iso-card--' . $context];

if ($is_factory_preferred) {
    $card_classes[] = 'iso-card--factory';
}
if ($is_blurred) {
    $card_classes[] = 'iso-card--blurred';
}
if ($has_matches) {
    $card_classes[] = 'iso-card--has-match';
}
if ($is_expiring_soon) {
    $card_classes[] = 'iso-card--expiring';
}

// =============================================================================
// RENDER
// =============================================================================
?>

<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>"
         data-iso-id="<?php echo esc_attr($post_id); ?>"
         <?php if ($clickable && !$is_blurred) : ?>onclick="unmaskIsoOpenDetail(<?php echo esc_attr($post_id); ?>)"<?php endif; ?>>

    <!-- Row 1: Tags + Expiration -->
    <div class="iso-card__top">
        <div class="iso-card__tags">
            <span class="iso-card__tag iso-card__tag--<?php echo esc_attr($iso_type); ?>">
                <?php echo esc_html($iso_type); ?>
            </span>
            <?php if ($iso_category) : ?>
            <span class="iso-card__tag iso-card__tag--category">
                <?php echo esc_html($iso_category); ?>
            </span>
            <?php endif; ?>
        </div>
        <span class="iso-card__expires <?php echo $is_expiring_soon ? 'iso-card__expires--soon' : ''; ?>">
            <?php echo esc_html($days_left); ?>d
        </span>
    </div>

    <!-- Row 2: Title -->
    <h3 class="iso-card__title"><?php echo esc_html(get_the_title($post_id)); ?></h3>

    <!-- Row 3: Description -->
    <p class="iso-card__description"><?php echo esc_html($description); ?></p>

    <!-- Row 4: Who (Avatar + Designation) -->
    <div class="iso-card__who">
        <div class="iso-card__avatar">
            <?php if ($avatar_url) : ?>
                <img src="<?php echo esc_url($avatar_url); ?>" alt="" loading="lazy">
            <?php endif; ?>
        </div>
        <span class="iso-card__designation <?php echo esc_attr($designation_color); ?> <?php echo $is_drone ? 'iso-card__designation--drone' : ''; ?>">
            <?php echo esc_html($designation); ?>
        </span>
    </div>

    <?php if ($context === 'grid') : ?>
    <!-- Row 5: Where (Grid context only) -->
    <div class="iso-card__where">
        <?php if ($iso_location) : ?>
            <span class="iso-card__location"><?php echo esc_html($iso_location); ?></span>
        <?php endif; ?>
        <?php if ($is_factory_preferred) : ?>
            <span class="iso-card__factory-badge">hosting at the Factory</span>
        <?php endif; ?>
    </div>

    <?php if ($has_matches) : ?>
    <!-- Row 6: Match Indicators (Grid context only) -->
    <div class="iso-card__matches">
        <?php if (!empty($match_indicators['local'])) : ?>
            <span class="iso-card__match iso-card__match--local" title="You're both in the same city">&#9670; local</span>
        <?php endif; ?>

        <?php if (!empty($match_indicators['practices'])) : ?>
            <span class="iso-card__match iso-card__match--practice" title="<?php echo esc_attr('You both practice: ' . implode(', ', $match_indicators['practices'])); ?>">
                &#9670; <?php echo esc_html(reset($match_indicators['practices'])); ?><?php echo count($match_indicators['practices']) > 1 ? ' +' . (count($match_indicators['practices']) - 1) : ''; ?>
            </span>
        <?php endif; ?>

        <?php if (!empty($match_indicators['mutual'])) : ?>
            <span class="iso-card__match iso-card__match--mutual" title="Mutual connections">&#9670; <?php echo esc_html($match_indicators['mutual']); ?> mutual</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else : ?>
    <!-- Rail context: Compact footer -->
    <div class="iso-card__footer">
        <span class="iso-card__meta"><?php echo esc_html($days_left); ?> days left</span>
        <?php if ($is_factory_preferred) : ?>
            <span class="iso-card__meta">@ factory</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</article>
