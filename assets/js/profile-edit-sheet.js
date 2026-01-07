/**
 * UNMASK Profile Edit — Split Panel + Bottom Sheet
 *
 * Desktop: Split panel with field list and detail editor
 * Mobile: Bottom sheet modal for editing
 *
 * @package UNMASK
 * @since 3.0.0
 */

(function() {
  'use strict';

  // Bail if not on edit page
  if (typeof unmaskProfileEdit === 'undefined') {
    return;
  }

  const MOBILE_BP = 768;

  // DOM elements
  let elements = {
    rows: null,
    detail: null,
    scrim: null,
    panel: null,
    handle: null,
    close: null,
    title: null,
    body: null
  };

  // State
  let state = {
    activeRow: null,
    activeField: null,
    isSheetOpen: false,
    touchStartY: 0,
    touchCurrentY: 0
  };

  /**
   * Initialize
   */
  function init() {
    // Cache DOM elements
    elements.rows = document.querySelectorAll('.unmask-edit__row');
    elements.detail = document.querySelector('.unmask-edit__detail');
    elements.scrim = document.querySelector('.unmask-sheet__scrim');
    elements.panel = document.querySelector('.unmask-sheet__panel');
    elements.handle = document.querySelector('.unmask-sheet__handle');
    elements.close = document.querySelector('.unmask-sheet__close');
    elements.title = document.querySelector('.unmask-sheet__title');
    elements.body = document.querySelector('.unmask-sheet__body');

    if (!elements.rows.length) {
      return;
    }

    // Bind row click handlers
    elements.rows.forEach(function(row) {
      row.addEventListener('click', handleRowClick);
    });

    // Sheet dismiss handlers
    if (elements.scrim) {
      elements.scrim.addEventListener('click', closeSheet);
    }

    if (elements.close) {
      elements.close.addEventListener('click', closeSheet);
    }

    // Keyboard handlers
    document.addEventListener('keydown', handleKeydown);

    // Swipe to dismiss
    initSwipeDismiss();

    // Handle window resize
    window.addEventListener('resize', handleResize);

    // Select first field on desktop
    if (window.innerWidth >= MOBILE_BP && elements.rows.length > 0) {
      // Delay to ensure DOM is ready
      setTimeout(function() {
        selectRow(elements.rows[0]);
      }, 100);
    }
  }

  /**
   * Handle row click
   */
  function handleRowClick(e) {
    var row = e.currentTarget;

    if (window.innerWidth >= MOBILE_BP) {
      // Desktop: select row and update detail panel
      selectRow(row);
    } else {
      // Mobile: open bottom sheet
      openSheet(row);
    }
  }

  /**
   * Select a row (desktop)
   */
  function selectRow(row) {
    // Remove active from all rows
    elements.rows.forEach(function(r) {
      r.classList.remove('unmask-edit__row--active');
    });

    // Add active to clicked row
    row.classList.add('unmask-edit__row--active');
    state.activeRow = row;

    // Update detail panel
    updateDetailPanel(row);

    // Scroll to page top so header is visible
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  /**
   * Update detail panel with field form
   */
  function updateDetailPanel(row) {
    if (!elements.detail) return;

    var fieldId = row.dataset.field;
    var fieldName = row.dataset.name || '';
    var fieldValue = row.dataset.value || '';
    var fieldType = row.dataset.type || 'text';
    var fieldHint = row.dataset.hint || '';
    var fieldOptions = row.dataset.options ? JSON.parse(row.dataset.options) : null;

    state.activeField = fieldId;

    var formHtml = buildFieldForm(fieldId, fieldName, fieldValue, fieldType, fieldHint, fieldOptions);
    elements.detail.innerHTML = formHtml;

    // Bind save handler
    var saveBtn = elements.detail.querySelector('.unmask-edit__field-save');
    if (saveBtn) {
      saveBtn.addEventListener('click', handleSave);
    }

    // Focus input
    var input = elements.detail.querySelector('.unmask-edit__field-input');
    if (input) {
      input.focus();
    }
  }

  /**
   * Open bottom sheet (mobile)
   */
  function openSheet(row) {
    if (!elements.scrim || !elements.panel) return;

    var fieldId = row.dataset.field;
    var fieldName = row.dataset.name || '';
    var fieldValue = row.dataset.value || '';
    var fieldType = row.dataset.type || 'text';
    var fieldHint = row.dataset.hint || '';
    var fieldOptions = row.dataset.options ? JSON.parse(row.dataset.options) : null;

    state.activeField = fieldId;
    state.activeRow = row;

    // Update sheet title
    if (elements.title) {
      elements.title.textContent = fieldName;
    }

    // Build sheet content
    if (elements.body) {
      var formHtml = buildSheetForm(fieldId, fieldName, fieldValue, fieldType, fieldHint, fieldOptions);
      elements.body.innerHTML = formHtml;

      // Bind save handler
      var saveBtn = elements.body.querySelector('.unmask-sheet__save');
      if (saveBtn) {
        saveBtn.addEventListener('click', handleSave);
      }
    }

    // Show sheet
    elements.scrim.classList.add('is-active');
    elements.panel.classList.add('is-active');
    document.body.style.overflow = 'hidden';
    state.isSheetOpen = true;

    // Focus input after animation
    setTimeout(function() {
      var input = elements.body.querySelector('.unmask-sheet__input');
      if (input) {
        input.focus();
      }
    }, 260);

    // Set aria attributes
    elements.panel.setAttribute('aria-hidden', 'false');
    elements.scrim.setAttribute('aria-hidden', 'false');
  }

  /**
   * Close bottom sheet
   */
  function closeSheet() {
    if (!elements.scrim || !elements.panel) return;

    elements.scrim.classList.remove('is-active');
    elements.panel.classList.remove('is-active');
    elements.panel.style.transform = '';
    document.body.style.overflow = '';
    state.isSheetOpen = false;

    // Set aria attributes
    elements.panel.setAttribute('aria-hidden', 'true');
    elements.scrim.setAttribute('aria-hidden', 'true');

    // Return focus to row
    if (state.activeRow) {
      state.activeRow.focus();
    }
  }

  /**
   * Build field form HTML (desktop detail panel)
   */
  function buildFieldForm(fieldId, fieldName, fieldValue, fieldType, fieldHint, fieldOptions) {
    var inputHtml = '';

    if (fieldType === 'textarea') {
      inputHtml = '<textarea class="unmask-edit__field-input unmask-edit__field-input--textarea" data-field="' + escapeHtml(fieldId) + '">' + escapeHtml(fieldValue) + '</textarea>';
    } else if (fieldType === 'select' && fieldOptions) {
      inputHtml = '<select class="unmask-edit__field-input unmask-edit__field-input--select" data-field="' + escapeHtml(fieldId) + '">';
      fieldOptions.forEach(function(opt) {
        var selected = opt.value === fieldValue ? ' selected' : '';
        inputHtml += '<option value="' + escapeHtml(opt.value) + '"' + selected + '>' + escapeHtml(opt.label) + '</option>';
      });
      inputHtml += '</select>';
    } else if (fieldType === 'multiselect' && fieldOptions) {
      var selectedValues = fieldValue ? fieldValue.split(',').map(function(v) { return v.trim(); }) : [];
      inputHtml = '<div class="unmask-edit__options" data-field="' + escapeHtml(fieldId) + '">';
      fieldOptions.forEach(function(opt) {
        var isSelected = selectedValues.indexOf(opt.value) !== -1;
        inputHtml += '<button type="button" class="unmask-edit__option' + (isSelected ? ' is-selected' : '') + '" data-value="' + escapeHtml(opt.value) + '">';
        inputHtml += escapeHtml(opt.label);
        inputHtml += '</button>';
      });
      inputHtml += '</div>';
    } else {
      inputHtml = '<input type="text" class="unmask-edit__field-input" value="' + escapeHtml(fieldValue) + '" data-field="' + escapeHtml(fieldId) + '">';
    }

    var html = '<div class="unmask-edit__field-form">';
    html += '<label class="unmask-edit__field-label">' + escapeHtml(fieldName) + '</label>';
    html += inputHtml;
    if (fieldHint) {
      html += '<p class="unmask-edit__field-hint">' + escapeHtml(fieldHint) + '</p>';
    }
    html += '<div class="unmask-edit__field-actions">';
    html += '<button type="button" class="unmask-edit__field-save">save</button>';
    html += '<span class="unmask-edit__field-success"><span>saved</span></span>';
    html += '</div>';
    html += '</div>';

    return html;
  }

  /**
   * Build sheet form HTML (mobile bottom sheet)
   */
  function buildSheetForm(fieldId, fieldName, fieldValue, fieldType, fieldHint, fieldOptions) {
    var inputHtml = '';

    if (fieldType === 'textarea') {
      inputHtml = '<textarea class="unmask-sheet__input unmask-sheet__input--textarea" data-field="' + escapeHtml(fieldId) + '">' + escapeHtml(fieldValue) + '</textarea>';
    } else if (fieldType === 'select' && fieldOptions) {
      inputHtml = '<select class="unmask-sheet__input unmask-sheet__input--select" data-field="' + escapeHtml(fieldId) + '">';
      fieldOptions.forEach(function(opt) {
        var selected = opt.value === fieldValue ? ' selected' : '';
        inputHtml += '<option value="' + escapeHtml(opt.value) + '"' + selected + '>' + escapeHtml(opt.label) + '</option>';
      });
      inputHtml += '</select>';
    } else if (fieldType === 'multiselect' && fieldOptions) {
      var selectedValues = fieldValue ? fieldValue.split(',').map(function(v) { return v.trim(); }) : [];
      inputHtml = '<div class="unmask-edit__options" data-field="' + escapeHtml(fieldId) + '">';
      fieldOptions.forEach(function(opt) {
        var isSelected = selectedValues.indexOf(opt.value) !== -1;
        inputHtml += '<button type="button" class="unmask-edit__option' + (isSelected ? ' is-selected' : '') + '" data-value="' + escapeHtml(opt.value) + '">';
        inputHtml += escapeHtml(opt.label);
        inputHtml += '</button>';
      });
      inputHtml += '</div>';
    } else {
      inputHtml = '<input type="text" class="unmask-sheet__input" value="' + escapeHtml(fieldValue) + '" data-field="' + escapeHtml(fieldId) + '">';
    }

    var html = '';
    html += inputHtml;
    if (fieldHint) {
      html += '<p class="unmask-sheet__hint">' + escapeHtml(fieldHint) + '</p>';
    }
    html += '<button type="button" class="unmask-sheet__save">save</button>';

    return html;
  }

  /**
   * Handle save button click
   */
  function handleSave(e) {
    var btn = e.currentTarget;
    var form = btn.closest('.unmask-edit__field-form') || btn.closest('.unmask-sheet__body');

    // Get input value
    var input = form.querySelector('input, textarea, select');
    var optionsContainer = form.querySelector('.unmask-edit__options');

    var fieldId;
    var newValue;

    if (optionsContainer) {
      // Multi-select: gather selected values
      fieldId = optionsContainer.dataset.field;
      var selected = optionsContainer.querySelectorAll('.is-selected');
      var values = [];
      selected.forEach(function(opt) {
        values.push(opt.dataset.value);
      });
      newValue = values.join(', ');
    } else if (input) {
      fieldId = input.dataset.field;
      newValue = input.value;
    } else {
      return;
    }

    // Disable button and show loading
    btn.disabled = true;
    btn.classList.add('unmask-edit__field-save--loading', 'unmask-sheet__save--loading');
    var originalText = btn.textContent;
    btn.textContent = 'saving...';

    // Make AJAX request
    var formData = new FormData();
    formData.append('action', 'unmask_update_profile_field');
    formData.append('nonce', unmaskProfileEdit.nonce);
    formData.append('field_id', fieldId);
    formData.append('value', newValue);

    fetch(unmaskProfileEdit.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function(response) {
      return response.json();
    })
    .then(function(data) {
      if (data.success) {
        // Update row value display
        var row = document.querySelector('[data-field="' + fieldId + '"]');
        if (row) {
          var valueEl = row.querySelector('.unmask-edit__value');
          if (valueEl) {
            valueEl.textContent = newValue || '\u2014'; // em dash for empty
            valueEl.classList.toggle('unmask-edit__value--empty', !newValue);
          }
          row.dataset.value = newValue;
        }

        // Show success indicator (desktop)
        var successEl = form.querySelector('.unmask-edit__field-success');
        if (successEl) {
          successEl.classList.add('is-visible');
          setTimeout(function() {
            successEl.classList.remove('is-visible');
          }, 2000);
        }

        // Close sheet on mobile
        if (window.innerWidth < MOBILE_BP && state.isSheetOpen) {
          setTimeout(closeSheet, 300);
        }
      } else {
        alert(data.data && data.data.message ? data.data.message : 'Save failed. Please try again.');
      }
    })
    .catch(function(error) {
      console.error('Save error:', error);
      alert('Save failed. Please try again.');
    })
    .finally(function() {
      btn.disabled = false;
      btn.classList.remove('unmask-edit__field-save--loading', 'unmask-sheet__save--loading');
      btn.textContent = originalText;
    });
  }

  /**
   * Initialize swipe-to-dismiss for sheet handle
   */
  function initSwipeDismiss() {
    if (!elements.handle || !elements.panel) return;

    elements.handle.addEventListener('touchstart', function(e) {
      state.touchStartY = e.touches[0].clientY;
    }, { passive: true });

    elements.handle.addEventListener('touchmove', function(e) {
      state.touchCurrentY = e.touches[0].clientY;
      var diff = state.touchCurrentY - state.touchStartY;

      // Only allow downward swipe
      if (diff > 0) {
        elements.panel.style.transform = 'translateY(' + diff + 'px)';
      }
    }, { passive: true });

    elements.handle.addEventListener('touchend', function() {
      var diff = state.touchCurrentY - state.touchStartY;

      // Reset transform
      elements.panel.style.transform = '';

      // Close if swiped more than 80px
      if (diff > 80) {
        closeSheet();
      }

      // Reset touch state
      state.touchStartY = 0;
      state.touchCurrentY = 0;
    });
  }

  /**
   * Handle keyboard events
   */
  function handleKeydown(e) {
    // Escape closes sheet
    if (e.key === 'Escape' && state.isSheetOpen) {
      closeSheet();
      return;
    }

    // Focus trap in sheet
    if (state.isSheetOpen && e.key === 'Tab') {
      trapFocus(e);
    }
  }

  /**
   * Trap focus within sheet
   */
  function trapFocus(e) {
    if (!elements.panel) return;

    var focusable = elements.panel.querySelectorAll(
      'button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );

    if (focusable.length === 0) return;

    var first = focusable[0];
    var last = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  /**
   * Handle window resize
   */
  function handleResize() {
    // Close sheet if resized to desktop
    if (window.innerWidth >= MOBILE_BP && state.isSheetOpen) {
      closeSheet();

      // Select the active row in desktop view
      if (state.activeRow) {
        selectRow(state.activeRow);
      }
    }
  }

  /**
   * Escape HTML for safe insertion
   */
  function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /**
   * Multi-select option click handler
   * Delegated from document for dynamic content
   */
  document.addEventListener('click', function(e) {
    var option = e.target.closest('.unmask-edit__option');
    if (option) {
      option.classList.toggle('is-selected');
    }
  });

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
