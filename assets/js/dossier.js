/**
 * UNMASK Dossier v3 Interactions
 *
 * Handles:
 * - Visibility toggle (mask/unmask)
 * - ISO expiration
 *
 * @package UNMASK
 */

(function($) {
    'use strict';

    // Bail if not on dossier page
    if (typeof unmaskDossier === 'undefined') {
        return;
    }

    /**
     * Visibility toggle
     */
    $('.my-unmask-visibility__btn').on('click', function() {
        var $btn = $(this);
        var visibility = $btn.data('visibility');

        // Optimistic UI update
        $('.my-unmask-visibility__btn').removeClass('active');
        $btn.addClass('active');

        $.post(unmaskDossier.ajaxUrl, {
            action: 'unmask_toggle_visibility',
            nonce: unmaskDossier.nonce,
            visibility: visibility
        }, function(response) {
            if (!response.success) {
                // Revert on error
                $('.my-unmask-visibility__btn').removeClass('active');
                $('[data-visibility="' + (visibility === 'masked' ? 'unmasked' : 'masked') + '"]').addClass('active');
                console.error('Visibility toggle failed:', response.data);
            }
        }).fail(function() {
            console.error('Visibility toggle request failed');
        });
    });

    /**
     * Expire ISO
     */
    $(document).on('click', '.dossier-iso__expire', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var $row = $btn.closest('.dossier-iso-row');
        var isoId = $row.data('iso-id') || $btn.data('iso-id');

        if (!isoId) {
            console.error('No ISO ID found');
            return;
        }

        if (!confirm('Expire this ISO? This cannot be undone.')) {
            return;
        }

        // Disable button during request
        $btn.prop('disabled', true).text('expiring...');

        $.post(unmaskDossier.ajaxUrl, {
            action: 'unmask_expire_iso',
            nonce: unmaskDossier.nonce,
            iso_id: isoId
        }, function(response) {
            if (response.success) {
                $row.fadeOut(300, function() {
                    $(this).remove();

                    // Check if any ISOs remain
                    if ($('.dossier-iso-row').length === 0) {
                        $('.dossier-iso-list').replaceWith(
                            '<div class="dossier-empty">no active ISOs — <a href="/iso-board/">post one</a></div>'
                        );
                    }
                });
            } else {
                alert('Failed to expire ISO: ' + (response.data.message || 'Unknown error'));
                $btn.prop('disabled', false).text('expire');
            }
        }).fail(function() {
            alert('Request failed. Please try again.');
            $btn.prop('disabled', false).text('expire');
        });
    });

    /**
     * Mark record as read (for single record pages)
     */
    $(document).on('click', '.unmask-mark-read', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var recordId = $btn.data('record-id');

        if (!recordId) {
            return;
        }

        $btn.prop('disabled', true).text('marking...');

        $.post(unmaskDossier.ajaxUrl, {
            action: 'unmask_mark_record_read',
            nonce: unmaskDossier.nonce,
            record_id: recordId
        }, function(response) {
            if (response.success) {
                $btn.replaceWith('<span class="unmask-read-status">read</span>');
            } else {
                $btn.prop('disabled', false).text('mark as read');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('mark as read');
        });
    });

    /**
     * Preview Mode Toggle
     * Allows profile owner to see their dossier as different visibility levels
     */
    var $previewToggle = $('#preview-toggle');
    var $previewBar = $('#preview-bar');
    var $previewClose = $('#preview-close');
    var $previewOptions = $('.dossier-preview-option');
    var previewClasses = ['preview-as-visitor', 'preview-as-user', 'preview-as-interlinked'];

    // Toggle preview mode on/off
    $previewToggle.on('click', function() {
        var isActive = $(this).attr('data-preview-active') === 'true';

        if (isActive) {
            // Exit preview mode
            exitPreviewMode();
        } else {
            // Enter preview mode with default (visitor)
            enterPreviewMode('visitor');
        }
    });

    // Select preview level
    $previewOptions.on('click', function() {
        var level = $(this).attr('data-preview-level');
        setPreviewLevel(level);
    });

    // Exit preview via close button
    $previewClose.on('click', function() {
        exitPreviewMode();
    });

    function enterPreviewMode(level) {
        $previewBar.removeAttr('hidden');
        $previewToggle
            .attr('data-preview-active', 'true')
            .text('exit preview');
        setPreviewLevel(level);
    }

    function exitPreviewMode() {
        // Remove all preview classes from body
        $('body').removeClass(previewClasses.join(' '));

        // Hide preview bar
        $previewBar.attr('hidden', '');

        // Reset toggle button
        $previewToggle
            .attr('data-preview-active', 'false')
            .text('preview as visitor');

        // Clear active state
        $previewOptions.removeClass('is-active');
    }

    function setPreviewLevel(level) {
        // Remove all preview classes first
        $('body').removeClass(previewClasses.join(' '));

        // Add the selected preview class
        $('body').addClass('preview-as-' + level);

        // Update active state on options
        $previewOptions.removeClass('is-active');
        $('[data-preview-level="' + level + '"]').addClass('is-active');
    }

    // Escape key exits preview mode
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $previewToggle.attr('data-preview-active') === 'true') {
            exitPreviewMode();
        }
    });

})(jQuery);
