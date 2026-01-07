<?php
/**
 * Dossier Notebook v4 — Main Template
 *
 * Per-field visibility editor with section lock/unlock pattern.
 * Called when viewing own profile in edit mode.
 *
 * @package UNMASK
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$sections = unmask_get_notebook_sections();
$custom_fields = unmask_get_custom_fields($user_id);
$visibility_counts = unmask_get_visibility_counts($user_id);
?>

<div class="dossier-notebook" data-user-id="<?php echo esc_attr($user_id); ?>">

    <!-- Header -->
    <div class="notebook-header">
        <span class="notebook-header__title">dossier</span>
    </div>

    <!-- Legend -->
    <div class="notebook-legend">
        <div class="notebook-legend__item">
            <span class="notebook-legend__dot notebook-legend__dot--green"></span>
            <span class="notebook-legend__label">interlinked</span>
        </div>
        <div class="notebook-legend__item">
            <span class="notebook-legend__dot notebook-legend__dot--yellow"></span>
            <span class="notebook-legend__label">user</span>
        </div>
        <div class="notebook-legend__item">
            <span class="notebook-legend__dot notebook-legend__dot--red"></span>
            <span class="notebook-legend__label">visitor</span>
        </div>
        <div class="notebook-legend__item">
            <span class="notebook-legend__dot notebook-legend__dot--gray"></span>
            <span class="notebook-legend__label">hidden</span>
        </div>
    </div>

    <!-- Sections -->
    <?php foreach ($sections as $section_id => $section) :
        $is_custom_section = !empty($section['is_custom']);
        $section_lock = unmask_get_section_lock($user_id, $section_id);
        $is_open = $section_lock === null;

        $section_classes = ['notebook-section'];
        if ($is_open) {
            $section_classes[] = 'notebook-section--open';
        } elseif ($section_lock) {
            $section_classes[] = 'notebook-section--locked-' . $section_lock;
        }
    ?>
    <div class="<?php echo esc_attr(implode(' ', $section_classes)); ?>"
         data-section-id="<?php echo esc_attr($section_id); ?>"
         data-locked="<?php echo esc_attr($section_lock ?: ''); ?>">

        <!-- Section Header -->
        <div class="notebook-section__header">
            <span class="notebook-section__label">
                <?php echo esc_html($section['label']); ?>
                <span class="notebook-section__status"></span>
            </span>

            <div class="visibility-circles">
                <!-- Gray (unlock/edit) -->
                <button type="button"
                        class="visibility-circle visibility-circle--gray <?php echo $is_open ? 'is-active' : ''; ?>"
                        data-action="section-unlock"
                        data-section="<?php echo esc_attr($section_id); ?>"
                        title="Edit individually">
                </button>

                <span class="visibility-circles__divider"></span>

                <!-- Green (lock to interlinked) -->
                <button type="button"
                        class="visibility-circle visibility-circle--green <?php echo $section_lock === 'green' ? 'is-active' : ''; ?>"
                        data-action="section-lock"
                        data-section="<?php echo esc_attr($section_id); ?>"
                        data-color="green"
                        title="Lock to interlinked">
                </button>

                <!-- Yellow (lock to user) -->
                <button type="button"
                        class="visibility-circle visibility-circle--yellow <?php echo $section_lock === 'yellow' ? 'is-active' : ''; ?>"
                        data-action="section-lock"
                        data-section="<?php echo esc_attr($section_id); ?>"
                        data-color="yellow"
                        title="Lock to user">
                </button>

                <!-- Red (lock to visitor) -->
                <button type="button"
                        class="visibility-circle visibility-circle--red <?php echo $section_lock === 'red' ? 'is-active' : ''; ?>"
                        data-action="section-lock"
                        data-section="<?php echo esc_attr($section_id); ?>"
                        data-color="red"
                        title="Lock to visitor">
                </button>
            </div>
        </div>

        <!-- Fields -->
        <?php if (!$is_custom_section) : ?>
            <?php foreach ($section['fields'] as $field_key => $field_def) :
                $field_value = unmask_get_field_value($user_id, $field_key);
                $field_visibility = unmask_get_field_visibility($user_id, $field_key);
                $effective_visibility = unmask_get_effective_field_visibility($user_id, $section_id, $field_key);
                $is_editable = $field_def['editable'] ?? true;
                $is_hidden = $field_visibility === 'hidden';

                $field_classes = ['notebook-field', 'notebook-field--' . $effective_visibility];
                if ($is_hidden) {
                    $field_classes[] = 'notebook-field--hidden';
                }
            ?>
            <div class="<?php echo esc_attr(implode(' ', $field_classes)); ?>"
                 data-field-key="<?php echo esc_attr($field_key); ?>"
                 data-visibility="<?php echo esc_attr($field_visibility); ?>">

                <div class="notebook-field__label-wrap">
                    <span class="notebook-field__label"><?php echo esc_html($field_def['label']); ?></span>
                </div>

                <div class="notebook-field__value-wrap">
                    <?php if ($is_editable) : ?>
                        <input type="text"
                               class="notebook-field__input"
                               value="<?php echo esc_attr($field_value); ?>"
                               placeholder="..."
                               data-action="edit-field"
                               data-field="<?php echo esc_attr($field_key); ?>">
                    <?php else : ?>
                        <span class="notebook-field__value notebook-field__value--readonly">
                            <?php echo esc_html($field_value); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="notebook-field__controls">
                    <!-- Gray (hidden) -->
                    <button type="button"
                            class="visibility-circle visibility-circle--gray <?php echo $field_visibility === 'hidden' ? 'is-active' : ''; ?> <?php echo !$is_open ? 'visibility-circle--disabled' : ''; ?>"
                            data-action="field-visibility"
                            data-field="<?php echo esc_attr($field_key); ?>"
                            data-color="hidden"
                            <?php echo !$is_open ? 'disabled' : ''; ?>>
                    </button>

                    <!-- Green -->
                    <button type="button"
                            class="visibility-circle visibility-circle--green <?php echo ($effective_visibility === 'green' && !$is_hidden) ? 'is-active' : ''; ?> <?php echo !$is_open ? 'visibility-circle--disabled' : ''; ?>"
                            data-action="field-visibility"
                            data-field="<?php echo esc_attr($field_key); ?>"
                            data-color="green"
                            <?php echo !$is_open ? 'disabled' : ''; ?>>
                    </button>

                    <!-- Yellow -->
                    <button type="button"
                            class="visibility-circle visibility-circle--yellow <?php echo ($effective_visibility === 'yellow' && !$is_hidden) ? 'is-active' : ''; ?> <?php echo !$is_open ? 'visibility-circle--disabled' : ''; ?>"
                            data-action="field-visibility"
                            data-field="<?php echo esc_attr($field_key); ?>"
                            data-color="yellow"
                            <?php echo !$is_open ? 'disabled' : ''; ?>>
                    </button>

                    <!-- Red -->
                    <button type="button"
                            class="visibility-circle visibility-circle--red <?php echo ($effective_visibility === 'red' && !$is_hidden) ? 'is-active' : ''; ?> <?php echo !$is_open ? 'visibility-circle--disabled' : ''; ?>"
                            data-action="field-visibility"
                            data-field="<?php echo esc_attr($field_key); ?>"
                            data-color="red"
                            <?php echo !$is_open ? 'disabled' : ''; ?>>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

        <?php else : ?>
            <!-- Custom Fields Section -->
            <?php foreach ($custom_fields as $custom_field) :
                $field_visibility = $custom_field['visibility'] ?? 'hidden';
                $is_hidden = $field_visibility === 'hidden';

                $field_classes = ['notebook-field', 'notebook-field--' . $field_visibility];
                if ($is_hidden) {
                    $field_classes[] = 'notebook-field--hidden';
                }
            ?>
            <div class="<?php echo esc_attr(implode(' ', $field_classes)); ?>"
                 data-field-key="<?php echo esc_attr($custom_field['id']); ?>"
                 data-visibility="<?php echo esc_attr($field_visibility); ?>"
                 data-is-custom="true">

                <div class="notebook-field__label-wrap">
                    <button type="button"
                            class="notebook-field__delete"
                            data-action="delete-custom"
                            data-field-id="<?php echo esc_attr($custom_field['id']); ?>"
                            title="Delete field">x</button>
                    <span class="notebook-field__label"><?php echo esc_html($custom_field['label']); ?></span>
                </div>

                <div class="notebook-field__value-wrap">
                    <input type="text"
                           class="notebook-field__input"
                           value="<?php echo esc_attr($custom_field['value']); ?>"
                           placeholder="..."
                           data-action="edit-custom"
                           data-field-id="<?php echo esc_attr($custom_field['id']); ?>">
                </div>

                <div class="notebook-field__controls">
                    <button type="button"
                            class="visibility-circle visibility-circle--gray <?php echo $field_visibility === 'hidden' ? 'is-active' : ''; ?>"
                            data-action="custom-visibility"
                            data-field-id="<?php echo esc_attr($custom_field['id']); ?>"
                            data-color="hidden">
                    </button>
                    <button type="button"
                            class="visibility-circle visibility-circle--green <?php echo $field_visibility === 'green' ? 'is-active' : ''; ?>"
                            data-action="custom-visibility"
                            data-field-id="<?php echo esc_attr($custom_field['id']); ?>"
                            data-color="green">
                    </button>
                    <button type="button"
                            class="visibility-circle visibility-circle--yellow <?php echo $field_visibility === 'yellow' ? 'is-active' : ''; ?>"
                            data-action="custom-visibility"
                            data-field-id="<?php echo esc_attr($custom_field['id']); ?>"
                            data-color="yellow">
                    </button>
                    <button type="button"
                            class="visibility-circle visibility-circle--red <?php echo $field_visibility === 'red' ? 'is-active' : ''; ?>"
                            data-action="custom-visibility"
                            data-field-id="<?php echo esc_attr($custom_field['id']); ?>"
                            data-color="red">
                    </button>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Add Custom Field Form -->
            <?php if (count($custom_fields) < UNMASK_MAX_CUSTOM_FIELDS) : ?>
            <div class="notebook-add-field" data-max="<?php echo UNMASK_MAX_CUSTOM_FIELDS; ?>">
                <div class="notebook-add-field__label-wrap">
                    <input type="text"
                           class="notebook-add-field__label-input"
                           placeholder="label"
                           data-input="label"
                           maxlength="30">
                </div>
                <div class="notebook-add-field__value-wrap">
                    <input type="text"
                           class="notebook-add-field__value-input"
                           placeholder="value..."
                           data-input="value"
                           maxlength="200">
                </div>
                <button type="button"
                        class="notebook-add-field__btn"
                        data-action="add-custom">add</button>
            </div>
            <?php else : ?>
            <div class="notebook-add-field">
                <div class="notebook-add-field__limit">
                    Maximum <?php echo UNMASK_MAX_CUSTOM_FIELDS; ?> custom fields reached
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>

    <!-- Bottom Preview Bar -->
    <div class="notebook-preview">
        <div class="notebook-preview__grid">
            <div class="notebook-preview__item">
                <span class="notebook-preview__count notebook-preview__count--green" data-count="green">
                    <?php echo intval($visibility_counts['green']); ?>
                </span>
                <span class="notebook-preview__label">interlinked</span>
            </div>
            <div class="notebook-preview__item">
                <span class="notebook-preview__count notebook-preview__count--yellow" data-count="yellow">
                    <?php echo intval($visibility_counts['yellow']); ?>
                </span>
                <span class="notebook-preview__label">user</span>
            </div>
            <div class="notebook-preview__item">
                <span class="notebook-preview__count notebook-preview__count--red" data-count="red">
                    <?php echo intval($visibility_counts['red']); ?>
                </span>
                <span class="notebook-preview__label">visitor</span>
            </div>
            <div class="notebook-preview__item">
                <span class="notebook-preview__count notebook-preview__count--gray" data-count="hidden">
                    <?php echo intval($visibility_counts['hidden']); ?>
                </span>
                <span class="notebook-preview__label">hidden</span>
            </div>
        </div>
    </div>

</div>
