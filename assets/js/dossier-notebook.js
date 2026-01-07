/**
 * Dossier Notebook v4 — JavaScript
 *
 * Handles all interactions for the per-field visibility editor.
 * - Section lock/unlock
 * - Field visibility circles
 * - Custom field CRUD
 * - Field value editing with debounce
 * - Preview bar count updates
 *
 * @package UNMASK
 */

(function() {
  'use strict';

  console.log('[Notebook] Script loaded');
  console.log('[Notebook] unmaskNotebook:', typeof unmaskNotebook !== 'undefined' ? unmaskNotebook : 'NOT DEFINED');

  // Debounce timers
  const saveTimers = {};
  const SAVE_DELAY = 800;
  const TOAST_DURATION = 2500;

  // Cache DOM references
  let notebook = null;
  let userId = null;
  let toastEl = null;
  let toastTimer = null;

  /**
   * Initialize notebook
   */
  function init() {
    console.log('[Notebook] init() called');

    notebook = document.querySelector('.dossier-notebook');
    if (!notebook) {
      console.log('[Notebook] No .dossier-notebook element found');
      return;
    }

    userId = notebook.dataset.userId;
    if (!userId) {
      console.log('[Notebook] No userId found');
      return;
    }

    console.log('[Notebook] Binding events for user', userId);
    bindEvents();
    createToastElement();
    console.log('[Notebook] Ready');
  }

  /**
   * Create toast notification element
   */
  function createToastElement() {
    toastEl = document.createElement('div');
    toastEl.className = 'notebook-toast';
    toastEl.setAttribute('role', 'status');
    toastEl.setAttribute('aria-live', 'polite');
    document.body.appendChild(toastEl);
  }

  /**
   * Show toast notification
   * @param {string} message - Message to display
   * @param {string} type - 'success', 'error', or 'info'
   */
  function showToast(message, type) {
    if (!toastEl) return;

    // Clear existing timer
    if (toastTimer) {
      clearTimeout(toastTimer);
    }

    // Update toast
    toastEl.textContent = message;
    toastEl.className = 'notebook-toast notebook-toast--' + type;

    // Force reflow for animation
    void toastEl.offsetWidth;

    // Show
    toastEl.classList.add('is-visible');

    // Auto-hide
    toastTimer = setTimeout(function() {
      toastEl.classList.remove('is-visible');
    }, TOAST_DURATION);
  }

  /**
   * Bind all event handlers
   */
  function bindEvents() {
    // Section lock/unlock
    notebook.addEventListener('click', function(e) {
      console.log('[Notebook] Click detected on:', e.target);
      const btn = e.target.closest('[data-action="section-unlock"]');
      if (btn) {
        console.log('[Notebook] Section unlock clicked');
        e.preventDefault();
        handleSectionUnlock(btn);
      }
    });

    notebook.addEventListener('click', function(e) {
      const btn = e.target.closest('[data-action="section-lock"]');
      if (btn) {
        console.log('[Notebook] Section lock clicked:', btn.dataset.color);
        e.preventDefault();
        handleSectionLock(btn);
      }
    });

    // Field visibility
    notebook.addEventListener('click', function(e) {
      const btn = e.target.closest('[data-action="field-visibility"]');
      if (btn && !btn.disabled) {
        e.preventDefault();
        handleFieldVisibility(btn);
      }
    });

    // Custom field visibility
    notebook.addEventListener('click', function(e) {
      const btn = e.target.closest('[data-action="custom-visibility"]');
      if (btn) {
        e.preventDefault();
        handleCustomVisibility(btn);
      }
    });

    // Add custom field
    notebook.addEventListener('click', function(e) {
      const btn = e.target.closest('[data-action="add-custom"]');
      if (btn) {
        e.preventDefault();
        handleAddCustomField(btn);
      }
    });

    // Delete custom field
    notebook.addEventListener('click', function(e) {
      const btn = e.target.closest('[data-action="delete-custom"]');
      if (btn) {
        e.preventDefault();
        handleDeleteCustomField(btn);
      }
    });

    // Field value editing (standard fields)
    notebook.addEventListener('input', function(e) {
      const input = e.target.closest('[data-action="edit-field"]');
      if (input) {
        handleFieldEdit(input);
      }
    });

    // Custom field value editing
    notebook.addEventListener('input', function(e) {
      const input = e.target.closest('[data-action="edit-custom"]');
      if (input) {
        handleCustomFieldEdit(input);
      }
    });
  }

  /**
   * Handle section unlock (gray circle)
   */
  function handleSectionUnlock(btn) {
    const section = btn.closest('.notebook-section');
    const sectionId = section.dataset.sectionId;

    // Update UI immediately
    section.classList.remove('notebook-section--locked-green', 'notebook-section--locked-yellow', 'notebook-section--locked-red');
    section.classList.add('notebook-section--open');
    section.dataset.locked = '';

    // Update header circles
    updateSectionCircles(section, null);

    // Enable field controls
    enableFieldControls(section);

    // Save via AJAX
    saveSectionLock(sectionId, '');
  }

  /**
   * Handle section lock (green/yellow/red circle)
   */
  function handleSectionLock(btn) {
    const section = btn.closest('.notebook-section');
    const sectionId = section.dataset.sectionId;
    const color = btn.dataset.color;

    // Update UI immediately
    section.classList.remove('notebook-section--open', 'notebook-section--locked-green', 'notebook-section--locked-yellow', 'notebook-section--locked-red');
    section.classList.add('notebook-section--locked-' + color);
    section.dataset.locked = color;

    // Update header circles
    updateSectionCircles(section, color);

    // Disable field controls and update field visibilities
    disableFieldControls(section);
    updateFieldsForLock(section, color);

    // Save via AJAX
    saveSectionLock(sectionId, color);
  }

  /**
   * Update section header circle states
   */
  function updateSectionCircles(section, activeColor) {
    const header = section.querySelector('.notebook-section__header');
    const circles = header.querySelectorAll('.visibility-circle');

    circles.forEach(function(circle) {
      circle.classList.remove('is-active');
    });

    if (activeColor === null) {
      // Unlock state - gray is active
      const grayCircle = header.querySelector('[data-action="section-unlock"]');
      if (grayCircle) grayCircle.classList.add('is-active');
    } else {
      // Lock state - specific color is active
      const activeCircle = header.querySelector('[data-color="' + activeColor + '"]');
      if (activeCircle) activeCircle.classList.add('is-active');
    }
  }

  /**
   * Enable field visibility controls when section unlocked
   */
  function enableFieldControls(section) {
    const controls = section.querySelectorAll('.notebook-field__controls .visibility-circle');
    controls.forEach(function(circle) {
      circle.classList.remove('visibility-circle--disabled');
      circle.disabled = false;
    });
  }

  /**
   * Disable field visibility controls when section locked
   */
  function disableFieldControls(section) {
    const controls = section.querySelectorAll('.notebook-field__controls .visibility-circle');
    controls.forEach(function(circle) {
      circle.classList.add('visibility-circle--disabled');
      circle.disabled = true;
    });
  }

  /**
   * Update field visual states when section is locked
   */
  function updateFieldsForLock(section, lockColor) {
    const fields = section.querySelectorAll('.notebook-field');

    fields.forEach(function(field) {
      const currentVis = field.dataset.visibility;

      // Hidden fields stay hidden, others get lock color
      if (currentVis !== 'hidden') {
        field.classList.remove('notebook-field--green', 'notebook-field--yellow', 'notebook-field--red');
        field.classList.add('notebook-field--' + lockColor);

        // Update field circle states
        const circles = field.querySelectorAll('.notebook-field__controls .visibility-circle');
        circles.forEach(function(c) {
          c.classList.remove('is-active');
        });

        const activeCircle = field.querySelector('[data-color="' + lockColor + '"]');
        if (activeCircle) activeCircle.classList.add('is-active');
      }
    });

    // Update preview counts
    updatePreviewCounts();
  }

  /**
   * Handle field visibility circle click
   */
  function handleFieldVisibility(btn) {
    const field = btn.closest('.notebook-field');
    const fieldKey = field.dataset.fieldKey;
    const color = btn.dataset.color;

    // Update UI immediately
    field.classList.remove('notebook-field--green', 'notebook-field--yellow', 'notebook-field--red', 'notebook-field--hidden');

    if (color === 'hidden') {
      field.classList.add('notebook-field--hidden', 'notebook-field--gray');
    } else {
      field.classList.add('notebook-field--' + color);
    }

    field.dataset.visibility = color;

    // Update circle states
    const circles = field.querySelectorAll('.notebook-field__controls .visibility-circle');
    circles.forEach(function(c) {
      c.classList.remove('is-active');
    });
    btn.classList.add('is-active');

    // Update preview counts
    updatePreviewCounts();

    // Save via AJAX
    saveFieldVisibility(fieldKey, color);
  }

  /**
   * Handle custom field visibility circle click
   */
  function handleCustomVisibility(btn) {
    const field = btn.closest('.notebook-field');
    const fieldId = btn.dataset.fieldId;
    const color = btn.dataset.color;

    // Update UI immediately
    field.classList.remove('notebook-field--green', 'notebook-field--yellow', 'notebook-field--red', 'notebook-field--hidden');

    if (color === 'hidden') {
      field.classList.add('notebook-field--hidden', 'notebook-field--gray');
    } else {
      field.classList.add('notebook-field--' + color);
    }

    field.dataset.visibility = color;

    // Update circle states
    const circles = field.querySelectorAll('.notebook-field__controls .visibility-circle');
    circles.forEach(function(c) {
      c.classList.remove('is-active');
    });
    btn.classList.add('is-active');

    // Update preview counts
    updatePreviewCounts();

    // Save via AJAX
    saveCustomVisibility(fieldId, color);
  }

  /**
   * Handle add custom field
   */
  function handleAddCustomField(btn) {
    const addForm = btn.closest('.notebook-add-field');
    const labelInput = addForm.querySelector('[data-input="label"]');
    const valueInput = addForm.querySelector('[data-input="value"]');

    const label = labelInput.value.trim();
    const value = valueInput.value.trim();

    // Clear previous errors
    labelInput.classList.remove('notebook-add-field__label-input--error');
    valueInput.classList.remove('notebook-add-field__value-input--error');

    // Validate
    if (!label) {
      labelInput.classList.add('notebook-add-field__label-input--error');
      labelInput.focus();
      return;
    }

    if (!value) {
      valueInput.classList.add('notebook-add-field__value-input--error');
      valueInput.focus();
      return;
    }

    // Disable button
    btn.disabled = true;
    btn.textContent = '...';

    // Save via AJAX
    const formData = new FormData();
    formData.append('action', 'unmask_add_custom_field');
    formData.append('nonce', unmaskNotebook.nonce);
    formData.append('label', label);
    formData.append('value', value);

    fetch(unmaskNotebook.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        // Add new field to DOM
        addCustomFieldToDOM(data.data);

        // Clear inputs
        labelInput.value = '';
        valueInput.value = '';

        // Update preview counts
        updatePreviewCounts();

        // Show success toast
        showToast('field added', 'success');

        // Check if at max
        if (data.data.at_max) {
          addForm.innerHTML = '<div class="notebook-add-field__limit">Maximum ' + data.data.max + ' custom fields reached</div>';
        }
      } else {
        showToast(data.data || 'error adding field', 'error');
      }
    })
    .catch(function(err) {
      console.error('Add custom field error:', err);
      showToast('error adding field', 'error');
    })
    .finally(function() {
      btn.disabled = false;
      btn.textContent = 'add';
    });
  }

  /**
   * Add custom field to DOM
   */
  function addCustomFieldToDOM(fieldData) {
    const section = notebook.querySelector('[data-section-id="custom"]');
    const addForm = section.querySelector('.notebook-add-field');

    const fieldHtml = `
      <div class="notebook-field notebook-field--hidden"
           data-field-key="${fieldData.id}"
           data-visibility="hidden"
           data-is-custom="true">
        <div class="notebook-field__label-wrap">
          <button type="button"
                  class="notebook-field__delete"
                  data-action="delete-custom"
                  data-field-id="${fieldData.id}"
                  title="Delete field">x</button>
          <span class="notebook-field__label">${escapeHtml(fieldData.label)}</span>
        </div>
        <div class="notebook-field__value-wrap">
          <input type="text"
                 class="notebook-field__input"
                 value="${escapeHtml(fieldData.value)}"
                 placeholder="..."
                 data-action="edit-custom"
                 data-field-id="${fieldData.id}">
        </div>
        <div class="notebook-field__controls">
          <button type="button"
                  class="visibility-circle visibility-circle--gray is-active"
                  data-action="custom-visibility"
                  data-field-id="${fieldData.id}"
                  data-color="hidden">
          </button>
          <button type="button"
                  class="visibility-circle visibility-circle--green"
                  data-action="custom-visibility"
                  data-field-id="${fieldData.id}"
                  data-color="green">
          </button>
          <button type="button"
                  class="visibility-circle visibility-circle--yellow"
                  data-action="custom-visibility"
                  data-field-id="${fieldData.id}"
                  data-color="yellow">
          </button>
          <button type="button"
                  class="visibility-circle visibility-circle--red"
                  data-action="custom-visibility"
                  data-field-id="${fieldData.id}"
                  data-color="red">
          </button>
        </div>
      </div>
    `;

    addForm.insertAdjacentHTML('beforebegin', fieldHtml);
  }

  /**
   * Handle delete custom field
   */
  function handleDeleteCustomField(btn) {
    const fieldId = btn.dataset.fieldId;
    const field = btn.closest('.notebook-field');

    if (!confirm('Delete this field?')) return;

    // Disable field
    field.style.opacity = '0.5';
    field.style.pointerEvents = 'none';

    // Delete via AJAX
    const formData = new FormData();
    formData.append('action', 'unmask_delete_custom_field');
    formData.append('nonce', unmaskNotebook.nonce);
    formData.append('field_id', fieldId);

    fetch(unmaskNotebook.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        // Remove from DOM
        field.remove();

        // Update preview counts
        updatePreviewCounts();

        // Show add form if it was hidden
        restoreAddFormIfNeeded();

        // Show success toast
        showToast('field deleted', 'success');
      } else {
        showToast(data.data || 'error deleting field', 'error');
        field.style.opacity = '';
        field.style.pointerEvents = '';
      }
    })
    .catch(function(err) {
      console.error('Delete custom field error:', err);
      showToast('error deleting field', 'error');
      field.style.opacity = '';
      field.style.pointerEvents = '';
    });
  }

  /**
   * Restore add form if under max
   */
  function restoreAddFormIfNeeded() {
    const section = notebook.querySelector('[data-section-id="custom"]');
    const customFields = section.querySelectorAll('.notebook-field[data-is-custom="true"]');
    const addForm = section.querySelector('.notebook-add-field');
    const maxFields = addForm ? parseInt(addForm.dataset.max || '5', 10) : 5;

    if (customFields.length < maxFields && addForm && addForm.querySelector('.notebook-add-field__limit')) {
      addForm.innerHTML = `
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
      `;
    }
  }

  /**
   * Handle field value edit (debounced)
   */
  function handleFieldEdit(input) {
    const fieldKey = input.dataset.field;
    const value = input.value;

    // Clear existing timer
    if (saveTimers[fieldKey]) {
      clearTimeout(saveTimers[fieldKey]);
    }

    // Set new timer
    saveTimers[fieldKey] = setTimeout(function() {
      saveFieldValue(fieldKey, value);
    }, SAVE_DELAY);
  }

  /**
   * Handle custom field value edit (debounced)
   */
  function handleCustomFieldEdit(input) {
    const fieldId = input.dataset.fieldId;
    const value = input.value;
    const timerKey = 'custom_' + fieldId;

    // Clear existing timer
    if (saveTimers[timerKey]) {
      clearTimeout(saveTimers[timerKey]);
    }

    // Set new timer
    saveTimers[timerKey] = setTimeout(function() {
      saveCustomFieldValue(fieldId, value);
    }, SAVE_DELAY);
  }

  /**
   * Update preview bar counts
   */
  function updatePreviewCounts() {
    const counts = { green: 0, yellow: 0, red: 0, hidden: 0 };

    // Count all field visibilities
    const fields = notebook.querySelectorAll('.notebook-field');
    fields.forEach(function(field) {
      const vis = field.dataset.visibility || 'hidden';
      if (vis === 'hidden') {
        counts.hidden++;
      } else if (counts[vis] !== undefined) {
        counts[vis]++;
      }
    });

    // Update DOM
    const preview = notebook.querySelector('.notebook-preview');
    if (!preview) return;

    Object.keys(counts).forEach(function(color) {
      const countEl = preview.querySelector('[data-count="' + color + '"]');
      if (countEl) {
        countEl.textContent = counts[color];
      }
    });
  }

  // AJAX helper functions

  function saveSectionLock(sectionId, color) {
    const formData = new FormData();
    formData.append('action', 'unmask_save_section_lock');
    formData.append('nonce', unmaskNotebook.nonce);
    formData.append('section_id', sectionId);
    formData.append('color', color);

    fetch(unmaskNotebook.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        showToast(color ? 'section locked' : 'section unlocked', 'success');
      } else {
        console.error('Save section lock error:', data);
        showToast('error saving section', 'error');
      }
    })
    .catch(function(err) {
      console.error('Save section lock error:', err);
      showToast('error saving section', 'error');
    });
  }

  function saveFieldVisibility(fieldKey, color) {
    const formData = new FormData();
    formData.append('action', 'unmask_save_field_visibility_v4');
    formData.append('nonce', unmaskNotebook.nonce);
    formData.append('field_key', fieldKey);
    formData.append('visibility', color);

    fetch(unmaskNotebook.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('visibility updated', 'success');
      } else {
        console.error('Save field visibility error:', data);
        showToast('error updating visibility', 'error');
      }
    })
    .catch(function(err) {
      console.error('Save field visibility error:', err);
      showToast('error updating visibility', 'error');
    });
  }

  function saveCustomVisibility(fieldId, color) {
    const formData = new FormData();
    formData.append('action', 'unmask_update_custom_field_visibility');
    formData.append('nonce', unmaskNotebook.nonce);
    formData.append('field_id', fieldId);
    formData.append('visibility', color);

    fetch(unmaskNotebook.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('visibility updated', 'success');
      } else {
        console.error('Save custom visibility error:', data);
        showToast('error updating visibility', 'error');
      }
    })
    .catch(function(err) {
      console.error('Save custom visibility error:', err);
      showToast('error updating visibility', 'error');
    });
  }

  function saveFieldValue(fieldKey, value) {
    const formData = new FormData();
    formData.append('action', 'unmask_save_field_value_v4');
    formData.append('nonce', unmaskNotebook.nonce);
    formData.append('field_key', fieldKey);
    formData.append('value', value);

    fetch(unmaskNotebook.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('saved', 'info');
      } else {
        console.error('Save field value error:', data);
        showToast('error saving', 'error');
      }
    })
    .catch(function(err) {
      console.error('Save field value error:', err);
      showToast('error saving', 'error');
    });
  }

  function saveCustomFieldValue(fieldId, value) {
    const formData = new FormData();
    formData.append('action', 'unmask_update_custom_field_value');
    formData.append('nonce', unmaskNotebook.nonce);
    formData.append('field_id', fieldId);
    formData.append('value', value);

    fetch(unmaskNotebook.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('saved', 'info');
      } else {
        console.error('Save custom field value error:', data);
        showToast('error saving', 'error');
      }
    })
    .catch(function(err) {
      console.error('Save custom field value error:', err);
      showToast('error saving', 'error');
    });
  }

  /**
   * Escape HTML helper
   */
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
