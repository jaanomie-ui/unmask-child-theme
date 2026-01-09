# Homepage Carousel Horizontal Swipe Fix

**Issue:** Horizontal swipe not working on mobile homepage carousels (rails), while Factory page carousel worked fine.

**Root Cause:** Multiple factors in BuddyBoss theme were blocking native CSS horizontal scroll:

1. **BuddyBoss CSS:** `.slick-slider { touch-action: pan-y }` restricts touch to vertical only
2. **BuddyBoss JS:** Various libraries (Draggabilly, Slick) attach touch event listeners
3. **Parent overflow:** Wrapper elements can clip or block scroll containers
4. **Event listeners:** Scripts attaching `touchstart`/`touchmove` handlers that may call `preventDefault()`

## The Fix

Located in: `template-parts/homepage/mobile-layout.php` (inline `<style>` and `<script>` blocks)

### CSS Requirements

```css
/* 1. Global touch-action reset for mobile layout */
.page-template-page-templatestemplate-homepage-php .unmask-mobile,
.page-template-page-templatestemplate-homepage-php .unmask-mobile *,
.unmask-homepage-mobile,
.unmask-homepage-mobile * {
    touch-action: auto !important;
    -ms-touch-action: auto !important;
}

/* 2. Track container - the scrollable element */
.homepage-rail__track {
    display: flex;
    gap: 12px;
    overflow-x: scroll !important;
    overflow-y: hidden !important;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch !important;
    scrollbar-width: none;
    touch-action: pan-x pan-y !important;
    pointer-events: auto !important;
    /* GPU acceleration resets touch handling */
    transform: translate3d(0, 0, 0);
    -webkit-transform: translate3d(0, 0, 0);
    will-change: scroll-position;
    /* Stacking context isolation */
    position: relative;
    z-index: 1;
    isolation: isolate;
}

/* 3. Parent containers must allow overflow */
.homepage-rail {
    overflow: visible !important;
    position: relative;
    max-width: 100vw;
    width: 100%;
}

.homepage-grid,
.unmask-mobile,
.unmask-homepage-mobile {
    overflow: visible !important;
    max-width: 100vw;
    width: 100%;
}

/* 4. Cards must not shrink and allow touch */
.homepage-rail__card {
    flex: 0 0 auto !important;
    min-width: 240px;
    touch-action: pan-x pan-y !important;
    pointer-events: auto !important;
}

/* 5. Links inside cards */
.homepage-rail__card a,
.homepage-rail__card-link {
    touch-action: pan-x pan-y !important;
    pointer-events: auto !important;
}
```

### JavaScript Requirements

Clone elements to remove event listeners attached by other scripts:

```javascript
(function() {
    'use strict';

    function fixTracks() {
        var tracks = document.querySelectorAll('.homepage-rail__track');

        tracks.forEach(function(track) {
            if (track.dataset.scrollFixed) return;

            // Clone removes all event listeners
            var parent = track.parentNode;
            var clone = track.cloneNode(true);

            // Force scroll styles inline
            clone.style.cssText = [
                'display: flex',
                'gap: 12px',
                'overflow-x: scroll',
                'overflow-y: hidden',
                '-webkit-overflow-scrolling: touch',
                'touch-action: pan-x pan-y',
                'scroll-snap-type: x mandatory',
                'scrollbar-width: none',
                'pointer-events: auto',
                'transform: translate3d(0,0,0)',
                'position: relative',
                'z-index: 1'
            ].join(' !important; ') + ' !important';

            clone.dataset.scrollFixed = 'true';

            // Passive listeners don't block scrolling
            clone.addEventListener('touchstart', function(e) {}, { passive: true });
            clone.addEventListener('touchmove', function(e) {}, { passive: true });

            parent.replaceChild(clone, track);
        });
    }

    function fixAncestors() {
        var ancestors = document.querySelectorAll('.homepage-grid, .unmask-mobile, .unmask-homepage-mobile, .bb-grid');
        ancestors.forEach(function(el) {
            el.style.overflow = 'visible';
            el.style.touchAction = 'auto';
        });
    }

    // Run multiple times to catch late-loading scripts
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            fixTracks();
            fixAncestors();
        });
    } else {
        fixTracks();
        fixAncestors();
    }

    window.addEventListener('load', function() {
        fixTracks();
        fixAncestors();
        setTimeout(function() { fixTracks(); fixAncestors(); }, 100);
        setTimeout(function() { fixTracks(); fixAncestors(); }, 500);
        setTimeout(function() { fixTracks(); fixAncestors(); }, 1000);
    });
})();
```

## Key Insights

1. **Factory page works because:** Its CSS is in an external stylesheet loaded after BuddyBoss, and it has simpler structure without the mobile wrapper hierarchy.

2. **`touch-action: pan-x pan-y`** allows both horizontal and vertical panning (BuddyBoss's `pan-y` only allows vertical).

3. **`transform: translate3d(0,0,0)`** forces GPU compositing which can reset touch handling behavior.

4. **Element cloning** removes ALL event listeners that other scripts attached.

5. **Passive event listeners** tell the browser we won't call `preventDefault()`, allowing native scroll.

6. **Width constraints** (`max-width: 100vw`) ensure the track actually overflows its container.

## If It Breaks Again

1. Check browser console for `[UNMASK] Fixed track:` logs - confirms JS is running
2. Use DevTools to inspect computed `touch-action` on `.homepage-rail__track`
3. Check if new BuddyBoss scripts are attaching touch handlers
4. Verify parent elements don't have `overflow: hidden`
5. Check for new CSS with `touch-action: pan-y` or similar

## Related Files

- `template-parts/homepage/mobile-layout.php` - Contains the fix
- `inc/enqueue-homepage-grid.php` - Disabled `homepage-rails.js` (was interfering)
- `inc/performance-optimizations.php` - Removes VideoJS on homepage (caused JS errors)
- `assets/css/pages/factory.css` - Reference for working carousel CSS pattern
