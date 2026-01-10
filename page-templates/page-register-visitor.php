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

<script>
(function() {
    'use strict';

    var form = document.querySelector('.mepr-signup-form');
    if (!form) return;

    // ==========================================================================
    // HIDE FIELDS COLLECTED LATER (Scene Name, Pronouns)
    // These are collected on the welcome/dossier page, not registration
    // ==========================================================================

    // Hide by checking input name, id, label text, and row classes
    // NOTE: MemberPress uses mp_form_row (underscore) not mp-form-row (hyphen)
    document.querySelectorAll('.mp_form_row, .form-group, .mepr-field, [class*="form-row"]').forEach(function(row) {
        var shouldHide = false;
        var reason = '';

        // Check input name/id
        var input = row.querySelector('input, select, textarea');
        if (input) {
            var name = (input.name || '').toLowerCase();
            var id = (input.id || '').toLowerCase();
            if (name.indexOf('scene') !== -1 || id.indexOf('scene') !== -1) {
                shouldHide = true;
                reason = 'scene in name/id';
            }
            if (name.indexOf('pronoun') !== -1 || id.indexOf('pronoun') !== -1) {
                shouldHide = true;
                reason = 'pronoun in name/id';
            }
        }

        // Check label text
        var label = row.querySelector('label');
        if (label) {
            var labelText = (label.textContent || '').toLowerCase();
            if (labelText.indexOf('scene') !== -1) {
                shouldHide = true;
                reason = 'scene in label';
            }
            if (labelText.indexOf('pronoun') !== -1) {
                shouldHide = true;
                reason = 'pronoun in label';
            }
        }

        // Check row class
        var rowClass = (row.className || '').toLowerCase();
        if (rowClass.indexOf('scene') !== -1 || rowClass.indexOf('pronoun') !== -1) {
            shouldHide = true;
            reason = 'class contains scene/pronoun';
        }

        if (shouldHide) {
            row.style.display = 'none';
            console.log('[UNMASK] Hiding field:', reason);
        }
    });

    // ==========================================================================
    // AUTO-GENERATE USERNAME FROM EMAIL
    // Users never see username - they log in with email
    // ==========================================================================

    var emailInput = form.querySelector('input[name="user_email"], input[name="mepr_user[user_email]"], input#user_email');
    var usernameInput = form.querySelector('input[name="user_login"], input#user_login');
    var sceneNameInput = form.querySelector('input[name*="scene_name"]');
    var firstNameInput = form.querySelector('input[name="mepr_user[first_name]"]');
    var lastNameInput = form.querySelector('input[name="mepr_user[last_name]"]');

    // Generate username from email prefix + short random suffix
    function generateUsername(email) {
        var suffix = Math.random().toString(36).substring(2, 6); // 4 chars
        if (!email || email.indexOf('@') === -1) {
            return 'user_' + suffix;
        }
        var prefix = email.split('@')[0];
        // Clean: lowercase, only letters/numbers/underscores, limit to 15 chars
        prefix = prefix.toLowerCase().replace(/[^a-z0-9_]/g, '').substring(0, 15);
        if (!prefix) prefix = 'user';
        return prefix + '_' + suffix;
    }

    // Fill username immediately on page load
    if (usernameInput) {
        usernameInput.value = generateUsername('');
        console.log('[UNMASK] Username pre-filled:', usernameInput.value);
    }

    // Update username as user types email
    if (emailInput && usernameInput) {
        emailInput.addEventListener('input', function() {
            usernameInput.value = generateUsername(emailInput.value);
        });
        emailInput.addEventListener('blur', function() {
            usernameInput.value = generateUsername(emailInput.value);
        });
    }

    // Auto-fill First/Last Name with placeholders (hidden fields)
    if (firstNameInput && !firstNameInput.value) {
        firstNameInput.value = 'Member';
    }
    if (lastNameInput && !lastNameInput.value) {
        lastNameInput.value = 'UNMASK';
    }

    // Before form submit, ensure all hidden fields are filled
    form.addEventListener('submit', function(e) {
        // Ensure username is set
        if (usernameInput && (!usernameInput.value || usernameInput.value.length < 3)) {
            usernameInput.value = generateUsername(emailInput ? emailInput.value : '');
        }

        // Auto-fill Scene Name if empty
        if (sceneNameInput && !sceneNameInput.value) {
            sceneNameInput.value = 'Visitor';
        }

        // Ensure first/last name are filled
        if (firstNameInput && !firstNameInput.value) {
            firstNameInput.value = 'Member';
        }
        if (lastNameInput && !lastNameInput.value) {
            lastNameInput.value = 'UNMASK';
        }
    });

    // ==========================================================================
    // HIDE ERROR MESSAGES UNTIL INTERACTION
    // ==========================================================================

    var errors = document.querySelectorAll('.cc-error, .mepr-form-has-errors');

    if (errors.length) {
        form.classList.add('unmask-form-pristine');

        // Hide all errors initially
        errors.forEach(function(el) {
            el.style.display = 'none';
        });

        // On form submit, show errors
        form.addEventListener('submit', function() {
            form.classList.remove('unmask-form-pristine');
            errors.forEach(function(el) {
                el.style.display = '';
            });
        });

        // Show errors on field blur
        form.querySelectorAll('input, select, textarea').forEach(function(input) {
            input.addEventListener('blur', function() {
                if (!form.classList.contains('unmask-form-pristine')) return;
                var row = input.closest('.mp-form-row');
                if (row) {
                    var error = row.querySelector('.cc-error');
                    if (error && input.value === '' && input.hasAttribute('required')) {
                        error.style.display = 'block';
                    }
                }
            });
        });
    }
})();
</script>

<?php get_footer('fullbleed'); ?>
