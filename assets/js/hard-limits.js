/**
 * Hard Limits Checklist — Traffic Light UI
 *
 * @package UNMASK
 * @since 1.0.0
 */

(function() {
    'use strict';

    // State: { activity_slug: 'red' | 'yellow' | 'green' }
    let state = {};
    let isDirty = false;

    // Level mapping for compact URL encoding
    const LEVEL_MAP = { green: 2, yellow: 1, red: 0 };
    const LEVEL_REVERSE = { 2: 'green', 1: 'yellow', 0: 'red' };

    /**
     * Initialize on DOM ready
     */
    function init() {
        // Load saved data from PHP or URL hash
        loadInitialState();

        // Bind traffic light buttons
        bindTrafficButtons();

        // Bind action buttons
        bindActionButtons();

        // Bind accordion toggles
        bindAccordion();

        // Update counts display
        updateCounts();
    }

    /**
     * Load initial state from saved data or URL hash
     */
    function loadInitialState() {
        // Check URL hash first (for shared links)
        if (window.location.hash && window.location.hash.length > 1) {
            try {
                const decoded = decodeFromHash(window.location.hash.slice(1));
                if (decoded && Object.keys(decoded).length > 0) {
                    state = decoded;
                    applyStateToUI();
                    showToast('loaded shared limits', 'success');
                    return;
                }
            } catch (e) {
                console.warn('Could not decode URL hash:', e);
            }
        }

        // Load from saved data (from PHP localization)
        if (typeof unmaskLimits !== 'undefined' && unmaskLimits.savedData) {
            try {
                const saved = typeof unmaskLimits.savedData === 'string'
                    ? JSON.parse(unmaskLimits.savedData)
                    : unmaskLimits.savedData;
                if (saved && Object.keys(saved).length > 0) {
                    state = saved;
                    applyStateToUI();
                }
            } catch (e) {
                console.warn('Could not parse saved data:', e);
            }
        }
    }

    /**
     * Apply current state to UI buttons
     */
    function applyStateToUI() {
        Object.entries(state).forEach(([slug, level]) => {
            const row = document.querySelector(`[data-activity="${slug}"]`);
            if (!row) return;

            // Remove all active states
            row.querySelectorAll('.traffic-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Set active button
            const activeBtn = row.querySelector(`.btn-${level}`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }
        });
    }

    /**
     * Bind click handlers to traffic light buttons
     */
    function bindTrafficButtons() {
        document.querySelectorAll('.traffic-btn').forEach(btn => {
            btn.addEventListener('click', handleTrafficClick);
        });
    }

    /**
     * Handle traffic button click
     */
    function handleTrafficClick(e) {
        const btn = e.currentTarget;
        const row = btn.closest('.activity-row');
        const slug = row.dataset.activity;
        const level = btn.dataset.level;

        // Get current state for this activity
        const currentLevel = state[slug];

        // Toggle: if already active, deselect. Otherwise, select.
        if (currentLevel === level) {
            // Deselect
            delete state[slug];
            btn.classList.remove('active');
        } else {
            // Select new level
            state[slug] = level;

            // Update UI
            row.querySelectorAll('.traffic-btn').forEach(b => {
                b.classList.remove('active');
            });
            btn.classList.add('active');
        }

        isDirty = true;
        updateCounts();
    }

    /**
     * Bind accordion toggle handlers
     */
    function bindAccordion() {
        // Category headers toggle their sections
        document.querySelectorAll('.category-header').forEach(header => {
            header.addEventListener('click', function() {
                const category = this.closest('.limits-category');
                category.classList.toggle('collapsed');
                updateToggleAllButton();
            });
        });

        // Toggle all button
        const toggleAllBtn = document.querySelector('.btn-toggle-all');
        if (toggleAllBtn) {
            toggleAllBtn.addEventListener('click', handleToggleAll);
        }
    }

    /**
     * Handle toggle all click
     */
    function handleToggleAll(e) {
        const btn = e.currentTarget;
        const action = btn.dataset.action;
        const categories = document.querySelectorAll('.limits-category');

        if (action === 'collapse') {
            categories.forEach(cat => cat.classList.add('collapsed'));
            btn.dataset.action = 'expand';
            btn.textContent = 'expand all';
        } else {
            categories.forEach(cat => cat.classList.remove('collapsed'));
            btn.dataset.action = 'collapse';
            btn.textContent = 'collapse all';
        }
    }

    /**
     * Update toggle all button text based on current state
     */
    function updateToggleAllButton() {
        const btn = document.querySelector('.btn-toggle-all');
        if (!btn) return;

        const categories = document.querySelectorAll('.limits-category');
        const collapsedCount = document.querySelectorAll('.limits-category.collapsed').length;

        if (collapsedCount === categories.length) {
            btn.dataset.action = 'expand';
            btn.textContent = 'expand all';
        } else if (collapsedCount === 0) {
            btn.dataset.action = 'collapse';
            btn.textContent = 'collapse all';
        }
    }

    /**
     * Bind action button handlers
     */
    function bindActionButtons() {
        const saveBtn = document.querySelector('.btn-save-limits');
        const shareBtn = document.querySelector('.btn-share-limits');
        const clearBtn = document.querySelector('.btn-clear-limits');

        if (saveBtn) saveBtn.addEventListener('click', handleSave);
        if (shareBtn) shareBtn.addEventListener('click', handleShare);
        if (clearBtn) clearBtn.addEventListener('click', handleClear);

        // Share modal close
        const closeModalBtn = document.querySelector('.btn-close-modal');
        const modalOverlay = document.querySelector('.share-modal-overlay');
        const copyBtn = document.querySelector('.btn-copy-url');

        if (closeModalBtn) closeModalBtn.addEventListener('click', closeShareModal);
        if (modalOverlay) {
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) closeShareModal();
            });
        }
        if (copyBtn) copyBtn.addEventListener('click', handleCopyUrl);
    }

    /**
     * Update counts display
     */
    function updateCounts() {
        const counts = { green: 0, yellow: 0, red: 0 };

        Object.values(state).forEach(level => {
            if (counts.hasOwnProperty(level)) {
                counts[level]++;
            }
        });

        // Update summary display
        const greenCount = document.querySelector('.summary-count.green');
        const yellowCount = document.querySelector('.summary-count.yellow');
        const redCount = document.querySelector('.summary-count.red');

        if (greenCount) greenCount.textContent = counts.green;
        if (yellowCount) yellowCount.textContent = counts.yellow;
        if (redCount) redCount.textContent = counts.red;

        // Update category counts
        document.querySelectorAll('.limits-category').forEach(cat => {
            const catSlug = cat.dataset.category;
            const activities = cat.querySelectorAll('.activity-row');
            let catCount = 0;

            activities.forEach(row => {
                if (state[row.dataset.activity]) {
                    catCount++;
                }
            });

            const countEl = cat.querySelector('.category-count');
            if (countEl) {
                countEl.textContent = `${catCount}/${activities.length}`;
            }
        });
    }

    /**
     * Save limits via AJAX
     */
    function handleSave() {
        if (typeof unmaskLimits === 'undefined') {
            showToast('configuration error', 'error');
            return;
        }

        if (!unmaskLimits.isLoggedIn) {
            showToast('sign in to file limits', 'error');
            return;
        }

        const saveBtn = document.querySelector('.btn-save-limits');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.classList.add('saving');
        }

        const formData = new FormData();
        formData.append('action', 'unmask_save_limits');
        formData.append('nonce', unmaskLimits.nonce);
        formData.append('limits', JSON.stringify(state));

        fetch(unmaskLimits.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                isDirty = false;
                showToast(data.data.message || 'limits filed', 'success');
                // Redirect to drone dashboard after brief delay
                setTimeout(() => {
                    window.location.href = '/drone-dashboard/';
                }, 1500);
            } else {
                showToast(data.data?.message || 'save failed', 'error');
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            showToast('network error', 'error');
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.classList.remove('saving');
            }
        });
    }

    /**
     * Open share modal with encoded URL
     */
    function handleShare() {
        const hash = encodeToHash(state);
        const url = window.location.origin + window.location.pathname + '#' + hash;

        const input = document.querySelector('.share-url-input');
        if (input) {
            input.value = url;
        }

        const modal = document.querySelector('.share-modal-overlay');
        if (modal) {
            modal.classList.add('visible');
            if (input) input.select();
        }
    }

    /**
     * Close share modal
     */
    function closeShareModal() {
        const modal = document.querySelector('.share-modal-overlay');
        if (modal) {
            modal.classList.remove('visible');
        }
    }

    /**
     * Copy URL to clipboard
     */
    function handleCopyUrl() {
        const input = document.querySelector('.share-url-input');
        if (!input) return;

        input.select();
        input.setSelectionRange(0, 99999);

        try {
            navigator.clipboard.writeText(input.value).then(() => {
                showToast('link copied', 'success');
            }).catch(() => {
                // Fallback for older browsers
                document.execCommand('copy');
                showToast('link copied', 'success');
            });
        } catch (e) {
            document.execCommand('copy');
            showToast('link copied', 'success');
        }
    }

    /**
     * Clear all selections
     */
    function handleClear() {
        if (Object.keys(state).length === 0) return;

        if (!confirm('clear all selections?')) return;

        state = {};
        isDirty = true;

        document.querySelectorAll('.traffic-btn.active').forEach(btn => {
            btn.classList.remove('active');
        });

        updateCounts();
        showToast('cleared', 'success');
    }

    /**
     * Encode state to URL hash
     * Converts { slug: 'green' } to { slug: 2 } then base64
     */
    function encodeToHash(data) {
        const compact = {};
        Object.entries(data).forEach(([slug, level]) => {
            if (LEVEL_MAP.hasOwnProperty(level)) {
                compact[slug] = LEVEL_MAP[level];
            }
        });
        const json = JSON.stringify(compact);
        return btoa(json);
    }

    /**
     * Decode URL hash to state
     */
    function decodeFromHash(hash) {
        try {
            const json = atob(hash);
            const compact = JSON.parse(json);
            const expanded = {};

            Object.entries(compact).forEach(([slug, num]) => {
                if (LEVEL_REVERSE.hasOwnProperty(num)) {
                    expanded[slug] = LEVEL_REVERSE[num];
                }
            });

            return expanded;
        } catch (e) {
            console.warn('Hash decode error:', e);
            return null;
        }
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        let toast = document.querySelector('.limits-toast');

        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'limits-toast';
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        toast.className = `limits-toast ${type}`;

        // Trigger reflow for animation
        void toast.offsetWidth;
        toast.classList.add('visible');

        setTimeout(() => {
            toast.classList.remove('visible');
        }, 3000);
    }

    /**
     * Warn on page leave if unsaved changes
     */
    window.addEventListener('beforeunload', (e) => {
        if (isDirty && typeof unmaskLimits !== 'undefined' && unmaskLimits.isLoggedIn) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
