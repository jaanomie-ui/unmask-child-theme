<?php
/**
 * ISO Listing Card Component
 *
 * Displays an ISO Board listing card.
 * Hierarchy: What (type + category) → Title → Who → Where/When
 * Includes match indicators for logged-in users.
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type int    $post_id      ISO post ID
 *     @type bool   $is_blurred   Whether to blur for strangers (not logged in)
 * }
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get args
$post_id = $args['post_id'] ?? get_the_ID();
$is_blurred = $args['is_blurred'] ?? false;

// Get ACF fields
$iso_type = get_field('iso_type', $post_id);
$iso_category = get_field('iso_category', $post_id);
$iso_factory = get_field('iso_factory', $post_id);
$iso_location = get_field('iso_location', $post_id);
$iso_expiration = get_field('iso_expiration', $post_id);

// Get author info
$author_id = get_post_field('post_author', $post_id);
$designation = function_exists('unmask_get_designation')
    ? unmask_get_designation($author_id)
    : 'V-' . str_pad($author_id, 3, '0', STR_PAD_LEFT);
$is_drone = function_exists('unmask_get_member_type')
    ? (unmask_get_member_type($author_id) === 'drone')
    : false;
$designation_color = function_exists('unmask_get_designation_color_class')
    ? unmask_get_designation_color_class($author_id)
    : 'designation--yellow';

// Match indicators for logged-in users
$current_user_id = get_current_user_id();
$match_indicators = [];
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

// Get avatar
$avatar_url = '';
if (function_exists('bp_core_fetch_avatar')) {
    $avatar_url = bp_core_fetch_avatar([
        'item_id' => $author_id,
        'type' => 'thumb',
        'html' => false
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

// Build classes
$card_classes = ['iso-listing-card'];
if ($is_factory_preferred) {
    $card_classes[] = 'factory-preferred';
}
if ($is_blurred) {
    $card_classes[] = 'blurred';
}
if ($has_matches) {
    $card_classes[] = 'has-match';
}

// Get content excerpt
$description = get_the_excerpt($post_id);
if (empty($description)) {
    $description = wp_trim_words(get_the_content(null, false, $post_id), 25, '...');
}

// Build "what they want" string
$what_label = $iso_type . ' ' . $iso_category;
?>

<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>"
         data-iso-id="<?php echo esc_attr($post_id); ?>"
         <?php if (!$is_blurred) : ?>onclick="unmaskIsoOpenDetail(<?php echo esc_attr($post_id); ?>)"<?php endif; ?>>

    <!-- Top Row: Tags (left) + Expiration (right) -->
    <div class="iso-card-top">
        <div class="iso-card-tags">
            <span class="iso-tag iso-tag-<?php echo esc_attr($iso_type); ?>">
                <?php echo esc_html($iso_type); ?>
            </span>
            <span class="iso-tag iso-tag-category">
                <?php echo esc_html($iso_category); ?>
            </span>
        </div>
        <span class="iso-card-expires <?php echo $is_expiring_soon ? 'expiring-soon' : ''; ?>">
            expires in <?php echo esc_html($days_left); ?> <?php echo $days_left === 1 ? 'day' : 'days'; ?>
        </span>
    </div>

    <!-- Title -->
    <h3 class="iso-card-title"><?php echo esc_html(get_the_title($post_id)); ?></h3>

    <!-- Description -->
    <p class="iso-card-description"><?php echo esc_html($description); ?></p>

    <!-- Who: Avatar + Designation -->
    <div class="iso-card-who">
        <div class="iso-card-avatar">
            <?php if ($avatar_url) : ?>
                <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($designation); ?>">
            <?php endif; ?>
        </div>
        <span class="iso-card-designation designation <?php echo esc_attr($designation_color); ?> <?php echo $is_drone ? 'designation--drone' : ''; ?>">
            <?php echo esc_html($designation); ?>
        </span>
    </div>

    <!-- Where -->
    <div class="iso-card-where">
        <?php if ($iso_location) : ?>
            <span class="iso-card-location"><?php echo esc_html($iso_location); ?></span>
        <?php endif; ?>
        <?php if ($is_factory_preferred) : ?>
            <span class="iso-factory-badge">hosting at the Factory</span>
        <?php endif; ?>
    </div>

    <?php if ($has_matches) : ?>
    <!-- Match Indicators -->
    <div class="iso-card-matches">
        <?php if (!empty($match_indicators['local'])) : ?>
            <span class="iso-match-indicator iso-match-local" title="You're both in the same city">&#9670; local</span>
        <?php endif; ?>

        <?php if (!empty($match_indicators['practices'])) : ?>
            <span class="iso-match-indicator iso-match-practice" title="<?php echo esc_attr('You both practice: ' . implode(', ', $match_indicators['practices'])); ?>">
                &#9670; you practice: <?php echo esc_html(reset($match_indicators['practices'])); ?><?php echo count($match_indicators['practices']) > 1 ? ' +' . (count($match_indicators['practices']) - 1) : ''; ?>
            </span>
        <?php endif; ?>

        <?php if (!empty($match_indicators['mutual'])) : ?>
            <span class="iso-match-indicator iso-match-mutual" title="Mutual connections">&#9670; <?php echo esc_html($match_indicators['mutual']); ?> mutual</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</article>
