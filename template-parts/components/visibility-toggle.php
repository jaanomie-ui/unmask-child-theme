<?php
/**
 * Visibility Toggle Component
 *
 * A three-state toggle for controlling section visibility:
 *   - user: Everyone including logged-out strangers (public)
 *   - visitor: Only logged-in members
 *   - interlinked: Only connected visitors (friends)
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * @param array $args {
 *     @type string $section_id   Unique identifier for this section (e.g., 'bio', 'practice', 'kink')
 *     @type string $current      Current visibility level: 'user' | 'visitor' | 'interlinked'
 *     @type bool   $disabled     Whether toggle is disabled (e.g., kink always interlinked)
 *     @type string $size         Size variant: 'default' | 'compact'
 * }
 */

if (!defined('ABSPATH')) exit;

$section_id = $args['section_id'] ?? '';
$current = $args['current'] ?? 'visitor';
$disabled = $args['disabled'] ?? false;
$size = $args['size'] ?? 'default';

// Validate current value
$valid_levels = ['user', 'visitor', 'interlinked'];
if (!in_array($current, $valid_levels)) {
    $current = 'visitor';
}

// Labels and descriptions
$levels = [
    'user' => [
        'label' => 'public',
        'icon' => '&#9673;', // ◉ (open circle)
        'title' => 'Visible to everyone, including strangers',
    ],
    'visitor' => [
        'label' => 'members',
        'icon' => '&#9670;', // ◆ (diamond)
        'title' => 'Visible only to logged-in members',
    ],
    'interlinked' => [
        'label' => 'connections',
        'icon' => '&#128279;', // 🔗 (link)
        'title' => 'Visible only to your connections',
    ],
];

$toggle_classes = ['visibility-toggle'];
if ($disabled) {
    $toggle_classes[] = 'visibility-toggle--disabled';
}
if ($size === 'compact') {
    $toggle_classes[] = 'visibility-toggle--compact';
}
?>

<div class="<?php echo esc_attr(implode(' ', $toggle_classes)); ?>"
     data-section="<?php echo esc_attr($section_id); ?>"
     data-current="<?php echo esc_attr($current); ?>">

    <?php foreach ($levels as $level => $data) : ?>
        <button type="button"
                class="visibility-toggle__option <?php echo $level === $current ? 'is-active' : ''; ?>"
                data-level="<?php echo esc_attr($level); ?>"
                title="<?php echo esc_attr($data['title']); ?>"
                <?php echo $disabled ? 'disabled' : ''; ?>>
            <span class="visibility-toggle__icon"><?php echo $data['icon']; ?></span>
            <?php if ($size !== 'compact') : ?>
                <span class="visibility-toggle__label"><?php echo esc_html($data['label']); ?></span>
            <?php endif; ?>
        </button>
    <?php endforeach; ?>

    <?php if (!$disabled) : ?>
        <input type="hidden"
               name="visibility_<?php echo esc_attr($section_id); ?>"
               value="<?php echo esc_attr($current); ?>">
    <?php endif; ?>
</div>
