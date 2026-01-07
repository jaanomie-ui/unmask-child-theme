<?php
/**
 * UNMASK Profile Edit — Split Panel + Bottom Sheet
 *
 * Replaces the default BuddyBoss/BuddyPress profile edit interface
 * with a split panel (desktop) and bottom sheet (mobile) pattern.
 *
 * @package UNMASK
 * @since 3.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get displayed user ID
$user_id = bp_displayed_user_id();

// Get all field groups
$field_groups = bp_xprofile_get_groups(array(
    'user_id'          => $user_id,
    'fetch_fields'     => true,
    'fetch_field_data' => true,
    'hide_empty_groups' => false,
    'hide_empty_fields' => false,
));

// Filter out empty groups and admin-only fields
$visible_groups = array();
foreach ($field_groups as $group) {
    if (empty($group->fields)) {
        continue;
    }

    $visible_fields = array();
    foreach ($group->fields as $field) {
        // Skip fields that shouldn't be editable
        if ($field->type === 'wp-biography') {
            continue;
        }

        // Get field value
        $field_value = xprofile_get_field_data($field->id, $user_id);
        if (is_array($field_value)) {
            $field_value = implode(', ', $field_value);
        }

        // Strip HTML for display (BuddyPress may return HTML for URL fields)
        if (is_string($field_value) && strpos($field_value, '<') !== false) {
            $field_value = wp_strip_all_tags($field_value);
        }

        $visible_fields[] = array(
            'id'      => $field->id,
            'name'    => $field->name,
            'value'   => $field_value,
            'type'    => unmask_get_input_type($field->type),
            'hint'    => unmask_get_field_hint($field->name),
            'options' => unmask_get_field_options($field),
        );
    }

    if (!empty($visible_fields)) {
        $visible_groups[] = array(
            'id'     => $group->id,
            'name'   => $group->name,
            'fields' => $visible_fields,
        );
    }
}
?>

<div class="unmask-edit">
    <header class="unmask-edit__header">
        <h1 class="unmask-edit__title">edit dossier</h1>
        <a href="<?php bp_displayed_user_link(); ?>" class="unmask-edit__visibility">
            back to profile
        </a>
    </header>

    <div class="unmask-edit__split">
        <!-- Left: Field list -->
        <nav class="unmask-edit__list" role="navigation" aria-label="Profile fields">
            <?php foreach ($visible_groups as $group) : ?>
                <div class="unmask-edit__section">
                    <h2 class="unmask-edit__section-title"><?php echo esc_html($group['name']); ?></h2>

                    <?php foreach ($group['fields'] as $field) :
                        $display_value = $field['value'] ?: '';
                        $is_empty = empty($display_value);

                        // Encode options for multiselect fields
                        $options_json = '';
                        if (!empty($field['options'])) {
                            $options_json = esc_attr(wp_json_encode($field['options']));
                        }
                    ?>
                        <button type="button"
                                class="unmask-edit__row"
                                data-field="<?php echo esc_attr($field['id']); ?>"
                                data-name="<?php echo esc_attr($field['name']); ?>"
                                data-value="<?php echo esc_attr($field['value']); ?>"
                                data-type="<?php echo esc_attr($field['type']); ?>"
                                data-hint="<?php echo esc_attr($field['hint']); ?>"
                                <?php if ($options_json) : ?>data-options="<?php echo $options_json; ?>"<?php endif; ?>
                                aria-label="Edit <?php echo esc_attr($field['name']); ?>">
                            <span class="unmask-edit__label"><?php echo esc_html($field['name']); ?></span>
                            <span class="unmask-edit__value<?php echo $is_empty ? ' unmask-edit__value--empty' : ''; ?>">
                                <?php echo $is_empty ? '—' : esc_html($display_value); ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if (empty($visible_groups)) : ?>
                <div class="unmask-edit__empty">
                    <p>No editable fields found.</p>
                </div>
            <?php endif; ?>
        </nav>

        <!-- Right: Edit detail (desktop only) -->
        <div class="unmask-edit__detail" role="region" aria-label="Field editor">
            <p class="unmask-edit__placeholder">← select a field to edit</p>
        </div>
    </div>
</div>

<!-- Bottom Sheet (mobile only) -->
<div class="unmask-sheet__scrim" aria-hidden="true"></div>
<div class="unmask-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="sheet-title" aria-hidden="true">
    <div class="unmask-sheet__handle" role="button" aria-label="Drag to close"></div>
    <header class="unmask-sheet__header">
        <h2 class="unmask-sheet__title" id="sheet-title">field name</h2>
        <button type="button" class="unmask-sheet__close" aria-label="Close">
            <span aria-hidden="true">✕</span>
        </button>
    </header>
    <div class="unmask-sheet__body">
        <!-- Dynamic content inserted by JS -->
    </div>
</div>
