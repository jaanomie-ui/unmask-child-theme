<?php
/**
 * Template Name: Register Visitor
 * Template Post Type: page
 *
 * UNMASK Registration page with terminal aesthetic.
 * Uses MemberPress for form handling with custom styling.
 *
 * @package BuddyBoss_Child
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url('/welcome/'));
    exit;
}

// Get dynamic stats for terminal display
$active_users = count(get_users(array('role__in' => array('subscriber', 'administrator'), 'number' => -1)));
$online_count = function_exists('bp_get_online_users') ? bp_get_online_users() : rand(10, 50);

get_header('fullbleed');
?>

<div class="registration-page">

    <!-- System Bar -->
    <header class="system-bar">
        <div class="system-bar-left">
            <span class="system-logo">UNMASK</span>
            <span class="system-path">/ registration</span>
        </div>
        <div class="system-bar-right">
            <span class="system-status">
                <span class="status-dot"></span>
                system online
            </span>
        </div>
    </header>

    <!-- Main Content -->
    <main class="registration-frame">

        <!-- Left Column: Form -->
        <section class="registration-form-container">

            <div class="registration-header">
                <h1 class="registration-title">ENTER THE SYSTEM</h1>
                <p class="registration-intro">The system does not ask why you are here. It asks what you are willing to do.</p>
            </div>

            <div class="registration-form">
                <!-- Function Select (Custom - handled via JS/PHP) -->
                <div class="form-group">
                    <label class="form-label">select function(s)</label>
                    <div class="function-grid">
                        <label class="function-option">
                            <input type="checkbox" name="unmask_function[]" value="subject">
                            <span class="function-box">
                                <span class="function-code">SUBJECT</span>
                                <span class="function-desc">to be documented</span>
                            </span>
                        </label>
                        <label class="function-option">
                            <input type="checkbox" name="unmask_function[]" value="operator">
                            <span class="function-box">
                                <span class="function-code">OPERATOR</span>
                                <span class="function-desc">to document others</span>
                            </span>
                        </label>
                        <label class="function-option">
                            <input type="checkbox" name="unmask_function[]" value="performer">
                            <span class="function-box">
                                <span class="function-code">PERFORMER</span>
                                <span class="function-desc">to move</span>
                            </span>
                        </label>
                    </div>
                </div>

                <?php
                /**
                 * MemberPress Registration Form
                 * Update the ID to match your membership level.
                 * Default: 2093 (Visitor membership)
                 */
                $membership_id = apply_filters('unmask_visitor_membership_id', 2093);
                echo do_shortcode('[mepr-membership-registration-form id="' . esc_attr($membership_id) . '"]');
                ?>

                <!-- Already member -->
                <div class="form-footer">
                    <span class="form-footer-text">Already in the system?</span>
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="form-footer-link">[sign in]</a>
                </div>
            </div>

        </section>

        <!-- Right Column: Terminal Display -->
        <aside class="registration-terminal">
            <div class="terminal-window">
                <div class="terminal-header">
                    <span class="terminal-title">system.log</span>
                    <span class="terminal-controls">
                        <span class="terminal-dot"></span>
                        <span class="terminal-dot"></span>
                        <span class="terminal-dot"></span>
                    </span>
                </div>
                <div class="terminal-body">
                    <div class="terminal-line">> initializing registration protocol...</div>
                    <div class="terminal-line">> checking system capacity...</div>
                    <div class="terminal-line terminal-success">> <?php echo esc_html($active_users); ?> drones active</div>
                    <div class="terminal-line terminal-success">> <?php echo esc_html($online_count); ?> visitors online</div>
                    <div class="terminal-line">> awaiting input...</div>
                    <div class="terminal-line terminal-blink">_</div>
                </div>
            </div>

            <div class="terminal-notice">
                <p class="notice-text">No payment required. No upsell. Just entry.</p>
                <p class="notice-text">The system is patient.</p>
            </div>
        </aside>

    </main>

</div>

<?php get_footer('fullbleed'); ?>
