/**
 * Rehearsal Planning Form
 * Handles date/gear selection and AJAX save
 *
 * @package UNMASK
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // State
    let selectedDates = [];
    let selectedGear = [];
    let notes = '';

    // Initialize
    function init() {
        loadSavedState();
        bindEvents();
        updateCounts();
    }

    // Load saved state from checkboxes
    function loadSavedState() {
        // Load selected dates
        $('.date-item input[type="checkbox"]:checked').each(function() {
            selectedDates.push($(this).val());
        });

        // Load selected gear
        $('.gear-item input[type="checkbox"]:checked').each(function() {
            selectedGear.push($(this).val());
        });

        // Load notes
        notes = $('#rehearsal-notes').val();
    }

    // Bind event handlers
    function bindEvents() {
        // Make entire date row clickable (but not if clicking label)
        $('.date-item').on('click', function(e) {
            // If clicking on label, checkbox, or inside a label, let native behavior handle it
            if ($(e.target).is('label') || $(e.target).closest('label').length > 0 || $(e.target).is('input') || $(e.target).hasClass('checkbox-box')) {
                return;
            }

            const $checkbox = $(this).find('input[type="checkbox"]');
            $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        });

        // Date selection - update visual state
        $('.date-item input[type="checkbox"]').on('change', function() {
            const dateValue = $(this).val();
            const $item = $(this).closest('.date-item');

            if ($(this).is(':checked')) {
                $item.addClass('selected');
                if (!selectedDates.includes(dateValue)) {
                    selectedDates.push(dateValue);
                }
            } else {
                $item.removeClass('selected');
                selectedDates = selectedDates.filter(d => d !== dateValue);
            }

            updateCounts();
        });

        // Make entire gear row clickable (but not if clicking label)
        $('.gear-item').on('click', function(e) {
            // If clicking on label, checkbox, or inside a label, let native behavior handle it
            if ($(e.target).is('label') || $(e.target).closest('label').length > 0 || $(e.target).is('input') || $(e.target).hasClass('checkbox-box')) {
                return;
            }

            const $checkbox = $(this).find('input[type="checkbox"]');
            $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        });

        // Gear selection - update visual state
        $('.gear-item input[type="checkbox"]').on('change', function() {
            const gearValue = $(this).val();
            const $item = $(this).closest('.gear-item');

            if ($(this).is(':checked')) {
                $item.addClass('checked');
                if (!selectedGear.includes(gearValue)) {
                    selectedGear.push(gearValue);
                }
            } else {
                $item.removeClass('checked');
                selectedGear = selectedGear.filter(g => g !== gearValue);
            }

            updateCounts();
        });

        // Notes
        $('#rehearsal-notes').on('input', function() {
            notes = $(this).val();
        });

        // Clear button
        $('.btn-clear-rehearsal').on('click', function() {
            if (confirm('Clear all selections and notes?')) {
                clearForm();
            }
        });

        // Save button
        $('.btn-save-rehearsal').on('click', function() {
            if ($(this).is('a')) return; // Let link work normally
            saveRehearsalPlan();
        });
    }

    // Update counts
    function updateCounts() {
        $('.dates-count').text(selectedDates.length);
        $('.gear-count').text(selectedGear.length);
    }

    // Clear form
    function clearForm() {
        selectedDates = [];
        selectedGear = [];
        notes = '';

        $('.date-item input[type="checkbox"]').prop('checked', false);
        $('.date-item').removeClass('selected');

        $('.gear-item input[type="checkbox"]').prop('checked', false);
        $('.gear-item').removeClass('checked');

        $('#rehearsal-notes').val('');

        updateCounts();
    }

    // Save rehearsal plan via AJAX
    function saveRehearsalPlan() {
        const $btn = $('.btn-save-rehearsal');
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('saving...');

        $.ajax({
            url: unmask_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'save_rehearsal_plan',
                nonce: unmask_ajax.nonce,
                selected_dates: selectedDates,
                gear_bringing: selectedGear,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    $btn.text('saved ✓');
                    setTimeout(function() {
                        $btn.text(originalText).prop('disabled', false);
                    }, 2000);

                    // Show success message
                    showNotification('Rehearsal plan submitted successfully', 'success');
                } else {
                    $btn.text('error').prop('disabled', false);
                    setTimeout(function() {
                        $btn.text(originalText);
                    }, 2000);

                    showNotification(response.data || 'Failed to save plan', 'error');
                }
            },
            error: function() {
                $btn.text('error').prop('disabled', false);
                setTimeout(function() {
                    $btn.text(originalText);
                }, 2000);

                showNotification('Network error. Please try again.', 'error');
            }
        });
    }

    // Show notification
    function showNotification(message, type) {
        const $notification = $('<div class="rehearsal-notification ' + type + '">' + message + '</div>');
        $('body').append($notification);

        setTimeout(function() {
            $notification.addClass('show');
        }, 10);

        setTimeout(function() {
            $notification.removeClass('show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
