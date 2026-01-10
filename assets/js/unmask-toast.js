/**
 * UNMASK Toast Notification System
 * Usage: unmaskToast('Message here', 'error') or unmaskToast('Success!', 'success')
 */
(function() {
    'use strict';

    window.unmaskToast = function(message, type, duration) {
        type = type || 'default';
        duration = duration || 4000;

        // Remove existing toast if present
        var existing = document.querySelector('.unmask-toast');
        if (existing) existing.remove();

        // Create toast element
        var toast = document.createElement('div');
        toast.className = 'unmask-toast';
        if (type === 'error') toast.classList.add('unmask-toast--error');
        if (type === 'success') toast.classList.add('unmask-toast--success');
        toast.textContent = message;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');

        document.body.appendChild(toast);

        // Trigger animation
        requestAnimationFrame(function() {
            toast.classList.add('unmask-toast--visible');
        });

        // Auto-dismiss
        setTimeout(function() {
            toast.classList.remove('unmask-toast--visible');
            setTimeout(function() { toast.remove(); }, 300);
        }, duration);
    };
})();
