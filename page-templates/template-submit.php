<?php
/**
 * Template Name: Submit
 * Description: Submission intake form for UNMASK
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

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

            $to = 'submissions@unmaskmagazine.com';
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

        <div class="page-label">submit</div>
        <h1>work for the archive</h1>

        <?php if ($form_submitted) : ?>

            <div class="submit-success">
                <p class="success-message">received.</p>
                <p class="success-detail">expect a response within 2 weeks.</p>
            </div>

        <?php else : ?>

            <p class="intro">
                think pieces. photo essays. kink-adjacent things.<br>
                the stuff you cannot post elsewhere.<br>
                the stuff you want in print.
                <span>expect a response within 2 weeks.</span>
            </p>

            <?php if ($form_error) : ?>
                <div class="submit-error">
                    <p>something went wrong. try again or email us directly.</p>
                </div>
            <?php endif; ?>

            <form method="post" class="submit-form">
                <?php wp_nonce_field('unmask_submit_form', 'unmask_submit_nonce'); ?>
                <input type="hidden" name="intent" id="intent-field" value="create">

                <div class="status-toggle">
                    <button type="button" class="status-btn active" data-status="create">create something</button>
                    <button type="button" class="status-btn" data-status="submit">submit something</button>
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
                              placeholder="what are you working on. what do you need."></textarea>
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
    const textarea = document.getElementById('description');
    const intentField = document.getElementById('intent-field');

    if (!textarea || !intentField) return;

    const placeholders = {
        create: 'what are you working on. what do you need.',
        submit: 'describe the work. what is it, why does it belong in the archive.'
    };

    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const status = this.dataset.status;
            textarea.placeholder = placeholders[status];
            intentField.value = status;
        });
    });
})();
</script>

<?php get_footer(); ?>
