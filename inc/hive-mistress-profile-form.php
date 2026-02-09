<?php
/**
 * Hive Mistress Profile Form
 * Allows users to manage gear, safeword, and availability
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hive Mistress profile form shortcode
 * Usage: [hive_mistress_profile]
 */
function hm_profile_form_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to manage your profile.</p>';
    }

    $user_id = get_current_user_id();
    $profile = get_user_meta($user_id, 'hm_user_profile', true);

    $gear = isset($profile['gear']) ? esc_textarea($profile['gear']) : '';
    $safeword = isset($profile['safeword']) ? esc_attr($profile['safeword']) : '';
    $availability = isset($profile['availability']) ? esc_attr($profile['availability']) : '';

    ob_start();
    ?>
    <div class="hm-profile-form-container">
        <h3>Hive Mistress Profile</h3>

        <form id="hm-profile-form" class="hm-profile-form">
            <div class="hm-form-field">
                <label for="hm-gear">Gear Available:</label>
                <textarea
                    id="hm-gear"
                    name="gear"
                    rows="3"
                    placeholder="e.g., Spandex bodysuits, Silicone bodysuits"
                ><?php echo $gear; ?></textarea>
                <p class="hm-field-help">List the equipment you have access to for conditioning sessions.</p>
            </div>

            <div class="hm-form-field">
                <label for="hm-safeword">Safe Signal:</label>
                <input
                    type="text"
                    id="hm-safeword"
                    name="safeword"
                    value="<?php echo $safeword; ?>"
                    placeholder="e.g., 3 successive stomps, left hoof"
                />
                <p class="hm-field-help">Your emergency signal to pause/stop conditioning.</p>
            </div>

            <div class="hm-form-field">
                <label for="hm-availability">Availability:</label>
                <input
                    type="text"
                    id="hm-availability"
                    name="availability"
                    value="<?php echo $availability; ?>"
                    placeholder="e.g., Tue, Wed, Thu after 2pm"
                />
                <p class="hm-field-help">When you're typically available for sessions.</p>
            </div>

            <div class="hm-form-actions">
                <button type="submit" class="hm-btn-primary">Save Profile</button>
            </div>

            <div id="hm-profile-message" class="hm-message" style="display: none;"></div>
        </form>
    </div>

    <style>
        .hm-profile-form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--bg-card, #1a1a1a);
            border-radius: 8px;
        }

        .hm-profile-form-container h3 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: var(--text-primary, #ffffff);
        }

        .hm-form-field {
            margin-bottom: 1.5rem;
        }

        .hm-form-field label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary, #ffffff);
        }

        .hm-form-field input[type="text"],
        .hm-form-field textarea {
            width: 100%;
            padding: 0.75rem;
            background: var(--bg-input, #2a2a2a);
            border: 1px solid var(--border-color, #444);
            border-radius: 4px;
            color: var(--text-primary, #ffffff);
            font-family: inherit;
            font-size: 1rem;
        }

        .hm-form-field textarea {
            resize: vertical;
        }

        .hm-field-help {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: var(--text-secondary, #aaa);
        }

        .hm-form-actions {
            margin-top: 2rem;
        }

        .hm-btn-primary {
            padding: 0.75rem 2rem;
            background: var(--accent-color, #ff006e);
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .hm-btn-primary:hover {
            opacity: 0.9;
        }

        .hm-btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .hm-message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .hm-message.success {
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 255, 0, 0.3);
            color: #00ff00;
        }

        .hm-message.error {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ff0000;
        }
    </style>

    <script>
    (function() {
        const form = document.getElementById('hm-profile-form');
        const message = document.getElementById('hm-profile-message');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            const formData = new FormData(form);
            formData.append('action', 'hm_save_profile');
            formData.append('nonce', '<?php echo wp_create_nonce('hm_profile_nonce'); ?>');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                message.style.display = 'block';

                if (data.success) {
                    message.className = 'hm-message success';
                    message.textContent = 'Profile saved successfully!';
                } else {
                    message.className = 'hm-message error';
                    message.textContent = data.data.message || 'Error saving profile.';
                }

                btn.disabled = false;
                btn.textContent = 'Save Profile';

                // Hide message after 3 seconds
                setTimeout(() => {
                    message.style.display = 'none';
                }, 3000);
            })
            .catch(error => {
                message.style.display = 'block';
                message.className = 'hm-message error';
                message.textContent = 'Network error. Please try again.';

                btn.disabled = false;
                btn.textContent = 'Save Profile';
            });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('hive_mistress_profile', 'hm_profile_form_shortcode');

/**
 * AJAX handler for saving profile
 */
function hm_ajax_save_profile() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'hm_profile_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    // Check if user is logged in
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => 'Please log in to save your profile'));
        return;
    }

    // Get and sanitize form data
    $profile_data = array(
        'gear' => isset($_POST['gear']) ? sanitize_textarea_field($_POST['gear']) : '',
        'safeword' => isset($_POST['safeword']) ? sanitize_text_field($_POST['safeword']) : '',
        'availability' => isset($_POST['availability']) ? sanitize_text_field($_POST['availability']) : ''
    );

    // Save using the hm_update_user_profile function if it exists
    if (function_exists('hm_update_user_profile')) {
        $result = hm_update_user_profile($profile_data, $user_id);
    } else {
        // Fallback: save directly
        $result = update_user_meta($user_id, 'hm_user_profile', $profile_data);
    }

    if ($result !== false) {
        wp_send_json_success(array('message' => 'Profile saved successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to save profile'));
    }
}
add_action('wp_ajax_hm_save_profile', 'hm_ajax_save_profile');
