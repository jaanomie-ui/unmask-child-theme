<?php
/**
 * UNMASK Shortcodes v1
 *
 * Component shortcodes for use in Gutenberg blocks when PHP isn't possible.
 * Prefer using get_template_part() directly in PHP templates.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================================================
   HELPER FUNCTIONS
   ========================================================================== */

/**
 * Get user designation — defers to canonical function in functions.php
 *
 * @param int $user_id WordPress user ID
 * @return string Formatted designation or empty string
 */
if ( ! function_exists( 'unmask_get_user_designation' ) ) {
    function unmask_get_user_designation($user_id = null) {
        if (function_exists('unmask_get_designation')) {
            return unmask_get_designation($user_id);
        }
        return '';
    }
}

/**
 * Check if user is on the drone path (recruit, cadet, or house drone)
 *
 * @param int $user_id WordPress user ID
 * @return bool
 */
function unmask_user_is_drone($user_id = null) {
    if (function_exists('unmask_get_member_type')) {
        return in_array(unmask_get_member_type($user_id), ['recruit', 'cadet', 'house_drone']);
    }
    return false;
}

/**
 * Parse shortcode boolean attributes
 *
 * @param mixed $value Attribute value
 * @return bool
 */
function unmask_parse_bool($value) {
    if (is_bool($value)) {
        return $value;
    }
    return in_array(strtolower($value), array('true', '1', 'yes', 'on'), true);
}

/* ==========================================================================
   SHORTCODE: [unmask_button]
   ========================================================================== */

/**
 * Button shortcode
 *
 * Usage: [unmask_button text="Click me" type="primary" href="/page/"]
 *
 * @param array $atts Shortcode attributes
 * @return string Button HTML
 */
function unmask_button_shortcode($atts) {
    $atts = shortcode_atts(array(
        'text'     => 'Button',
        'type'     => 'primary',  // primary, secondary, ghost, danger
        'size'     => 'md',       // sm, md, lg
        'href'     => '#',
        'target'   => '',
        'icon'     => '',
        'disabled' => 'false',
        'class'    => '',
    ), $atts, 'unmask_button');

    $atts['disabled'] = unmask_parse_bool($atts['disabled']);

    ob_start();
    get_template_part('template-parts/components/unmask-button', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_button', 'unmask_button_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_badge]
   ========================================================================== */

/**
 * Badge shortcode
 *
 * Usage: [unmask_badge text="D-047" type="drone" dot="true"]
 *
 * @param array $atts Shortcode attributes
 * @return string Badge HTML
 */
function unmask_badge_shortcode($atts) {
    $atts = shortcode_atts(array(
        'text'  => '',
        'type'  => 'default',  // published, draft, expired, active, drone, visitor, schedule, available
        'size'  => 'md',       // sm, md, lg
        'dot'   => 'false',
        'class' => '',
    ), $atts, 'unmask_badge');

    $atts['dot'] = unmask_parse_bool($atts['dot']);

    ob_start();
    get_template_part('template-parts/components/unmask-badge', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_badge', 'unmask_badge_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_avatar]
   ========================================================================== */

/**
 * Avatar shortcode
 *
 * Usage: [unmask_avatar user_id="1" size="lg" online="true"]
 *
 * @param array $atts Shortcode attributes
 * @return string Avatar HTML
 */
function unmask_avatar_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id' => get_current_user_id(),
        'size'    => 'md',      // xs, sm, md, lg, xl
        'online'  => 'false',
        'square'  => 'false',
        'href'    => '',
        'class'   => '',
    ), $atts, 'unmask_avatar');

    $atts['online'] = unmask_parse_bool($atts['online']);
    $atts['square'] = unmask_parse_bool($atts['square']);
    $atts['user_id'] = intval($atts['user_id']);

    ob_start();
    get_template_part('template-parts/components/unmask-avatar', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_avatar', 'unmask_avatar_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_card]
   ========================================================================== */

/**
 * Card (fullbleed) shortcode
 *
 * Usage: [unmask_card post_id="123"]
 * Or:    [unmask_card subject="Title" image="url" href="/page/"]
 *
 * @param array $atts Shortcode attributes
 * @return string Card HTML
 */
function unmask_card_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id'    => 0,
        'image'      => '',
        'file_id'    => '',
        'type_badge' => '',
        'subject'    => '',
        'desc'       => '',
        'href'       => '',
        'class'      => '',
    ), $atts, 'unmask_card');

    $atts['post_id'] = intval($atts['post_id']);

    ob_start();
    get_template_part('template-parts/components/unmask-card-fullbleed', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_card', 'unmask_card_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_iso_card]
   ========================================================================== */

/**
 * ISO Card shortcode (member card with designation)
 *
 * Usage: [unmask_iso_card user_id="1" show_designation="true"]
 *
 * @param array $atts Shortcode attributes
 * @return string ISO Card HTML
 */
function unmask_iso_card_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id'          => get_current_user_id(),
        'show_designation' => 'true',
        'show_avatar'      => 'true',
        'show_name'        => 'true',
        'variant'          => 'default',  // default, compact, expanded
        'class'            => '',
    ), $atts, 'unmask_iso_card');

    $atts['show_designation'] = unmask_parse_bool($atts['show_designation']);
    $atts['show_avatar'] = unmask_parse_bool($atts['show_avatar']);
    $atts['show_name'] = unmask_parse_bool($atts['show_name']);
    $atts['user_id'] = intval($atts['user_id']);

    // Get user data
    $user = get_userdata($atts['user_id']);
    if ($user) {
        $atts['display_name'] = $user->display_name;
        $atts['designation'] = unmask_get_user_designation($atts['user_id']);
    }

    ob_start();
    get_template_part('template-parts/components/unmask-iso-card', null, $atts);
    return ob_get_clean();
}
add_shortcode('unmask_iso_card', 'unmask_iso_card_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_type_badge]
   ========================================================================== */

/**
 * Type badge shortcode - displays category as styled badge
 *
 * Usage: [unmask_type_badge]
 *
 * @param array $atts Shortcode attributes
 * @return string Badge HTML
 */
// V2 may be defined in WPCode snippet 2823 - defer to that if present
if (!function_exists('unmask_type_badge_shortcode')) {
    function unmask_type_badge_shortcode($atts) {
        $categories = get_the_category();

        if (empty($categories)) {
            return '';
        }

        $category = $categories[0];
        $name = strtoupper($category->name);
        $slug = $category->slug;

        // Red for profiles/interviews, Blue for events/photoshoots
        $color_class = 'profile';
        if ($slug === 'photoshoots' || $slug === 'events' || $slug === 'event') {
            $color_class = 'event';
        }

        return '<span class="unmask-type-badge unmask-type-badge--' . esc_attr($color_class) . '">' . esc_html($name) . '</span>';
    }
    add_shortcode('unmask_type_badge', 'unmask_type_badge_shortcode');
}

/* ==========================================================================
   SHORTCODE: [unmask_tags]
   ========================================================================== */

/**
 * Tags display shortcode
 *
 * Usage: [unmask_tags]
 *
 * @param array $atts Shortcode attributes
 * @return string Tags HTML
 */
function unmask_tags_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(),
    ), $atts);

    $tags = get_the_tags($atts['post_id']);

    if (empty($tags)) {
        return '';
    }

    $output = '<div class="unmask-tags-label">TAGS</div>';
    $output .= '<div class="unmask-tags">';

    foreach ($tags as $tag) {
        $output .= sprintf(
            '<a href="%s" class="unmask-tag">%s</a>',
            esc_url(get_tag_link($tag->term_id)),
            esc_html($tag->name)
        );
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('unmask_tags', 'unmask_tags_shortcode');

/* ==========================================================================
   HELPER: Get gallery images
   ========================================================================== */

/**
 * Extract image attachment IDs from post content
 *
 * Parses Gutenberg blocks for:
 * - wp-block-image (single images)
 * - wp-block-gallery (image galleries)
 *
 * @param string $content Post content
 * @return array Array of attachment IDs
 */
function unmask_extract_images_from_content($content) {
    $image_ids = array();

    if (empty($content)) {
        return $image_ids;
    }

    // Method 1: Extract from wp-image-{ID} classes (works for both Image and Gallery blocks)
    if (preg_match_all('/wp-image-(\d+)/', $content, $matches)) {
        $image_ids = array_map('intval', $matches[1]);
    }

    // Method 2: Extract from data-id attributes (Gallery block fallback)
    if (preg_match_all('/data-id=["\'](\d+)["\']/', $content, $matches)) {
        $image_ids = array_merge($image_ids, array_map('intval', $matches[1]));
    }

    // Remove duplicates and preserve order
    $image_ids = array_unique($image_ids);

    return $image_ids;
}

/**
 * Strip image and gallery blocks from post content
 *
 * Used to prevent images from appearing twice (in gallery strip AND in article body).
 * Images are extracted for the gallery strip, then removed from the main content.
 *
 * @param string $content Post content
 * @return string Filtered content without image/gallery blocks
 */
function unmask_strip_images_from_content($content) {
    if (empty($content)) {
        return $content;
    }

    // Remove Gutenberg image blocks: <!-- wp:image --> ... <!-- /wp:image -->
    $content = preg_replace(
        '/<!--\s*wp:image.*?-->.*?<!--\s*\/wp:image\s*-->/s',
        '',
        $content
    );

    // Remove Gutenberg gallery blocks: <!-- wp:gallery --> ... <!-- /wp:gallery -->
    $content = preg_replace(
        '/<!--\s*wp:gallery.*?-->.*?<!--\s*\/wp:gallery\s*-->/s',
        '',
        $content
    );

    // Remove classic editor images (standalone img tags with wp-image class)
    $content = preg_replace(
        '/<figure[^>]*class="[^"]*wp-block-image[^"]*"[^>]*>.*?<\/figure>/s',
        '',
        $content
    );

    // Clean up any resulting double whitespace
    $content = preg_replace('/\n{3,}/', "\n\n", $content);

    return trim($content);
}

/**
 * Get gallery images for a post
 *
 * Priority order:
 * 1. Explicit IDs passed to shortcode
 * 2. Featured image + images extracted from post content
 * 3. Featured image only as fallback
 *
 * @param string $ids Comma-separated image IDs (optional)
 * @param int $post_id Post ID
 * @return array Array of image data
 */
function unmask_get_gallery_images($ids = '', $post_id = 0) {
    $images = array();
    $image_ids = array();

    // If explicit IDs provided, use those only
    if (!empty($ids)) {
        $image_ids = array_map('intval', explode(',', $ids));
    } else {
        // Start with featured image
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            $image_ids[] = intval($thumbnail_id);
        }

        // Extract images from post content
        $post = get_post($post_id);
        if ($post && !empty($post->post_content)) {
            $content_ids = unmask_extract_images_from_content($post->post_content);
            $image_ids = array_merge($image_ids, $content_ids);
        }

        // Remove duplicates (featured image might also be in content)
        $image_ids = array_unique($image_ids);
    }

    // Build image data array
    foreach ($image_ids as $id) {
        $full = wp_get_attachment_image_src($id, 'full');
        $thumb = wp_get_attachment_image_src($id, 'thumbnail');

        if ($full) {
            $filename = basename(get_attached_file($id));
            $images[] = array(
                'id'       => $id,
                'full'     => $full[0],
                'thumb'    => $thumb ? $thumb[0] : $full[0],
                'width'    => $full[1],
                'height'   => $full[2],
                'filename' => $filename,
            );
        }
    }

    return $images;
}

/* ==========================================================================
   SHORTCODE: [unmask_gallery_lead]
   ========================================================================== */

/**
 * Gallery lead image shortcode
 *
 * Usage: [unmask_gallery_lead height="500px"]
 *
 * @param array $atts Shortcode attributes
 * @return string Gallery HTML
 */
function unmask_gallery_lead_shortcode($atts) {
    $atts = shortcode_atts(array(
        'ids'     => '',
        'post_id' => get_the_ID(),
        'width'   => '100%',
        'height'  => '500px',
    ), $atts);

    $images = unmask_get_gallery_images($atts['ids'], $atts['post_id']);

    if (empty($images)) {
        return '<div class="unmask-gallery-empty">No images found.</div>';
    }

    $gallery_id = 'unmask-gallery-' . $atts['post_id'];
    $total = count($images);
    $image_json = json_encode($images);

    ob_start();
    ?>
    <div class="unmask-gallery-lead"
         id="<?php echo esc_attr($gallery_id); ?>-lead"
         data-gallery-id="<?php echo esc_attr($gallery_id); ?>"
         data-total="<?php echo esc_attr($total); ?>"
         data-images='<?php echo esc_attr($image_json); ?>'
         style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;">

        <img
            src="<?php echo esc_url($images[0]['full']); ?>"
            alt="<?php echo esc_attr($images[0]['filename']); ?>"
            class="unmask-gallery-lead__img"
        />

        <div class="unmask-gallery-lead__filename"><?php echo esc_html($images[0]['filename']); ?></div>

        <div class="unmask-gallery-lead__counter">
            <span class="unmask-gallery-current">01</span> \ <?php echo str_pad($total, 2, '0', STR_PAD_LEFT); ?>
        </div>

        <?php if ($total > 1) : ?>
        <button class="unmask-gallery-nav unmask-gallery-nav--prev" aria-label="Previous">←</button>
        <button class="unmask-gallery-nav unmask-gallery-nav--next" aria-label="Next">→</button>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('unmask_gallery_lead', 'unmask_gallery_lead_shortcode');

/* ==========================================================================
   SHORTCODE: [unmask_gallery_strip]
   ========================================================================== */

/**
 * Gallery thumbnail strip shortcode
 *
 * Usage: [unmask_gallery_strip]
 *
 * @param array $atts Shortcode attributes
 * @return string Gallery strip HTML
 */
function unmask_gallery_strip_shortcode($atts) {
    $atts = shortcode_atts(array(
        'ids'     => '',
        'post_id' => get_the_ID(),
    ), $atts);

    $images = unmask_get_gallery_images($atts['ids'], $atts['post_id']);

    if (empty($images)) {
        return '';
    }

    $gallery_id = 'unmask-gallery-' . $atts['post_id'];

    ob_start();
    ?>
    <div class="unmask-gallery-strip" data-gallery-id="<?php echo esc_attr($gallery_id); ?>">
        <?php foreach ($images as $index => $image) : ?>
        <div class="unmask-gallery-strip__item<?php echo $index === 0 ? ' unmask-gallery-strip__item--active' : ''; ?>"
             data-index="<?php echo esc_attr($index); ?>">
            <img
                src="<?php echo esc_url($image['thumb']); ?>"
                alt="<?php echo esc_attr($image['filename']); ?>"
            />
            <span class="unmask-gallery-strip__label">IMG_<?php echo str_pad($index + 1, 3, '0', STR_PAD_LEFT); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('unmask_gallery_strip', 'unmask_gallery_strip_shortcode');
