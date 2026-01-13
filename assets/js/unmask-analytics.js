/**
 * UNMASK Analytics - Behavioral Tracking
 *
 * Pushes events to dataLayer for GTM → GA4
 * GA4 Measurement ID: G-27MFSBL8G6
 * GTM Container: GTM-5NJKFDSX
 *
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Ensure dataLayer exists
    window.dataLayer = window.dataLayer || [];

    // =========================================================================
    // UTILITIES
    // =========================================================================

    const Utils = {
        // Throttle function to limit event firing
        throttle: function(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        // Debounce function for delayed execution
        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        // Get clean selector for element
        getSelector: function(el) {
            if (!el) return '';
            if (el.id) return '#' + el.id;
            if (el.className && typeof el.className === 'string') {
                return el.tagName.toLowerCase() + '.' + el.className.split(' ').filter(c => c).join('.');
            }
            return el.tagName ? el.tagName.toLowerCase() : '';
        },

        // Get current page path
        getPagePath: function() {
            return window.location.pathname;
        },

        // Get domain from URL
        getDomain: function(url) {
            try {
                return new URL(url).hostname;
            } catch (e) {
                return '';
            }
        },

        // Check if URL is external
        isExternal: function(url) {
            if (!url) return false;
            try {
                const linkHost = new URL(url, window.location.origin).hostname;
                return linkHost !== window.location.hostname;
            } catch (e) {
                return false;
            }
        },

        // Get week number in YYYY-WW format
        getWeekNumber: function(date) {
            const d = new Date(date);
            d.setHours(0, 0, 0, 0);
            d.setDate(d.getDate() + 4 - (d.getDay() || 7));
            const yearStart = new Date(d.getFullYear(), 0, 1);
            const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
            return d.getFullYear() + '-W' + String(weekNo).padStart(2, '0');
        },

        // Storage helpers
        storage: {
            get: function(key) {
                try {
                    return JSON.parse(localStorage.getItem('unmask_' + key));
                } catch (e) {
                    return null;
                }
            },
            set: function(key, value) {
                try {
                    localStorage.setItem('unmask_' + key, JSON.stringify(value));
                } catch (e) {}
            }
        }
    };

    // =========================================================================
    // PHASE 2: CLICK TRACKING
    // =========================================================================

    const ClickTracking = {
        // CTA button selectors
        ctaSelectors: [
            '[href*="membership"]',
            '[href*="register"]',
            '[href*="the-factory"]',
            '[href*="book"]',
            '.btn-primary',
            '.cta-button',
            '[class*="become-drone"]',
            '[class*="book-factory"]',
            '[class*="respond"]',
            '[class*="complete-dossier"]',
            '.newsletter-submit',
            '[type="submit"]'
        ],

        // Navigation selectors
        navSelectors: [
            '.site-header a',
            '.main-navigation a',
            '.buddypanel a',
            '.footer-widget a',
            '.site-footer a'
        ],

        init: function() {
            document.addEventListener('click', this.handleClick.bind(this), true);
        },

        handleClick: function(e) {
            const target = e.target.closest('a, button, [role="button"]');
            if (!target) return;

            const href = target.href || target.getAttribute('data-href') || '';
            const text = (target.textContent || target.innerText || '').trim().substring(0, 50);

            // Check for CTA clicks
            if (this.isCTA(target)) {
                this.trackCTA(target, text, href);
            }
            // Check for navigation clicks
            else if (this.isNavigation(target)) {
                this.trackNavigation(target, text, href);
            }
            // Check for outbound clicks
            else if (Utils.isExternal(href)) {
                this.trackOutbound(target, text, href);
            }

            // Track last click for exit intent
            State.lastClick = {
                text: text,
                selector: Utils.getSelector(target),
                timestamp: Date.now()
            };
        },

        isCTA: function(el) {
            return this.ctaSelectors.some(selector => el.matches(selector));
        },

        isNavigation: function(el) {
            return this.navSelectors.some(selector => el.closest(selector));
        },

        trackCTA: function(el, text, href) {
            const location = this.getButtonLocation(el);
            dataLayer.push({
                'event': 'cta_click',
                'button_name': text,
                'button_location': location,
                'button_destination': href
            });
        },

        trackNavigation: function(el, text, href) {
            const location = this.getNavLocation(el);
            dataLayer.push({
                'event': 'navigation_click',
                'link_text': text,
                'link_url': href,
                'link_location': location
            });
        },

        trackOutbound: function(el, text, href) {
            dataLayer.push({
                'event': 'outbound_click',
                'link_url': href,
                'link_text': text,
                'outbound_destination': Utils.getDomain(href)
            });
        },

        getButtonLocation: function(el) {
            if (el.closest('.hero, .home-hero, [class*="hero"]')) return 'hero';
            if (el.closest('.site-header, header')) return 'header';
            if (el.closest('.site-footer, footer')) return 'footer';
            if (el.closest('.sidebar, aside')) return 'sidebar';
            if (el.closest('.modal, .popup')) return 'modal';
            if (el.closest('form')) return 'form';
            return 'content';
        },

        getNavLocation: function(el) {
            if (el.closest('.site-header, header')) return 'header';
            if (el.closest('.site-footer, footer')) return 'footer';
            if (el.closest('.sidebar, aside, .buddypanel')) return 'sidebar';
            return 'inline';
        }
    };

    // =========================================================================
    // PHASE 3: DWELL TIME & ENGAGEMENT
    // =========================================================================

    const EngagementTracking = {
        scrollMilestones: [25, 50, 75, 90, 100],
        scrollFired: {},
        timeMilestones: [30, 60, 120, 300], // seconds
        timeFired: {},
        startTime: Date.now(),

        init: function() {
            this.initScrollTracking();
            this.initTimeTracking();
            this.initContentEngagement();
        },

        // Scroll depth tracking
        initScrollTracking: function() {
            const handleScroll = Utils.throttle(() => {
                const scrollPercent = this.getScrollPercent();

                this.scrollMilestones.forEach(milestone => {
                    if (scrollPercent >= milestone && !this.scrollFired[milestone]) {
                        this.scrollFired[milestone] = true;
                        dataLayer.push({
                            'event': 'scroll_depth',
                            'percent_scrolled': milestone,
                            'page_path': Utils.getPagePath()
                        });

                        // Update max scroll for exit tracking
                        State.maxScroll = Math.max(State.maxScroll || 0, milestone);
                    }
                });
            }, 100);

            window.addEventListener('scroll', handleScroll, { passive: true });
        },

        getScrollPercent: function() {
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (docHeight <= 0) return 100;
            return Math.round((window.scrollY / docHeight) * 100);
        },

        // Time on page tracking
        initTimeTracking: function() {
            this.timeMilestones.forEach(seconds => {
                setTimeout(() => {
                    if (!this.timeFired[seconds] && !document.hidden) {
                        this.timeFired[seconds] = true;
                        dataLayer.push({
                            'event': 'engaged_time',
                            'time_threshold': seconds,
                            'page_path': Utils.getPagePath()
                        });
                    }
                }, seconds * 1000);
            });

            // Handle visibility change (pause when tab hidden)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    State.hiddenTime = Date.now();
                } else if (State.hiddenTime) {
                    const hiddenDuration = Date.now() - State.hiddenTime;
                    this.startTime += hiddenDuration;
                    State.hiddenTime = null;
                }
            });
        },

        getTimeOnPage: function() {
            return Math.round((Date.now() - this.startTime) / 1000);
        },

        // Content engagement for articles
        initContentEngagement: function() {
            // Only on single posts/articles
            if (!document.body.classList.contains('single-post') &&
                !document.querySelector('article.post')) return;

            const article = document.querySelector('article, .post-content, .entry-content');
            if (!article) return;

            let hasTrackedPartial = false;
            let hasTrackedComplete = false;

            const checkEngagement = Utils.throttle(() => {
                const scrollPercent = this.getScrollPercent();
                const timeOnPage = this.getTimeOnPage();
                const contentId = this.getContentId();

                // Partial read: 50% scroll + 30s
                if (!hasTrackedPartial && scrollPercent >= 50 && timeOnPage >= 30) {
                    hasTrackedPartial = true;
                    dataLayer.push({
                        'event': 'content_engagement',
                        'engagement_type': 'article_read',
                        'content_id': contentId,
                        'engagement_depth': 'partial'
                    });
                }

                // Complete read: 90% scroll + 60s
                if (!hasTrackedComplete && scrollPercent >= 90 && timeOnPage >= 60) {
                    hasTrackedComplete = true;
                    dataLayer.push({
                        'event': 'content_engagement',
                        'engagement_type': 'article_read',
                        'content_id': contentId,
                        'engagement_depth': 'complete'
                    });
                }
            }, 1000);

            window.addEventListener('scroll', checkEngagement, { passive: true });
        },

        getContentId: function() {
            const body = document.body;
            const classes = body.className.match(/postid-(\d+)|page-id-(\d+)/);
            if (classes) return classes[1] || classes[2];
            return Utils.getPagePath();
        }
    };

    // =========================================================================
    // PHASE 4: BOUNCE & EXIT TRACKING
    // =========================================================================

    const ExitTracking = {
        exitIntentFired: false,

        init: function() {
            this.initExitIntent();
            this.initRageClickDetection();
            this.initFormAbandonment();
        },

        // Exit intent detection
        initExitIntent: function() {
            // Desktop: mouse leaves viewport
            document.addEventListener('mouseout', (e) => {
                if (this.exitIntentFired) return;
                if (e.clientY <= 0 && e.relatedTarget === null) {
                    this.trackExitIntent();
                }
            });

            // Mobile/All: page visibility change or beforeunload
            document.addEventListener('visibilitychange', () => {
                if (document.hidden && !this.exitIntentFired) {
                    this.trackExitIntent();
                }
            });

            window.addEventListener('beforeunload', () => {
                if (!this.exitIntentFired) {
                    this.trackExitIntent();
                }
            });
        },

        trackExitIntent: function() {
            this.exitIntentFired = true;
            dataLayer.push({
                'event': 'exit_intent',
                'page_path': Utils.getPagePath(),
                'time_on_page': EngagementTracking.getTimeOnPage(),
                'scroll_depth_at_exit': State.maxScroll || 0,
                'last_click': State.lastClick ? State.lastClick.text : ''
            });
        },

        // Rage click detection
        initRageClickDetection: function() {
            let clickHistory = [];
            const RAGE_THRESHOLD = 3;
            const RAGE_WINDOW = 1000; // 1 second

            document.addEventListener('click', (e) => {
                const now = Date.now();
                const target = e.target;

                // Clean old clicks
                clickHistory = clickHistory.filter(c => now - c.time < RAGE_WINDOW);

                // Add current click
                clickHistory.push({
                    time: now,
                    x: e.clientX,
                    y: e.clientY,
                    target: target
                });

                // Check for rage clicks (3+ clicks in same area within 1s)
                if (clickHistory.length >= RAGE_THRESHOLD) {
                    const recentClicks = clickHistory.slice(-RAGE_THRESHOLD);
                    const sameArea = recentClicks.every(c => {
                        return Math.abs(c.x - recentClicks[0].x) < 50 &&
                               Math.abs(c.y - recentClicks[0].y) < 50;
                    });

                    if (sameArea) {
                        // Check if clicking on non-interactive element
                        const isInteractive = target.matches('a, button, input, select, textarea, [role="button"], [onclick]');

                        dataLayer.push({
                            'event': 'frustration_signal',
                            'signal_type': isInteractive ? 'rage_click' : 'dead_click',
                            'element_clicked': Utils.getSelector(target),
                            'page_path': Utils.getPagePath()
                        });

                        clickHistory = []; // Reset after detection
                    }
                }
            });
        },

        // Form abandonment tracking
        initFormAbandonment: function() {
            const forms = document.querySelectorAll('form');

            forms.forEach(form => {
                const formName = this.getFormName(form);
                let formStarted = false;
                let fieldsCompleted = 0;
                let lastField = '';
                let formStartTime = null;

                // Track form start
                form.addEventListener('focusin', (e) => {
                    if (!formStarted && e.target.matches('input, select, textarea')) {
                        formStarted = true;
                        formStartTime = Date.now();
                    }
                });

                // Track field completion
                form.addEventListener('change', (e) => {
                    if (e.target.matches('input, select, textarea')) {
                        fieldsCompleted++;
                        lastField = e.target.name || e.target.id || e.target.type;
                    }
                });

                // Track form submission
                form.addEventListener('submit', () => {
                    formStarted = false; // Reset so abandonment doesn't fire
                });

                // Track abandonment on page leave
                window.addEventListener('beforeunload', () => {
                    if (formStarted && fieldsCompleted > 0) {
                        dataLayer.push({
                            'event': 'form_abandonment',
                            'form_name': formName,
                            'fields_completed': fieldsCompleted,
                            'last_field': lastField,
                            'time_in_form': formStartTime ? Math.round((Date.now() - formStartTime) / 1000) : 0
                        });
                    }
                });
            });
        },

        getFormName: function(form) {
            if (form.id) return form.id;
            if (form.name) return form.name;
            if (form.action) {
                if (form.action.includes('register')) return 'registration';
                if (form.action.includes('factory') || form.action.includes('book')) return 'factory_booking';
                if (form.action.includes('newsletter') || form.action.includes('subscribe')) return 'newsletter';
                if (form.action.includes('iso')) return 'iso_post';
            }
            if (form.closest('.newsletter')) return 'newsletter';
            if (form.closest('.registration, .register')) return 'registration';
            return 'unknown_form';
        }
    };

    // =========================================================================
    // PHASE 5: RETURN VISITOR TRACKING
    // =========================================================================

    const ReturnVisitorTracking = {
        init: function() {
            this.initVisitorData();
            this.setUserProperties();
            this.trackSessionStart();
            this.trackCohort();
        },

        initVisitorData: function() {
            const now = Date.now();
            let visitorData = Utils.storage.get('visitor_data') || {
                firstVisit: now,
                lastVisit: now,
                sessionCount: 0,
                firstVisitWeek: Utils.getWeekNumber(new Date())
            };

            // Check if this is a new session (30 min gap)
            const sessionGap = 30 * 60 * 1000; // 30 minutes
            const isNewSession = (now - visitorData.lastVisit) > sessionGap;

            if (isNewSession) {
                visitorData.sessionCount++;
                visitorData.lastVisit = now;
            }

            Utils.storage.set('visitor_data', visitorData);
            State.visitorData = visitorData;
        },

        setUserProperties: function() {
            const data = State.visitorData;
            const daysSinceFirst = Math.floor((Date.now() - data.firstVisit) / (1000 * 60 * 60 * 24));

            // Determine member status from body classes or cookies
            let memberStatus = 'anonymous';
            if (document.body.classList.contains('logged-in')) {
                memberStatus = document.body.classList.contains('mepr-active-member') ? 'drone' : 'free';
            }

            dataLayer.push({
                'event': 'user_properties_set',
                'user_properties': {
                    'visitor_type': data.sessionCount > 1 ? 'returning' : 'new',
                    'session_count': data.sessionCount,
                    'days_since_first_visit': daysSinceFirst,
                    'member_status': memberStatus
                }
            });
        },

        trackSessionStart: function() {
            const data = State.visitorData;
            const daysSinceLast = Math.floor((Date.now() - data.lastVisit) / (1000 * 60 * 60 * 24));

            // Only track on actual new sessions
            if (data.sessionCount > 0) {
                dataLayer.push({
                    'event': 'session_start_custom',
                    'session_number': data.sessionCount,
                    'days_since_last_visit': daysSinceLast,
                    'returning_to_page': Utils.getPagePath()
                });
            }
        },

        trackCohort: function() {
            const data = State.visitorData;
            const currentWeek = Utils.getWeekNumber(new Date());
            const firstWeek = data.firstVisitWeek;

            // Calculate weeks retained
            const getWeekDiff = (w1, w2) => {
                const [y1, wk1] = w1.split('-W').map(Number);
                const [y2, wk2] = w2.split('-W').map(Number);
                return ((y2 - y1) * 52) + (wk2 - wk1);
            };

            const weeksRetained = getWeekDiff(firstWeek, currentWeek);

            // Only track for returning visitors
            if (weeksRetained > 0) {
                dataLayer.push({
                    'event': 'cohort_activity',
                    'first_visit_week': firstWeek,
                    'current_week': currentWeek,
                    'weeks_retained': weeksRetained
                });
            }
        }
    };

    // =========================================================================
    // SHARED STATE
    // =========================================================================

    const State = {
        lastClick: null,
        maxScroll: 0,
        visitorData: null,
        hiddenTime: null
    };

    // =========================================================================
    // INITIALIZATION
    // =========================================================================

    function init() {
        // Wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    }

    function initAll() {
        ClickTracking.init();
        EngagementTracking.init();
        ExitTracking.init();
        ReturnVisitorTracking.init();

        // Debug mode - uncomment to test
        // console.log('UNMASK Analytics initialized');
        // window.unmaskAnalytics = { ClickTracking, EngagementTracking, ExitTracking, ReturnVisitorTracking, State };
    }

    // Start
    init();

})();
