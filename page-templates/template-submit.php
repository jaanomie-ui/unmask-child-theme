<?php
/**
 * Template Name: Submit
 * Description: Submission intake form for UNMASK
 *
 * @package UNMASK
 * @since 1.0.0
 *
 * ACF Fields (non-repeating):
 * - submit_page_label: Text (default: "submit")
 * - submit_page_title: Text (default: "work for the archive")
 * - submit_intro_text: Textarea (default: "think pieces. photo essays...")
 * - submit_response_time: Text (default: "expect a response within 2 weeks.")
 * - submit_success_message: Text (default: "received.")
 * - submit_error_message: Text (default: "something went wrong...")
 * - submit_email_address: Email (default: "submissions@unmaskmagazine.com")
 * - submit_btn_create: Text (default: "create something")
 * - submit_btn_submit: Text (default: "submit something")
 * - submit_placeholder_create: Text
 * - submit_placeholder_submit: Text
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get ACF field values with fallbacks
$submit_page_label = unmask_get_field('submit_page_label', 'submit');
$submit_page_title = unmask_get_field('submit_page_title', 'work for the archive');
$submit_intro_text = unmask_get_field('submit_intro_text', "think pieces. photo essays. kink-adjacent things.<br>\nthe stuff you cannot post elsewhere.<br>\nthe stuff you want in print.");
$submit_response_time = unmask_get_field('submit_response_time', 'expect a response within 2 weeks.');
$submit_success_message = unmask_get_field('submit_success_message', 'received.');
$submit_error_message = unmask_get_field('submit_error_message', 'something went wrong. try again or email us directly.');
$submit_email_address = unmask_get_field('submit_email_address', 'submissions@unmaskmagazine.com');
$submit_btn_create = unmask_get_field('submit_btn_create', 'create something');
$submit_btn_submit = unmask_get_field('submit_btn_submit', 'submit something');
$submit_placeholder_create = unmask_get_field('submit_placeholder_create', 'what are you working on. what do you need.');
$submit_placeholder_submit = unmask_get_field('submit_placeholder_submit', 'describe the work. what is it, why does it belong in the archive.');

// Handle form submission
$form_submitted = false;
$form_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unmask_submit_nonce'])) {
    if (wp_verify_nonce($_POST['unmask_submit_nonce'], 'unmask_submit_form')) {

        $intent = sanitize_text_field($_POST['intent'] ?? 'create');
        $name = sanitize_text_field($_POST['submitter_name'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $link = esc_url_raw($_POST['link'] ?? '');

        // Get email - from form or from logged-in user
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $email = $current_user->user_email;
        } else {
            $email = sanitize_email($_POST['email'] ?? '');
        }

        // Validate required fields
        if (empty($name) || empty($email) || empty($description)) {
            $form_error = true;
        } else {
            // Build email
            $intent_label = ($intent === 'submit') ? 'Submit Something' : 'Create Something';

            $to = $submit_email_address;
            $subject = "[UNMASK] New Submission: {$intent_label}";

            $body = "New submission from the UNMASK website.\n\n";
            $body .= "Intent: {$intent_label}\n";
            $body .= "Name: {$name}\n";
            $body .= "Email: {$email}\n\n";
            $body .= "Description:\n{$description}\n";

            if (!empty($link)) {
                $body .= "\nLink: {$link}\n";
            }

            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                "Reply-To: {$name} <{$email}>"
            );

            $sent = wp_mail($to, $subject, $body, $headers);

            if ($sent) {
                $form_submitted = true;
            } else {
                $form_error = true;
            }
        }
    }
}

// Get current user info if logged in
$is_logged_in = is_user_logged_in();
$user_name = '';
$user_email = '';

if ($is_logged_in) {
    $current_user = wp_get_current_user();
    $user_name = $current_user->first_name ?: $current_user->display_name;
    $user_email = $current_user->user_email;
}

get_header();
?>

<div class="submit-page<?php echo $is_logged_in ? ' user-logged-in' : ''; ?>">
    <div class="submit-frame">

        <div class="page-label"><?php echo esc_html($submit_page_label); ?></div>
        <h1><?php echo esc_html($submit_page_title); ?></h1>

        <?php if ($form_submitted) : ?>

            <div class="submit-success">
                <p class="success-message"><?php echo esc_html($submit_success_message); ?></p>
                <p class="success-detail"><?php echo esc_html($submit_response_time); ?></p>
            </div>

        <?php else : ?>

            <p class="intro">
                <?php echo wp_kses_post($submit_intro_text); ?>
                <span><?php echo esc_html($submit_response_time); ?></span>
            </p>

            <?php if ($form_error) : ?>
                <div class="submit-error">
                    <p><?php echo esc_html($submit_error_message); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" class="submit-form">
                <?php wp_nonce_field('unmask_submit_form', 'unmask_submit_nonce'); ?>
                <input type="hidden" name="intent" id="intent-field" value="create">

                <div class="submit-mode-selector" role="tablist" aria-label="Submission type">
                    <button type="button" role="tab" class="submit-mode-tab active" data-status="create" aria-selected="true"><?php echo esc_html($submit_btn_create); ?></button>
                    <button type="button" role="tab" class="submit-mode-tab" data-status="submit" aria-selected="false"><?php echo esc_html($submit_btn_submit); ?></button>
                </div>

                <div class="submit-mode-description" aria-live="polite">
                    <p class="submit-mode-description__text" data-mode="create">
                        <strong>Create</strong> — Start a new project from scratch. We'll help shape your idea into something publishable.
                    </p>
                    <p class="submit-mode-description__text" data-mode="submit" hidden>
                        <strong>Submit</strong> — You already have finished work ready for publication. Send it our way.
                    </p>
                </div>

                <div class="form-group">
                    <label for="submitter_name">name</label>
                    <input type="text" name="submitter_name" id="submitter_name" required
                           value="<?php echo esc_attr($user_name); ?>"
                           <?php echo $is_logged_in && !empty($user_name) ? 'readonly' : ''; ?>>
                </div>

                <div class="form-group form-group-email">
                    <label for="email">email</label>
                    <input type="email" name="email" id="email" <?php echo !$is_logged_in ? 'required' : ''; ?>
                           value="<?php echo esc_attr($user_email); ?>">
                </div>

                <div class="form-group">
                    <label for="description">tell me about it</label>
                    <textarea name="description" id="description" required
                              placeholder="<?php echo esc_attr($submit_placeholder_create); ?>"></textarea>
                </div>

                <div class="form-group">
                    <label for="link">link</label>
                    <input type="url" name="link" id="link" placeholder="portfolio, google doc, instagram, etc.">
                    <p class="helper">optional. something that shows your work.</p>
                </div>

                <button type="submit" class="btn-submit">[submit]</button>

            </form>

        <?php endif; ?>

    </div>
</div>

<script>
(function() {
    var textarea = document.getElementById('description');
    var intentField = document.getElementById('intent-field');

    if (!textarea || !intentField) return;

    var placeholders = {
        create: <?php echo json_encode($submit_placeholder_create); ?>,
        submit: <?php echo json_encode($submit_placeholder_submit); ?>
    };

    var tabs = document.querySelectorAll('.submit-mode-tab');
    var descriptions = document.querySelectorAll('.submit-mode-description__text');

    tabs.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var status = this.dataset.status;

            // Update tab states
            tabs.forEach(function(b) {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            // Update descriptions visibility
            descriptions.forEach(function(desc) {
                if (desc.dataset.mode === status) {
                    desc.hidden = false;
                } else {
                    desc.hidden = true;
                }
            });

            // Update form state
            textarea.placeholder = placeholders[status];
            intentField.value = status;
        });
    });
})();
</script>

<?php get_footer(); ?>
