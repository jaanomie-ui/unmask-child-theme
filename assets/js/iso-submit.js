/**
 * UNMASK ISO Submit Form
 * Multi-step wizard for ISO Board submissions
 *
 * @package UNMASK
 * @since 1.0.0
 */

(function () {
    'use strict';

    // Form state
    var formData = {
        type: null,
        category: null,
        title: '',
        description: '',
        location: 'chicago',
        factory: 'open',
        expiration: null
    };

    var currentStep = 1;
    var totalSteps = 5;
    var isSubmitting = false;

    // DOM elements (cached on init)
    var elements = {};

    /**
     * Initialize the form
     */
    function init() {
        // Cache DOM elements
        elements.progressBar = document.getElementById('isoProgressBar');
        elements.formError = document.getElementById('isoFormError');
        elements.errorText = document.getElementById('isoErrorText');
        elements.successMessage = document.getElementById('isoSuccessMessage');
        elements.nonce = document.getElementById('isoNonce');
        elements.ajaxUrl = document.getElementById('isoAjaxUrl');

        // Input fields
        elements.titleInput = document.getElementById('isoTitle');
        elements.descInput = document.getElementById('isoDescription');
        elements.charCount = document.getElementById('isoCharCount');
        elements.expirationInput = document.getElementById('isoExpiration');

        // Navigation buttons
        elements.step1Next = document.getElementById('step1Next');
        elements.step2Next = document.getElementById('step2Next');
        elements.step3Next = document.getElementById('step3Next');
        elements.step4Next = document.getElementById('step4Next');
        elements.submitBtn = document.getElementById('isoSubmitBtn');

        // Set initial expiration value from form
        if (elements.expirationInput && elements.expirationInput.value) {
            formData.expiration = elements.expirationInput.value;
        }

        // Bind event listeners
        bindEvents();
    }

    /**
     * Bind all event listeners
     */
    function bindEvents() {
        // Type buttons
        document.querySelectorAll('.iso-type-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                selectType(this.dataset.type);
            });
        });

        // Category buttons
        document.querySelectorAll('.iso-category-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                selectCategory(this.dataset.category);
            });
        });

        // Location buttons
        document.querySelectorAll('.iso-location-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                selectLocation(this.dataset.location);
            });
        });

        // Factory options
        document.querySelectorAll('.iso-factory-option').forEach(function (opt) {
            opt.addEventListener('click', function () {
                selectFactory(this.dataset.factory);
            });
        });

        // Text inputs
        if (elements.titleInput) {
            elements.titleInput.addEventListener('input', validateStep3);
        }
        if (elements.descInput) {
            elements.descInput.addEventListener('input', function () {
                updateCharCount();
                validateStep3();
            });
        }
        if (elements.expirationInput) {
            elements.expirationInput.addEventListener('change', function () {
                formData.expiration = this.value;
            });
        }

        // Navigation buttons
        if (elements.step1Next) {
            elements.step1Next.addEventListener('click', nextStep);
        }
        if (elements.step2Next) {
            elements.step2Next.addEventListener('click', nextStep);
        }
        if (elements.step3Next) {
            elements.step3Next.addEventListener('click', nextStep);
        }
        if (elements.step4Next) {
            elements.step4Next.addEventListener('click', nextStep);
        }

        // Back buttons
        document.querySelectorAll('.iso-btn-back').forEach(function (btn) {
            btn.addEventListener('click', prevStep);
        });

        // Submit button
        if (elements.submitBtn) {
            elements.submitBtn.addEventListener('click', submitForm);
        }
    }

    /**
     * Type selection (seeking/offering)
     */
    function selectType(type) {
        formData.type = type;
        document.querySelectorAll('.iso-type-btn').forEach(function (btn) {
            btn.classList.toggle('selected', btn.dataset.type === type);
        });
        if (elements.step1Next) {
            elements.step1Next.disabled = false;
        }
        hideError();
    }

    /**
     * Category selection
     */
    function selectCategory(category) {
        formData.category = category;
        document.querySelectorAll('.iso-category-btn').forEach(function (btn) {
            btn.classList.toggle('selected', btn.dataset.category === category);
        });
        if (elements.step2Next) {
            elements.step2Next.disabled = false;
        }
        hideError();
    }

    /**
     * Location selection
     */
    function selectLocation(location) {
        formData.location = location;
        document.querySelectorAll('.iso-location-btn').forEach(function (btn) {
            btn.classList.toggle('selected', btn.dataset.location === location);
        });
    }

    /**
     * Factory selection
     */
    function selectFactory(factory) {
        formData.factory = factory;
        document.querySelectorAll('.iso-factory-option').forEach(function (opt) {
            opt.classList.toggle('selected', opt.dataset.factory === factory);
        });
    }

    /**
     * Update character count for description
     */
    function updateCharCount() {
        if (!elements.descInput || !elements.charCount) return;

        var count = elements.descInput.value.length;
        elements.charCount.textContent = count + ' / 1000';
        elements.charCount.classList.remove('warning', 'error');

        if (count > 900) {
            elements.charCount.classList.add('warning');
        }
        if (count >= 1000) {
            elements.charCount.classList.add('error');
        }
    }

    /**
     * Validate step 3 (title + description)
     */
    function validateStep3() {
        if (!elements.titleInput || !elements.descInput) return;

        var title = elements.titleInput.value.trim();
        var desc = elements.descInput.value.trim();

        formData.title = title;
        formData.description = desc;

        var isValid = title.length >= 10 && desc.length >= 20;

        if (elements.step3Next) {
            elements.step3Next.disabled = !isValid;
        }

        if (isValid) {
            hideError();
        }
    }

    /**
     * Navigate to next step
     */
    function nextStep() {
        // Validate current step
        if (!validateCurrentStep()) {
            return;
        }

        if (currentStep < totalSteps) {
            // Hide current step
            var currentStepEl = document.querySelector('.iso-form-step[data-step="' + currentStep + '"]');
            var currentProgressEl = document.querySelector('.iso-progress-step[data-step="' + currentStep + '"]');

            if (currentStepEl) currentStepEl.classList.remove('active');
            if (currentProgressEl) currentProgressEl.classList.add('complete');

            // Show next step
            currentStep++;
            var nextStepEl = document.querySelector('.iso-form-step[data-step="' + currentStep + '"]');
            var nextProgressEl = document.querySelector('.iso-progress-step[data-step="' + currentStep + '"]');

            if (nextStepEl) nextStepEl.classList.add('active');
            if (nextProgressEl) nextProgressEl.classList.add('active');

            // Update review if on step 5
            if (currentStep === 5) {
                updateReview();
            }

            hideError();
        }
    }

    /**
     * Navigate to previous step
     */
    function prevStep() {
        if (currentStep > 1) {
            // Hide current step
            var currentStepEl = document.querySelector('.iso-form-step[data-step="' + currentStep + '"]');
            var currentProgressEl = document.querySelector('.iso-progress-step[data-step="' + currentStep + '"]');

            if (currentStepEl) currentStepEl.classList.remove('active');
            if (currentProgressEl) currentProgressEl.classList.remove('active');

            // Show previous step
            currentStep--;
            var prevStepEl = document.querySelector('.iso-form-step[data-step="' + currentStep + '"]');
            var prevProgressEl = document.querySelector('.iso-progress-step[data-step="' + currentStep + '"]');

            if (prevStepEl) prevStepEl.classList.add('active');
            if (prevProgressEl) prevProgressEl.classList.remove('complete');

            hideError();
        }
    }

    /**
     * Validate current step before proceeding
     */
    function validateCurrentStep() {
        switch (currentStep) {
            case 1:
                if (!formData.type) {
                    showError('please select seeking or offering');
                    return false;
                }
                break;
            case 2:
                if (!formData.category) {
                    showError('please select a category');
                    return false;
                }
                break;
            case 3:
                if (formData.title.length < 10) {
                    showError('title must be at least 10 characters');
                    return false;
                }
                if (formData.description.length < 20) {
                    showError('description must be at least 20 characters');
                    return false;
                }
                break;
            case 4:
                if (!formData.expiration) {
                    showError('please select an expiration date');
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * Update review section with form data
     */
    function updateReview() {
        setReviewText('reviewType', formData.type);
        setReviewText('reviewCategory', formData.category);
        setReviewText('reviewTitle', formData.title);
        setReviewText('reviewDescription', formData.description);
        setReviewText('reviewLocation', formData.location);

        // Format factory display
        var factoryDisplay = formData.factory;
        if (formData.factory === 'yes' || formData.factory === 'preferred') {
            factoryDisplay = 'hosting at the Factory';
        } else if (formData.factory === 'no') {
            factoryDisplay = 'not needed';
        }
        setReviewText('reviewFactory', factoryDisplay);

        // Format expiration date
        if (formData.expiration) {
            var expDate = new Date(formData.expiration + 'T00:00:00');
            var formatted = expDate.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            setReviewText('reviewExpiration', formatted);
        }
    }

    /**
     * Set review text helper
     */
    function setReviewText(id, text) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = text || '—';
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        if (elements.errorText) {
            elements.errorText.textContent = message;
        }
        if (elements.formError) {
            elements.formError.classList.add('active');
        }
    }

    /**
     * Hide error message
     */
    function hideError() {
        if (elements.formError) {
            elements.formError.classList.remove('active');
        }
    }

    /**
     * Submit form via AJAX
     */
    function submitForm() {
        if (isSubmitting) return;

        // Final validation
        if (!formData.type || !formData.category || !formData.title || !formData.description) {
            showError('please complete all required fields');
            return;
        }

        isSubmitting = true;

        // Update button state
        if (elements.submitBtn) {
            elements.submitBtn.classList.add('loading');
            elements.submitBtn.textContent = '[posting...]';
        }

        // Get nonce and URL
        var nonce = elements.nonce ? elements.nonce.value : '';
        var ajaxUrl = elements.ajaxUrl ? elements.ajaxUrl.value : '/wp-admin/admin-ajax.php';

        // Build form data
        var params = new URLSearchParams();
        params.append('action', 'unmask_iso_submit');
        params.append('nonce', nonce);
        params.append('iso_type', formData.type);
        params.append('iso_category', formData.category);
        params.append('iso_title', formData.title);
        params.append('iso_description', formData.description);
        params.append('iso_location', formData.location);
        params.append('iso_factory', formData.factory);
        params.append('iso_expiration', formData.expiration);

        // Submit via fetch
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params.toString()
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    showSuccess();
                } else {
                    showError(data.data.message || 'failed to create listing');
                    resetSubmitButton();
                }
            })
            .catch(function (err) {
                console.error('ISO submit error:', err);
                showError('network error. please try again.');
                resetSubmitButton();
            });
    }

    /**
     * Reset submit button state
     */
    function resetSubmitButton() {
        isSubmitting = false;
        if (elements.submitBtn) {
            elements.submitBtn.classList.remove('loading');
            elements.submitBtn.textContent = '[post listing]';
        }
    }

    /**
     * Show success state and redirect
     */
    function showSuccess() {
        // Hide all steps
        document.querySelectorAll('.iso-form-step').forEach(function (step) {
            step.classList.remove('active');
        });

        // Hide progress bar
        if (elements.progressBar) {
            elements.progressBar.classList.add('hidden');
        }

        // Show success message
        if (elements.successMessage) {
            elements.successMessage.classList.add('active');
        }

        // Redirect back to ISO board after 2 seconds
        setTimeout(function () {
            window.location.href = '/iso-board/';
        }, 2000);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
