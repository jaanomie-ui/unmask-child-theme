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

    // Check for required global
    if (typeof unmaskArchive === 'undefined') {
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
        if (isLoading) return;
        isLoading = true;

        // Add loading state
        if (archiveGrid) {
            archiveGrid.classList.add('loading');
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
            const response = await fetch(url);
            const data = await response.json();

            if (data.success && archiveGrid) {
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
            // Silent fail - grid stays in current state
        } finally {
            isLoading = false;
            if (archiveGrid) {
                archiveGrid.classList.remove('loading');
            }
        }
    }

    // Apply filters
    function applyFilters() {
        currentPage = 1; // Reset to first page
        updateURL();
        updateFilterButtonStates();
        fetchFilteredRecords();
    }

    // Type filter click
    typeFilters.forEach((btn) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentType = this.dataset.value;
            applyFilters();
        });
    });

    // Tag filter click (multi-select)
    tagFilters.forEach((btn) => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const tag = this.dataset.value;

            if (currentTags.includes(tag)) {
                currentTags = currentTags.filter(t => t !== tag);
            } else {
                currentTags.push(tag);
            }

            applyFilters();
        });
    });

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
        var paginationLinks = document.querySelectorAll('.archive-pagination a');
        paginationLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var url = new URL(this.href);
                currentPage = parseInt(url.searchParams.get('paged')) || 1;
                updateURL();
                fetchFilteredRecords();
            });
        });

        // Load More button handler
        var loadMoreBtn = document.getElementById('load-more-btn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                var btn = this;
                var currentPageNum = parseInt(btn.dataset.page) || 1;
                var maxPages = parseInt(btn.dataset.max) || 1;

                if (currentPageNum >= maxPages) return;

                btn.classList.add('loading');
                btn.textContent = '[loading...]';

                currentPage = currentPageNum + 1;
                updateURL();
                fetchFilteredRecords().then(function() {
                    // Update button state after fetch
                    btn.dataset.page = currentPage;
                    btn.classList.remove('loading');
                    btn.textContent = '[load more]';

                    if (currentPage >= maxPages) {
                        btn.style.display = 'none';
                    }

                    // Update count display
                    updateLoadCount();
                });
            });
        }
    }

    // Update the "showing X of Y" count
    function updateLoadCount() {
        var countEl = document.querySelector('.archive-load-count');
        if (!countEl) return;

        var grid = document.getElementById('archive-grid');
        var cards = grid ? grid.querySelectorAll('.card--record').length : 0;

        // The count element structure: "showing <strong>X</strong> of <strong>Y</strong> records"
        var strongs = countEl.querySelectorAll('strong');
        if (strongs.length >= 1) {
            strongs[0].textContent = cards;
        }
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
        if (shuffleLoaded) return;

        try {
            const url = unmaskArchive.ajaxUrl + '?action=get_shuffle_records&nonce=' + unmaskArchive.nonce;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                allRecords = data.data;
                shuffleLoaded = true;
            }
        } catch (error) {
            // Silent fail
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
        // Fetch records if not loaded
        if (!shuffleLoaded) {
            await fetchShuffleRecords();
        }

        const record = getRandomRecord();

        if (record && shuffleModal) {
            displayShuffleRecord(record);
            shuffleModal.classList.add('active');
            document.body.style.overflow = 'hidden';
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
    if (shuffleBtn) {
        shuffleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openShuffleModal();
        });
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
    // INFINITE SCROLL (Mobile Only)
    // =============================================================================

    let infiniteScrollEnabled = false;
    let hasMorePages = true;
    let maxPages = 1;

    function isMobile() {
        return window.innerWidth <= 768;
    }

    function initInfiniteScroll() {
        if (!isMobile()) {
            document.body.classList.remove('has-infinite-scroll');
            return;
        }

        document.body.classList.add('has-infinite-scroll');
        infiniteScrollEnabled = true;

        // Get max pages from pagination if present
        const pagination = document.querySelector('.archive-pagination');
        if (pagination) {
            const pageLinks = pagination.querySelectorAll('a.page-numbers, span.page-numbers');
            let maxFound = 1;
            pageLinks.forEach(link => {
                const pageNum = parseInt(link.textContent);
                if (!isNaN(pageNum) && pageNum > maxFound) {
                    maxFound = pageNum;
                }
            });
            maxPages = maxFound;
        }

        hasMorePages = currentPage < maxPages;

        // Create intersection observer for infinite scroll
        const observerOptions = {
            root: null,
            rootMargin: '200px', // Start loading before reaching bottom
            threshold: 0
        };

        const scrollObserver = new IntersectionObserver(handleInfiniteScroll, observerOptions);

        // Observe the last card in the grid
        updateScrollObserver(scrollObserver);

        // Re-observe after AJAX loads
        window.updateScrollObserver = function() {
            updateScrollObserver(scrollObserver);
        };
    }

    function updateScrollObserver(observer) {
        // Disconnect previous observations
        observer.disconnect();

        if (!infiniteScrollEnabled || !hasMorePages) return;

        // Observe the last card
        const cards = archiveGrid.querySelectorAll('.card, .archive-submit-card');
        if (cards.length > 0) {
            const lastCard = cards[cards.length - 1];
            observer.observe(lastCard);
        }
    }

    async function handleInfiniteScroll(entries) {
        const entry = entries[0];

        if (!entry.isIntersecting) return;
        if (isLoading) return;
        if (!hasMorePages) return;

        // Load next page
        currentPage++;

        // Show loading indicator
        const loader = document.createElement('div');
        loader.className = 'archive-infinite-loader';
        loader.id = 'infinite-loader';
        archiveGrid.appendChild(loader);

        try {
            const params = new URLSearchParams({
                action: 'filter_archive_records',
                nonce: unmaskArchive.nonce,
                record_type: currentType,
                record_tags: currentTags.join(','),
                paged: currentPage
            });

            isLoading = true;
            const response = await fetch(unmaskArchive.ajaxUrl + '?' + params.toString());
            const data = await response.json();

            // Remove loader
            const existingLoader = document.getElementById('infinite-loader');
            if (existingLoader) existingLoader.remove();

            if (data.success && data.data.html.trim()) {
                // Append new cards (don't replace)
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.data.html;

                // Remove the "no records" message if present in new content
                const noRecords = tempDiv.querySelector('.archive-no-records');
                if (noRecords) noRecords.remove();

                // Skip submit card on subsequent pages
                const submitCard = tempDiv.querySelector('.archive-submit-card');
                if (submitCard) submitCard.remove();

                // Append remaining cards
                while (tempDiv.firstChild) {
                    archiveGrid.appendChild(tempDiv.firstChild);
                }

                maxPages = data.data.max_pages || maxPages;
                hasMorePages = currentPage < maxPages;

                // Re-observe last card
                if (window.updateScrollObserver) {
                    window.updateScrollObserver();
                }

                // Re-init trail tracking
                initTrailTracking();
            } else {
                hasMorePages = false;
            }

            // Show end message when done
            if (!hasMorePages) {
                const endMsg = document.createElement('div');
                endMsg.className = 'archive-end-message';
                endMsg.textContent = '— end of archive —';
                archiveGrid.appendChild(endMsg);
            }

        } catch (error) {
            const existingLoader = document.getElementById('infinite-loader');
            if (existingLoader) existingLoader.remove();
        } finally {
            isLoading = false;
        }
    }

    // Handle resize - enable/disable infinite scroll
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (isMobile() && !infiniteScrollEnabled) {
                initInfiniteScroll();
            } else if (!isMobile() && infiniteScrollEnabled) {
                document.body.classList.remove('has-infinite-scroll');
                infiniteScrollEnabled = false;
            }
        }, 100);
    });

    // =============================================================================
    // INITIALIZATION
    // =============================================================================

    function init() {
        initFiltersFromURL();
        updateFilterButtonStates();
        renderTrail();
        initTrailTracking();
        initPaginationHandlers();
        initInfiniteScroll();

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
