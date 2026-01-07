/**
 * UNMASK Gallery JavaScript
 *
 * Handles gallery navigation for single record pages.
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initGalleries();
    });

    function initGalleries() {
        const leads = document.querySelectorAll('.unmask-gallery-lead');

        leads.forEach(function(lead) {
            const galleryId = lead.dataset.galleryId;
            const images = JSON.parse(lead.dataset.images || '[]');
            const total = parseInt(lead.dataset.total, 10) || 0;

            if (total <= 1) return;

            let currentIndex = 0;

            const img = lead.querySelector('.unmask-gallery-lead__img');
            const filename = lead.querySelector('.unmask-gallery-lead__filename');
            const counter = lead.querySelector('.unmask-gallery-current');
            const prevBtn = lead.querySelector('.unmask-gallery-nav--prev');
            const nextBtn = lead.querySelector('.unmask-gallery-nav--next');
            const strip = document.querySelector('.unmask-gallery-strip[data-gallery-id="' + galleryId + '"]');

            function updateGallery(index) {
                if (index < 0) index = total - 1;
                if (index >= total) index = 0;

                currentIndex = index;
                const image = images[currentIndex];

                // Update lead image
                if (img && image) {
                    img.src = image.full;
                    img.alt = image.filename;
                }

                // Update filename
                if (filename && image) {
                    filename.textContent = image.filename;
                }

                // Update counter (01-padded)
                if (counter) {
                    counter.textContent = String(currentIndex + 1).padStart(2, '0');
                }

                // Update strip active state
                if (strip) {
                    strip.querySelectorAll('.unmask-gallery-strip__item').forEach(function(item, i) {
                        item.classList.toggle('unmask-gallery-strip__item--active', i === currentIndex);
                    });

                    // Scroll strip horizontally to center active thumbnail (without moving page)
                    const activeItem = strip.querySelector('.unmask-gallery-strip__item--active');
                    if (activeItem) {
                        const scrollLeft = activeItem.offsetLeft - (strip.offsetWidth / 2) + (activeItem.offsetWidth / 2);
                        strip.scrollTo({ left: scrollLeft, behavior: 'smooth' });
                    }
                }
            }

            // Navigation buttons
            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    updateGallery(currentIndex - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    updateGallery(currentIndex + 1);
                });
            }

            // Strip thumbnail clicks
            if (strip) {
                strip.querySelectorAll('.unmask-gallery-strip__item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index, 10);
                        updateGallery(index);
                    });
                });
            }

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    updateGallery(currentIndex - 1);
                } else if (e.key === 'ArrowRight') {
                    updateGallery(currentIndex + 1);
                }
            });
        });
    }
})();
