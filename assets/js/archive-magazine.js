/**
 * UNMASK Magazine Archive JavaScript
 * Template: template-archive-magazine.php
 *
 * Handles:
 * - Type/Tag filtering (AJAX - no page refresh)
 * - Shuffle modal
 * - User trail (localStorage)
 *
 * ============================================================
 */

(function() {
    'use strict';

    // Debug logging
    console.log('[Archive] Script loaded');
    console.log('[Archive] unmaskArchive available:', typeof unmaskArchive !== 'undefined');
    if (typeof unmaskArchive !== 'undefined') {
        console.log('[Archive] AJAX URL:', unmaskArchive.ajaxUrl);
        console.log('[Archive] Nonce:', unmaskArchive.nonce ? 'present' : 'missing');
    }

    // Check for required global
    if (typeof unmaskArchive === 'undefined') {
        console.error('[Archive] CRITICAL: unmaskArchive not defined! AJAX will not work.');
        // Create fallback to prevent script errors
        window.unmaskArchive = {
            ajaxUrl: '/wp-admin/admin-ajax.php',
            nonce: ''
        };
    }

    // =============================================================================
    // STATE
    // =============================================================================

    let currentType = 'all';
    let currentTags = [];
    let currentPage = 1;
    let isLoading = false;

    // =============================================================================
    // DOM ELEMENTS
    // =============================================================================

    const typeFilters = document.querySelectorAll('[data-filter="type"] .archive-filter-btn');
    const tagFilters = document.querySelectorAll('[data-filter="tags"] .archive-tag-btn');
    const archiveGrid = document.getElementById('archive-grid');
    const filterBar = document.querySelector('.archive-filter-bar');

    // Debug DOM elements
    console.log('[Archive] Type filters found:', typeFilters.length);
    console.log('[Archive] Tag filters found:', tagFilters.length);
    console.log('[Archive] Archive grid found:', !!archiveGrid);
    console.log('[Archive] Filter bar found:', !!filterBar);

    // =============================================================================
    // FILTER FUNCTIONALITY (AJAX)
    // =============================================================================

    // Initialize from URL params
    function initFiltersFromURL() {
        const params = new URLSearchParams(window.location.search);

        if (params.has('record_type')) {
            currentType = params.get('record_type');
        }

        if (params.has('record_tags')) {
            currentTags = params.get('record_tags').split(',').filter(t => t);
        }

        if (params.has('paged')) {
            currentPage = parseInt(params.get('paged')) || 1;
        }

        // Update button states
        updateFilterButtonStates();
    }

    // Update button active states
    function updateFilterButtonStates() {
        // Type buttons
        typeFilters.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.value === currentType);
        });

        // Tag buttons
        tagFilters.forEach(btn => {
            btn.classList.toggle('active', currentTags.includes(btn.dataset.value));
        });

        // Show/hide reset button
        updateResetButton();
    }

    // Update URL without reload
    function updateURL() {
        const params = new URLSearchParams();

        if (currentType !== 'all') {
            params.set('record_type', currentType);
        }

        if (currentTags.length > 0) {
            params.set('record_tags', currentTags.join(','));
        }

        if (currentPage > 1) {
            params.set('paged', currentPage);
        }

        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({ type: currentType, tags: currentTags, page: currentPage }, '', newURL);
    }

    // Fetch filtered records via AJAX
    async function fetchFilteredRecords() {
        console.log('[Archive] fetchFilteredRecords called');
        if (isLoading) {
            console.log('[Archive] Already loading, skipping');
            return;
        }
        isLoading = true;

        // Add loading state
        if (archiveGrid) {
            archiveGrid.classList.add('loading');
            console.log('[Archive] Added loading class to grid');
        }

        try {
            const params = new URLSearchParams({
                action: 'filter_archive_records',
                nonce: unmaskArchive.nonce,
                record_type: currentType,
                record_tags: currentTags.join(','),
                paged: currentPage
            });

            const url = unmaskArchive.ajaxUrl + '?' + params.toString();
            console.log('[Archive] Fetching:', url);

            const response = await fetch(url);
            console.log('[Archive] Response status:', response.status);
            console.log('[Archive] Response headers:', response.headers.get('content-type'));

            // Get raw text first to debug
            const rawText = await response.text();
            console.log('[Archive] Raw response (first 500 chars):', rawText.substring(0, 500));

            // Try to parse as JSON
            let data;
            try {
                data = JSON.parse(rawText);
            } catch (parseError) {
                console.error('[Archive] JSON parse error:', parseError);
                console.error('[Archive] Full raw response:', rawText);
                throw parseError;
            }
            console.log('[Archive] Response data:', data);

            if (data.success && archiveGrid) {
                console.log('[Archive] Updating grid with', data.data.found, 'records');
                // Update grid content
                archiveGrid.innerHTML = data.data.html;

                // Update pagination
                const existingPagination = document.querySelector('.archive-pagination');
                if (existingPagination) {
                    existingPagination.remove();
                }

                if (data.data.pagination) {
                    archiveGrid.insertAdjacentHTML('afterend', data.data.pagination);
                    // Re-attach pagination handlers
                    initPaginationHandlers();
                }

                // Re-init trail tracking for new cards
                initTrailTracking();

                // Scroll to top of grid smoothly
                archiveGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (error) {
            console.error('Failed to fetch records:', error);
        } finally {
            isLoading = false;
            if (archiveGrid) {
                archiveGrid.classList.remove('loading');
            }
        }
    }

    // Apply filters
    function applyFilters() {
        console.log('[Archive] applyFilters called');
        console.log('[Archive] Current state - type:', currentType, 'tags:', currentTags);
        currentPage = 1; // Reset to first page
        updateURL();
        updateFilterButtonStates();
        fetchFilteredRecords();
    }

    // Type filter click
    console.log('[Archive] Attaching type filter listeners...');
    typeFilters.forEach((btn, index) => {
        console.log('[Archive] Attaching listener to type button:', btn.dataset.value);
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('[Archive] Type filter clicked:', this.dataset.value);
            currentType = this.dataset.value;
            applyFilters();
        });
    });
    console.log('[Archive] Type filter listeners attached:', typeFilters.length);

    // Tag filter click (multi-select)
    console.log('[Archive] Attaching tag filter listeners...');
    tagFilters.forEach((btn, index) => {
        console.log('[Archive] Attaching listener to tag button:', btn.dataset.value);
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const tag = this.dataset.value;
            console.log('[Archive] Tag filter clicked:', tag);

            if (currentTags.includes(tag)) {
                currentTags = currentTags.filter(t => t !== tag);
            } else {
                currentTags.push(tag);
            }

            applyFilters();
        });
    });
    console.log('[Archive] Tag filter listeners attached:', tagFilters.length);

    // Update/create reset button
    function updateResetButton() {
        const hasActiveFilters = currentType !== 'all' || currentTags.length > 0;
        let resetBtn = document.getElementById('reset-filters');

        if (hasActiveFilters) {
            if (!resetBtn && filterBar) {
                // Create reset button
                const divider = document.createElement('div');
                divider.className = 'archive-filter-divider';
                divider.id = 'reset-divider';

                resetBtn = document.createElement('button');
                resetBtn.className = 'archive-reset-btn';
                resetBtn.id = 'reset-filters';
                resetBtn.textContent = '[reset]';
                resetBtn.addEventListener('click', resetFilters);

                filterBar.appendChild(divider);
                filterBar.appendChild(resetBtn);
            }
        } else {
            // Remove reset button if exists
            if (resetBtn) {
                resetBtn.remove();
                const divider = document.getElementById('reset-divider');
                if (divider) divider.remove();
            }
        }
    }

    // Reset filters
    function resetFilters() {
        currentType = 'all';
        currentTags = [];
        currentPage = 1;
        applyFilters();
    }

    // Handle browser back/forward
    window.addEventListener('popstate', function(e) {
        if (e.state) {
            currentType = e.state.type || 'all';
            currentTags = e.state.tags || [];
            currentPage = e.state.page || 1;
        } else {
            // Parse from URL
            initFiltersFromURL();
        }
        updateFilterButtonStates();
        fetchFilteredRecords();
    });

    // Pagination handlers
    function initPaginationHandlers() {
        const paginationLinks = document.querySelectorAll('.archive-pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                currentPage = parseInt(url.searchParams.get('paged')) || 1;
                updateURL();
                fetchFilteredRecords();
            });
        });
    }

    // =============================================================================
    // SHUFFLE MODAL
    // =============================================================================

    const shuffleBtn = document.getElementById('shuffle-btn');
    const shuffleModal = document.getElementById('shuffle-modal');
    const shuffleClose = document.getElementById('shuffle-close');
    const shuffleAgain = document.getElementById('shuffle-again');

    // Shuffle state
    let allRecords = [];
    let shuffleLoaded = false;

    // Fetch all records for shuffle
    async function fetchShuffleRecords() {
        console.log('[Archive] fetchShuffleRecords called, already loaded:', shuffleLoaded);
        if (shuffleLoaded) return;

        try {
            const url = unmaskArchive.ajaxUrl + '?action=get_shuffle_records&nonce=' + unmaskArchive.nonce;
            console.log('[Archive] Fetching shuffle records from:', url);
            const response = await fetch(url);
            console.log('[Archive] Shuffle response status:', response.status);
            console.log('[Archive] Shuffle response headers:', response.headers.get('content-type'));

            // Get raw text first to debug
            const rawText = await response.text();
            console.log('[Archive] Shuffle raw response (first 500 chars):', rawText.substring(0, 500));

            // Try to parse as JSON
            let data;
            try {
                data = JSON.parse(rawText);
            } catch (parseError) {
                console.error('[Archive] Shuffle JSON parse error:', parseError);
                console.error('[Archive] Shuffle full raw response:', rawText);
                throw parseError;
            }
            console.log('[Archive] Shuffle response data:', data);

            if (data.success) {
                allRecords = data.data;
                shuffleLoaded = true;
                console.log('[Archive] Loaded', allRecords.length, 'records for shuffle');
            } else {
                console.error('[Archive] Shuffle fetch failed:', data);
            }
        } catch (error) {
            console.error('[Archive] Failed to fetch shuffle records:', error);
        }
    }

    // Get random record
    function getRandomRecord() {
        if (allRecords.length === 0) return null;

        const randomIndex = Math.floor(Math.random() * allRecords.length);
        return allRecords[randomIndex];
    }

    // Display record in modal
    function displayShuffleRecord(record) {
        if (!record) return;

        const fileIdEl = document.getElementById('shuffle-file-id');
        const imageEl = document.getElementById('shuffle-image');
        const titleEl = document.getElementById('shuffle-title');
        const excerptEl = document.getElementById('shuffle-excerpt');
        const openEl = document.getElementById('shuffle-open');

        if (fileIdEl) fileIdEl.textContent = record.file_id;
        if (imageEl) {
            imageEl.src = record.thumbnail;
            imageEl.alt = record.title;
        }
        if (titleEl) titleEl.textContent = record.title;
        if (excerptEl) excerptEl.textContent = record.excerpt || '';
        if (openEl) openEl.href = record.url;
    }

    // Open modal
    async function openShuffleModal() {
        console.log('[Archive] openShuffleModal called');
        // Fetch records if not loaded
        if (!shuffleLoaded) {
            console.log('[Archive] Records not loaded, fetching...');
            await fetchShuffleRecords();
        }

        const record = getRandomRecord();
        console.log('[Archive] Random record:', record);

        if (record && shuffleModal) {
            displayShuffleRecord(record);
            shuffleModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            console.log('[Archive] Modal opened');
        } else {
            console.error('[Archive] Cannot open modal - record:', !!record, 'modal:', !!shuffleModal);
        }
    }

    // Close modal
    function closeShuffleModal() {
        if (shuffleModal) {
            shuffleModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Event listeners
    console.log('[Archive] Shuffle button found:', !!shuffleBtn);
    console.log('[Archive] Shuffle modal found:', !!shuffleModal);

    if (shuffleBtn) {
        shuffleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('[Archive] Shuffle button clicked');
            openShuffleModal();
        });
        console.log('[Archive] Shuffle button listener attached');
    }

    if (shuffleClose) {
        shuffleClose.addEventListener('click', closeShuffleModal);
    }

    if (shuffleAgain) {
        shuffleAgain.addEventListener('click', function() {
            const record = getRandomRecord();
            displayShuffleRecord(record);
        });
    }

    // Close on backdrop click
    if (shuffleModal) {
        shuffleModal.addEventListener('click', function(e) {
            if (e.target === shuffleModal) {
                closeShuffleModal();
            }
        });
    }

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && shuffleModal && shuffleModal.classList.contains('active')) {
            closeShuffleModal();
        }
    });

    // =============================================================================
    // USER TRAIL (localStorage)
    // =============================================================================

    const TRAIL_KEY = 'unmask_archive_trail';
    const MAX_TRAIL_ITEMS = 5;

    // Get trail from storage
    function getTrail() {
        try {
            const trail = localStorage.getItem(TRAIL_KEY);
            return trail ? JSON.parse(trail) : [];
        } catch (e) {
            return [];
        }
    }

    // Save trail to storage
    function saveTrail(trail) {
        try {
            localStorage.setItem(TRAIL_KEY, JSON.stringify(trail));
        } catch (e) {
            console.error('Failed to save trail:', e);
        }
    }

    // Add record to trail
    function addToTrail(record) {
        let trail = getTrail();

        // Remove if already exists
        trail = trail.filter(item => item.id !== record.id);

        // Add to beginning
        trail.unshift({
            id: record.id,
            name: record.name,
            url: record.url,
            thumbnail: record.thumbnail,
            timestamp: Date.now()
        });

        // Limit to max items
        trail = trail.slice(0, MAX_TRAIL_ITEMS);

        saveTrail(trail);
        renderTrail();
    }

    // Format relative time
    function formatRelativeTime(timestamp) {
        const diff = Date.now() - timestamp;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'just now';
        if (minutes < 60) return minutes + ' min ago';
        if (hours < 24) return hours + ' hr ago';
        return days + ' day' + (days > 1 ? 's' : '') + ' ago';
    }

    // Render trail in sidebar
    function renderTrail() {
        const trailList = document.getElementById('user-trail');
        if (!trailList) return;

        const trail = getTrail();

        if (trail.length === 0) {
            trailList.innerHTML = '<li class="archive-trail-empty">No records viewed yet.</li>';
            return;
        }

        trailList.innerHTML = trail.map(item => `
            <li class="archive-trail-item">
                <a href="${item.url}">
                    <img src="${item.thumbnail}" alt="" class="archive-trail-thumb">
                    <div class="archive-trail-info">
                        <div class="archive-trail-name">${item.name}</div>
                        <div class="archive-trail-time">${formatRelativeTime(item.timestamp)}</div>
                    </div>
                </a>
            </li>
        `).join('');
    }

    // Track clicks on record cards
    function initTrailTracking() {
        const recordCards = document.querySelectorAll('.archive-record-card');

        recordCards.forEach(card => {
            const link = card.querySelector('.archive-card-link');
            if (!link) return;

            // Remove existing listener to prevent duplicates
            link.removeEventListener('click', handleCardClick);
            link.addEventListener('click', handleCardClick);
        });
    }

    function handleCardClick(e) {
        const card = e.currentTarget.closest('.archive-record-card');
        if (!card) return;

        const name = card.querySelector('.archive-card-title')?.textContent || 'Unknown';
        const thumbnail = card.querySelector('.archive-card-image')?.src || '';
        const url = e.currentTarget.href;
        const id = url.split('/').filter(Boolean).pop();

        addToTrail({
            id: id,
            name: name,
            url: url,
            thumbnail: thumbnail
        });
    }

    // =============================================================================
    // LOADING STATE CSS
    // =============================================================================

    // Add loading styles dynamically
    const loadingStyles = document.createElement('style');
    loadingStyles.textContent = `
        .archive-record-grid.loading {
            opacity: 0.5;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
    `;
    document.head.appendChild(loadingStyles);

    // =============================================================================
    // INITIALIZATION
    // =============================================================================

    function init() {
        initFiltersFromURL();
        updateFilterButtonStates();
        renderTrail();
        initTrailTracking();
        initPaginationHandlers();

        // Preload shuffle records in background
        setTimeout(fetchShuffleRecords, 1000);
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
