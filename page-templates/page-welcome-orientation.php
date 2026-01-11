<?php
/**
 * Template Name: Welcome Orientation
 * Template Post Type: page
 *
 * UNMASK Onboarding Screen 2: Orientation
 * Shows constellation map (desktop) or vertical stack (mobile).
 *
 * @package BuddyBoss_Child
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/register-visitor/'));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Check onboarding progress - if Screen 2+ complete, skip to Screen 3
$onboarding_step = get_user_meta($user_id, 'unmask_onboarding_step', true);
if ($onboarding_step && intval($onboarding_step) > 2) {
    wp_redirect(home_url('/welcome/start/'));
    exit;
}

// Mark Screen 1 as complete if not already
if (!$onboarding_step || intval($onboarding_step) < 2) {
    update_user_meta($user_id, 'unmask_onboarding_step', 2);
}

// Generate visitor designation
$designation = 'V-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);

// Node data
$nodes = array(
    'portal' => array(
        'title' => 'THE PORTAL',
        'sublabel' => 'YOU ARE HERE',
        'desc' => 'Your dossier. ISO Board. The community layer.',
        'icon' => '◉',
        'position' => array('top' => 250, 'left' => 300),
        'is_you' => true,
    ),
    'archive' => array(
        'title' => 'THE ARCHIVE',
        'sublabel' => 'THE RECORD',
        'desc' => 'Documentation of Chicago\'s creative underground. Records. Profiles.',
        'icon' => '▣',
        'position' => array('top' => 120, 'left' => 150),
    ),
    'factory' => array(
        'title' => 'THE FACTORY',
        'sublabel' => 'THE STUDIO',
        'desc' => 'The studio. $40/hour. Professional lighting. Create work. Be documented.',
        'icon' => '▢',
        'position' => array('top' => 120, 'left' => 450),
    ),
    'panthers' => array(
        'title' => 'PINK PANTHERS',
        'sublabel' => 'THE EVENT',
        'desc' => 'Monthly. You will be photographed. You will be part of the record.',
        'icon' => '◈',
        'position' => array('top' => 380, 'left' => 150),
    ),
    'submit' => array(
        'title' => 'SUBMIT',
        'sublabel' => 'BECOME THE RECORD',
        'desc' => 'Send your work. Become part of the archive.',
        'icon' => '△',
        'position' => array('top' => 380, 'left' => 450),
    ),
);

get_header('fullbleed');
?>

<div class="unmask-onboarding unmask-onboarding--screen-2">

    <!-- Ambient Background -->
    <div class="unmask-ambient">
        <div class="unmask-ambient__blob unmask-ambient__blob--red"></div>
        <div class="unmask-ambient__blob unmask-ambient__blob--green"></div>
    </div>

    <!-- Header -->
    <header class="unmask-onboarding__header">
        <span class="unmask-onboarding__step">STEP 2 OF 4 — ORIENTATION</span>
        <span class="unmask-onboarding__designation"><?php echo esc_html($designation); ?></span>
    </header>

    <!-- Main Content -->
    <main class="unmask-onboarding__body">

        <!-- DESKTOP: Constellation Map -->
        <div class="unmask-constellation-container">
            <!-- Welcome Title -->
            <div class="unmask-orientation__welcome">
                <span class="unmask-orientation__welcome-label">WELCOME</span>
                <span class="unmask-orientation__welcome-designation"><?php echo esc_html($designation); ?></span>
                <span class="unmask-orientation__welcome-hint">hover to explore</span>
            </div>

            <div class="unmask-constellation">
                <!-- SVG Connections -->
                <svg viewBox="0 0 600 500">
                    <!-- Portal to Archive -->
                    <line class="unmask-constellation__line unmask-constellation__line--pulse" x1="300" y1="250" x2="150" y2="120" style="animation-delay: 0.2s"/>
                    <!-- Portal to Factory -->
                    <line class="unmask-constellation__line unmask-constellation__line--pulse" x1="300" y1="250" x2="450" y2="120" style="animation-delay: 0.4s"/>
                    <!-- Portal to Pink Panthers -->
                    <line class="unmask-constellation__line unmask-constellation__line--pulse" x1="300" y1="250" x2="150" y2="380" style="animation-delay: 0.6s"/>
                    <!-- Portal to Submit -->
                    <line class="unmask-constellation__line unmask-constellation__line--pulse" x1="300" y1="250" x2="450" y2="380" style="animation-delay: 0.8s"/>
                    <!-- Archive to Submit (content loop) -->
                    <line class="unmask-constellation__line unmask-constellation__line--strong unmask-constellation__line--pulse" x1="150" y1="120" x2="450" y2="380" style="animation-delay: 1s"/>
                    <!-- Factory to Pink Panthers (physical loop) -->
                    <line class="unmask-constellation__line unmask-constellation__line--strong unmask-constellation__line--pulse" x1="450" y1="120" x2="150" y2="380" style="animation-delay: 1.2s"/>
                </svg>

                <!-- Floating Particles -->
                <div class="unmask-particle" style="top: 20%; left: 30%; animation-delay: 0s;"></div>
                <div class="unmask-particle" style="top: 40%; left: 60%; animation-delay: 1s;"></div>
                <div class="unmask-particle" style="top: 60%; left: 25%; animation-delay: 2s;"></div>
                <div class="unmask-particle" style="top: 30%; left: 70%; animation-delay: 3s;"></div>
                <div class="unmask-particle" style="top: 70%; left: 50%; animation-delay: 4s;"></div>
                <div class="unmask-particle" style="top: 15%; left: 45%; animation-delay: 1.5s;"></div>
                <div class="unmask-particle" style="top: 55%; left: 80%; animation-delay: 2.5s;"></div>
                <div class="unmask-particle" style="top: 80%; left: 35%; animation-delay: 3.5s;"></div>

                <!-- Nodes -->
                <?php foreach ($nodes as $key => $node) : ?>
                    <div class="unmask-constellation__node <?php echo isset($node['is_you']) ? 'unmask-constellation__node--you' : ''; ?>"
                         style="top: <?php echo esc_attr($node['position']['top']); ?>px; left: <?php echo esc_attr($node['position']['left']); ?>px;"
                         data-node="<?php echo esc_attr($key); ?>">
                        <div class="unmask-constellation__heat-glow" style="animation-delay: -<?php echo array_search($key, array_keys($nodes)); ?>s;"></div>
                        <?php if (isset($node['is_you'])) : ?>
                            <div class="unmask-constellation__pulse-ring"></div>
                            <div class="unmask-constellation__pulse-ring unmask-constellation__pulse-ring--delay-1"></div>
                            <div class="unmask-constellation__pulse-ring unmask-constellation__pulse-ring--delay-2"></div>
                        <?php endif; ?>
                        <div class="unmask-constellation__node-core">
                            <div class="unmask-constellation__node-circle <?php echo isset($node['is_you']) ? 'unmask-constellation__node-circle--large unmask-constellation__node-circle--you' : ''; ?>">
                                <span class="unmask-constellation__node-icon"><?php echo esc_html($node['icon']); ?></span>
                            </div>
                            <span class="unmask-constellation__node-label"><?php echo esc_html($node['title']); ?></span>
                            <span class="unmask-constellation__node-sublabel"><?php echo esc_html($node['sublabel']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Node Detail Popup -->
                <div class="unmask-constellation__detail" id="nodeDetail">
                    <div class="unmask-constellation__detail-title"></div>
                    <div class="unmask-constellation__detail-desc"></div>
                </div>
            </div>

        </div>

        <!-- MOBILE: Vertical Stack -->
        <div class="unmask-nodes-container">
            <div class="unmask-orientation__intro">
                <p class="unmask-orientation__intro-title">WELCOME <?php echo esc_html($designation); ?></p>
                <p class="unmask-orientation__intro-text">Tap to explore what's available to you.</p>
            </div>

            <?php
            // Reorder for mobile: Portal first, then others
            $mobile_order = array('portal', 'archive', 'factory', 'panthers', 'submit');
            $first = true;
            foreach ($mobile_order as $key) :
                $node = $nodes[$key];
                if (!$first) : ?>
                    <div class="unmask-node-connection" style="animation-delay: -<?php echo array_search($key, $mobile_order) * 0.5; ?>s;"></div>
                <?php endif; ?>

                <div class="unmask-node-card <?php echo isset($node['is_you']) ? 'unmask-node-card--you' : ''; ?>">
                    <div class="unmask-node-card__heat" style="animation-delay: -<?php echo array_search($key, $mobile_order); ?>s;"></div>
                    <div class="unmask-node-card__content">
                        <div class="unmask-node-card__icon-wrap">
                            <?php if (isset($node['is_you'])) : ?>
                                <div class="unmask-node-card__pulse"></div>
                            <?php endif; ?>
                            <span class="unmask-node-card__icon"><?php echo esc_html($node['icon']); ?></span>
                        </div>
                        <div class="unmask-node-card__info">
                            <div class="unmask-node-card__name"><?php echo esc_html($node['title']); ?></div>
                            <div class="unmask-node-card__desc"><?php echo esc_html($node['desc']); ?></div>
                            <?php if (isset($node['is_you'])) : ?>
                                <div class="unmask-node-card__tag">← YOU ARE HERE</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php
                $first = false;
            endforeach;
            ?>
        </div>

    </main>

    <!-- Footer -->
    <footer class="unmask-onboarding__footer">
        <a href="<?php echo esc_url(home_url('/welcome/start/')); ?>" class="unmask-onboarding__cta unmask-onboarding__cta--primary" data-onboarding-next="3">
            [continue]
        </a>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="unmask-onboarding__skip">skip onboarding</a>
    </footer>

</div>

<script>
(function() {
    'use strict';

    // Node hover detail popup (desktop only)
    var nodes = document.querySelectorAll('.unmask-constellation__node');
    var detail = document.getElementById('nodeDetail');

    var nodeData = {
        portal: {
            title: 'THE PORTAL',
            desc: 'Your dossier. ISO Board. The community layer. You are already inside.'
        },
        archive: {
            title: 'THE ARCHIVE',
            desc: 'Documentation of Chicago\'s creative underground. Records. Profiles. The thing itself.'
        },
        factory: {
            title: 'THE FACTORY',
            desc: 'The studio. $40/hour. Professional lighting. Create work. Be documented.'
        },
        panthers: {
            title: 'PINK PANTHERS',
            desc: 'Monthly. You will be photographed. You will be part of the record. The night documented.'
        },
        submit: {
            title: 'SUBMIT',
            desc: 'Send your work. Become part of the archive.'
        }
    };

    if (nodes.length && detail) {
        nodes.forEach(function(node) {
            node.addEventListener('mouseenter', function(e) {
                var data = nodeData[node.dataset.node];
                if (data) {
                    detail.querySelector('.unmask-constellation__detail-title').textContent = data.title;
                    detail.querySelector('.unmask-constellation__detail-desc').textContent = data.desc;

                    var rect = node.getBoundingClientRect();
                    var containerRect = document.querySelector('.unmask-constellation').getBoundingClientRect();

                    detail.style.left = (rect.left - containerRect.left + 80) + 'px';
                    detail.style.top = (rect.top - containerRect.top - 20) + 'px';
                    detail.classList.add('unmask-constellation__detail--visible');
                }
            });

            node.addEventListener('mouseleave', function() {
                detail.classList.remove('unmask-constellation__detail--visible');
            });
        });
    }

    // Mark step complete
    var nextBtn = document.querySelector('[data-onboarding-next]');
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            document.cookie = 'unmask_onboarding_step=3; path=/; max-age=' + (60*60*24*30);
        });
    }
})();
</script>

<?php get_footer('fullbleed'); ?>
