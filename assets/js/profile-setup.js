/**
 * UNMASK Profile Setup
 * Guided profile creation flow for new users.
 *
 * Sections:
 * 1. Identity (Scene Name, Pronouns)
 * 2. Practice (What do you practice?)
 * 3. Status (Availability toggles)
 */

(function() {
  'use strict';

  // Configuration from PHP (set via wp_localize_script)
  var config = window.unmaskProfileSetup || {
    ajaxUrl: '/wp-admin/admin-ajax.php',
    nonce: '',
    practiceOptions: [],
  };

  // State
  var state = {
    currentSection: 0,
    totalSections: 3,
    data: {
      scene_name: '',
      pronouns: '',
      practice: [],
      available_photographed: false,
      available_photograph: false,
      open_bookings: false,
    },
    isSaving: false,
  };

  // DOM Elements (cached after init)
  var els = {};

  /**
   * Initialize the profile setup
   */
  function init() {
    // Cache DOM elements
    els.scrim = document.querySelector('.profile-setup__scrim');
    els.panel = document.querySelector('.profile-setup__panel');
    els.sections = document.querySelectorAll('.profile-setup__section');
    els.dots = document.querySelectorAll('.profile-setup__dot');
    els.ctaButton = document.querySelector('[data-profile-setup-start]');
    els.skipLink = document.querySelector('[data-profile-setup-skip]');

    if (!els.panel) return;

    // Bind events
    bindEvents();
  }

  /**
   * Bind event listeners
   */
  function bindEvents() {
    // Start button
    if (els.ctaButton) {
      els.ctaButton.addEventListener('click', openSheet);
    }

    // Skip link on welcome screen
    if (els.skipLink) {
      els.skipLink.addEventListener('click', handleSkip);
    }

    // Scrim click to close
    if (els.scrim) {
      els.scrim.addEventListener('click', closeSheet);
    }

    // Close button
    var closeBtn = document.querySelector('.profile-setup__close');
    if (closeBtn) {
      closeBtn.addEventListener('click', closeSheet);
    }

    // Next buttons
    document.querySelectorAll('[data-profile-next]').forEach(function(btn) {
      btn.addEventListener('click', handleNext);
    });

    // Skip section buttons
    document.querySelectorAll('[data-profile-skip-section]').forEach(function(btn) {
      btn.addEventListener('click', handleSkipSection);
    });

    // Multi-select options
    document.querySelectorAll('.profile-setup__option').forEach(function(opt) {
      opt.addEventListener('click', handleOptionToggle);
    });

    // Toggle switches
    document.querySelectorAll('.profile-setup__toggle').forEach(function(toggle) {
      toggle.addEventListener('click', handleToggle);
    });

    // Input fields
    document.querySelectorAll('.profile-setup__input').forEach(function(input) {
      input.addEventListener('input', handleInputChange);
    });

    // Keyboard: Escape to close
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && els.panel.classList.contains('is-active')) {
        closeSheet();
      }
    });
  }

  /**
   * Open the bottom sheet
   */
  function openSheet(e) {
    if (e) e.preventDefault();

    if (els.scrim) els.scrim.classList.add('is-active');
    if (els.panel) els.panel.classList.add('is-active');

    // Lock body scroll
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(function() {
      var firstInput = els.panel.querySelector('.profile-setup__input');
      if (firstInput) firstInput.focus();
    }, 300);
  }

  /**
   * Close the bottom sheet
   */
  function closeSheet() {
    if (els.scrim) els.scrim.classList.remove('is-active');
    if (els.panel) els.panel.classList.remove('is-active');

    // Unlock body scroll
    document.body.style.overflow = '';
  }

  /**
   * Handle skip (skip entire flow)
   */
  function handleSkip(e) {
    if (e) e.preventDefault();

    // Mark onboarding as complete and redirect
    saveAndRedirect(true);
  }

  /**
   * Handle next button click
   */
  function handleNext(e) {
    if (e) e.preventDefault();

    var btn = e.target;
    var currentSection = state.currentSection;

    // Validate current section
    if (!validateSection(currentSection)) {
      return;
    }

    // If last section, save and complete
    if (currentSection === state.totalSections - 1) {
      saveProfile();
      return;
    }

    // Move to next section
    goToSection(currentSection + 1);
  }

  /**
   * Handle skip section button
   */
  function handleSkipSection(e) {
    if (e) e.preventDefault();

    var currentSection = state.currentSection;

    // If last section, save what we have and complete
    if (currentSection === state.totalSections - 1) {
      saveProfile();
      return;
    }

    // Move to next section without validation
    goToSection(currentSection + 1);
  }

  /**
   * Navigate to a specific section
   */
  function goToSection(index) {
    if (index < 0 || index >= state.totalSections) return;

    state.currentSection = index;

    // Update sections visibility
    els.sections.forEach(function(section, i) {
      section.classList.toggle('is-active', i === index);
      section.style.display = i === index ? 'block' : 'none';
    });

    // Update progress dots
    els.dots.forEach(function(dot, i) {
      dot.classList.remove('profile-setup__dot--active', 'profile-setup__dot--complete');
      if (i < index) {
        dot.classList.add('profile-setup__dot--complete');
      } else if (i === index) {
        dot.classList.add('profile-setup__dot--active');
      }
    });

    // Focus first input in new section
    setTimeout(function() {
      var firstInput = els.sections[index].querySelector('.profile-setup__input');
      if (firstInput) firstInput.focus();
    }, 100);
  }

  /**
   * Validate current section
   */
  function validateSection(index) {
    var section = els.sections[index];
    if (!section) return true;

    var valid = true;
    var errors = section.querySelectorAll('.profile-setup__error');

    // Clear previous errors
    errors.forEach(function(err) {
      err.textContent = '';
    });

    // Section 1: Identity - Scene Name is required
    if (index === 0) {
      var sceneNameInput = section.querySelector('[name="scene_name"]');
      if (sceneNameInput && !sceneNameInput.value.trim()) {
        showFieldError(sceneNameInput, 'Scene name is required');
        valid = false;
      } else if (sceneNameInput) {
        state.data.scene_name = sceneNameInput.value.trim();
      }

      var pronounsInput = section.querySelector('[name="pronouns"]');
      if (pronounsInput) {
        state.data.pronouns = pronounsInput.value.trim();
      }
    }

    // Section 2: Practice - optional
    if (index === 1) {
      var selected = section.querySelectorAll('.profile-setup__option.is-selected');
      state.data.practice = [];
      selected.forEach(function(opt) {
        state.data.practice.push(opt.dataset.value);
      });
    }

    // Section 3: Status - optional
    if (index === 2) {
      var toggles = section.querySelectorAll('.profile-setup__toggle');
      toggles.forEach(function(toggle) {
        var field = toggle.dataset.field;
        var isOn = toggle.classList.contains('is-on');
        state.data[field] = isOn;
      });
    }

    return valid;
  }

  /**
   * Show field error
   */
  function showFieldError(input, message) {
    input.classList.add('profile-setup__input--error');
    var errorEl = input.parentElement.querySelector('.profile-setup__error');
    if (errorEl) {
      errorEl.textContent = message;
    }

    // Clear error on input
    input.addEventListener('input', function clearError() {
      input.classList.remove('profile-setup__input--error');
      if (errorEl) errorEl.textContent = '';
      input.removeEventListener('input', clearError);
    });
  }

  /**
   * Handle input change
   */
  function handleInputChange(e) {
    var input = e.target;
    var name = input.name;
    if (name) {
      state.data[name] = input.value;
    }
  }

  /**
   * Handle multi-select option toggle
   */
  function handleOptionToggle(e) {
    var option = e.currentTarget;
    option.classList.toggle('is-selected');
  }

  /**
   * Handle toggle switch
   */
  function handleToggle(e) {
    var toggle = e.currentTarget;
    toggle.classList.toggle('is-on');
  }

  /**
   * Save profile to server
   */
  function saveProfile() {
    if (state.isSaving) return;

    // Validate last section
    if (!validateSection(state.currentSection)) {
      return;
    }

    state.isSaving = true;

    // Show loading state
    var submitBtn = els.sections[state.currentSection].querySelector('[data-profile-next]');
    if (submitBtn) {
      submitBtn.classList.add('profile-setup__next--loading');
      submitBtn.disabled = true;
    }

    // Prepare data
    var formData = new FormData();
    formData.append('action', 'unmask_save_profile_setup');
    formData.append('nonce', config.nonce);
    formData.append('scene_name', state.data.scene_name);
    formData.append('pronouns', state.data.pronouns);
    formData.append('practice', JSON.stringify(state.data.practice));
    formData.append('available_photographed', state.data.available_photographed ? '1' : '0');
    formData.append('available_photograph', state.data.available_photograph ? '1' : '0');
    formData.append('open_bookings', state.data.open_bookings ? '1' : '0');

    // Send AJAX request
    fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
    })
    .then(function(response) {
      return response.json();
    })
    .then(function(data) {
      state.isSaving = false;

      if (data.success) {
        showCompletion();
      } else {
        showError(data.data || 'Failed to save profile');
        if (submitBtn) {
          submitBtn.classList.remove('profile-setup__next--loading');
          submitBtn.disabled = false;
        }
      }
    })
    .catch(function(error) {
      state.isSaving = false;
      showError('Network error. Please try again.');
      if (submitBtn) {
        submitBtn.classList.remove('profile-setup__next--loading');
        submitBtn.disabled = false;
      }
    });
  }

  /**
   * Show completion screen
   */
  function showCompletion() {
    // Hide all sections
    els.sections.forEach(function(section) {
      section.style.display = 'none';
    });

    // Show completion section
    var completeSection = document.querySelector('.profile-setup__complete');
    if (completeSection) {
      completeSection.style.display = 'block';
    }

    // Update all dots to complete
    els.dots.forEach(function(dot) {
      dot.classList.remove('profile-setup__dot--active');
      dot.classList.add('profile-setup__dot--complete');
    });
  }

  /**
   * Show error message
   */
  function showError(message) {
    // Find or create error container
    var errorContainer = document.querySelector('.profile-setup__global-error');
    if (!errorContainer) {
      errorContainer = document.createElement('div');
      errorContainer.className = 'profile-setup__global-error';
      errorContainer.style.cssText = 'padding: 12px; background: rgba(138,50,51,0.15); border: 1px solid var(--primitive-red); color: var(--primitive-red-light); font-size: 14px; margin-bottom: 16px;';
      var body = els.panel.querySelector('.unmask-sheet__body');
      if (body) body.insertBefore(errorContainer, body.firstChild);
    }
    errorContainer.textContent = message;
    errorContainer.style.display = 'block';

    // Auto-hide after 5 seconds
    setTimeout(function() {
      errorContainer.style.display = 'none';
    }, 5000);
  }

  /**
   * Save and redirect (for skip)
   */
  function saveAndRedirect(isSkip) {
    var formData = new FormData();
    formData.append('action', 'unmask_complete_onboarding');
    formData.append('nonce', config.nonce);
    formData.append('skipped', isSkip ? '1' : '0');

    fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
    })
    .then(function() {
      // Redirect to home or profile
      window.location.href = config.redirectUrl || '/';
    })
    .catch(function() {
      // Redirect anyway
      window.location.href = config.redirectUrl || '/';
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
