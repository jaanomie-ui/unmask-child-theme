/**
 * UNMASK Dossier — Inline Editing & Visibility Toggles
 *
 * "editing the profile is an action within itself. not done in the backroom, but in the open."
 *
 * Handles:
 *   - Per-section visibility toggles (User/Visitor/Interlinked)
 *   - Inline field editing with AJAX save
 */

(function() {
    'use strict';

    // Ensure AJAX URL is available
    if (typeof ajaxurl === 'undefined') {
        window.ajaxurl = '/wp-admin/admin-ajax.php';
    }

    /**
     * Visibility Toggle Handler
     */
    class VisibilityToggle {
        constructor(element) {
            this.el = element;
            this.section = element.dataset.section;
            this.current = element.dataset.current;
            this.options = element.querySelectorAll('.visibility-toggle__option');
            this.hiddenInput = element.querySelector('input[type="hidden"]');

            this.bindEvents();
        }

        bindEvents() {
            this.options.forEach(option => {
                option.addEventListener('click', (e) => this.handleClick(e));
            });
        }

        handleClick(e) {
            const option = e.currentTarget;
            if (option.disabled) return;

            const level = option.dataset.level;
            if (level === this.current) return;

            // Update UI
            this.options.forEach(opt => opt.classList.remove('is-active'));
            option.classList.add('is-active');

            // Update state
            this.current = level;
            this.el.dataset.current = level;
            if (this.hiddenInput) {
                this.hiddenInput.value = level;
            }

            // Save to server
            this.save(level);
        }

        async save(level) {
            try {
                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'unmask_save_visibility',
                        section: this.section,
                        level: level,
                        nonce: unmaskDossier?.nonce || ''
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    console.error('Failed to save visibility:', data.message);
                }
            } catch (error) {
                console.error('Visibility save error:', error);
            }
        }
    }

    /**
     * Inline Editable Field Handler
     */
    class InlineEditable {
        constructor(element) {
            this.el = element;
            this.field = element.dataset.field;
            this.type = element.dataset.type || 'text';
            this.value = element.querySelector('.dossier-editable__value');
            this.input = element.querySelector('.dossier-editable__input');
            this.saveBtn = element.querySelector('.dossier-editable__save');
            this.cancelBtn = element.querySelector('.dossier-editable__cancel');
            this.originalValue = '';

            this.bindEvents();
        }

        bindEvents() {
            // Click to edit
            if (this.value) {
                this.value.addEventListener('click', () => this.startEdit());
            }

            // Save/Cancel buttons
            if (this.saveBtn) {
                this.saveBtn.addEventListener('click', () => this.save());
            }
            if (this.cancelBtn) {
                this.cancelBtn.addEventListener('click', () => this.cancel());
            }

            // Keyboard shortcuts
            if (this.input) {
                this.input.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.cancel();
                    } else if (e.key === 'Enter' && this.type === 'text') {
                        e.preventDefault();
                        this.save();
                    } else if (e.key === 'Enter' && e.ctrlKey) {
                        e.preventDefault();
                        this.save();
                    }
                });
            }
        }

        startEdit() {
            this.originalValue = this.value.textContent.trim();
            if (this.input) {
                this.input.value = this.originalValue;
            }
            this.el.classList.add('is-editing');
            if (this.input) {
                this.input.focus();
                // Move cursor to end
                this.input.setSelectionRange(this.input.value.length, this.input.value.length);
            }
        }

        cancel() {
            this.el.classList.remove('is-editing');
            if (this.input) {
                this.input.value = this.originalValue;
            }
        }

        async save() {
            const newValue = this.input ? this.input.value.trim() : '';

            // No change
            if (newValue === this.originalValue) {
                this.cancel();
                return;
            }

            this.el.classList.add('is-saving');

            try {
                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'unmask_save_profile_field',
                        field: this.field,
                        value: newValue,
                        nonce: unmaskDossier?.nonce || ''
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update display value
                    if (this.value) {
                        this.value.textContent = newValue || '(not set)';
                    }
                    this.originalValue = newValue;
                    this.el.classList.remove('is-editing', 'is-saving');
                } else {
                    console.error('Failed to save field:', data.message);
                    this.el.classList.remove('is-saving');
                    // Could show error UI here
                }
            } catch (error) {
                console.error('Save error:', error);
                this.el.classList.remove('is-saving');
            }
        }
    }

    /**
     * Initialize on DOM ready
     */
    function init() {
        // Initialize all visibility toggles
        document.querySelectorAll('.visibility-toggle').forEach(el => {
            new VisibilityToggle(el);
        });

        // Initialize all inline editables
        document.querySelectorAll('.dossier-editable').forEach(el => {
            new InlineEditable(el);
        });
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Export for potential use elsewhere
    window.UnmaskDossier = {
        VisibilityToggle,
        InlineEditable,
        init
    };
})();
