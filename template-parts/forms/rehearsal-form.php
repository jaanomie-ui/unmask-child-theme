<?php
/**
 * Rehearsal Planning Form Template
 *
 * Variables available:
 * - $is_handler (bool) - True if current user is a handler
 * - $rehearsal (array) - Rehearsal plan data
 * - $plan_owner_id (int) - User ID whose plan is being viewed
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="hm-rehearsal-form">
    <h2><?php echo $is_handler ? 'Handler: Confirm Rehearsal Plan' : 'Drone: Propose Rehearsal Plan'; ?></h2>

    <?php if ($is_handler && !$rehearsal['drone_submitted']): ?>
        <div class="hm-info-notice">
            <p>Waiting for drone to submit rehearsal plan...</p>
        </div>
    <?php endif; ?>

    <form id="rehearsalForm" class="hm-form">
        <?php wp_nonce_field('hm_rehearsal_nonce', 'nonce'); ?>

        <!-- Date/Time/Location -->
        <div class="hm-form-section">
            <h3>Logistics</h3>

            <label>
                Proposed Date
                <input type="date" name="proposed_date" value="<?php echo esc_attr($rehearsal['proposed_date']); ?>" <?php echo $is_handler ? 'readonly' : ''; ?> required>
            </label>

            <label>
                Proposed Time
                <input type="time" name="proposed_time" value="<?php echo esc_attr($rehearsal['proposed_time']); ?>" <?php echo $is_handler ? 'readonly' : ''; ?> required>
            </label>

            <label>
                Location
                <input type="text" name="location" value="<?php echo esc_attr($rehearsal['location']); ?>" <?php echo $is_handler ? 'readonly' : ''; ?> placeholder="e.g., the factory, PPNC venue" required>
            </label>
        </div>

        <!-- Performance Parts Checklist -->
        <div class="hm-form-section">
            <h3>Performance Parts Readiness</h3>

            <?php
            $parts = array(
                'encasement' => 'ENCASEMENT CEREMONY - Gear installation, transformation',
                'inspection' => 'PUBLIC INSPECTION - Handler checks, stillness',
                'dressage' => 'THE DRESSAGE - Commanded movements (prance, trot, halt)',
                'procession' => 'THE PROCESSION - Led on reins, witnessed objectification'
            );

            foreach ($parts as $key => $label):
                $part_data = $rehearsal['performance_parts'][$key];
            ?>
            <div class="hm-part-item">
                <label class="hm-checkbox">
                    <input type="checkbox" name="<?php echo $key; ?>_ready" <?php checked($part_data['ready']); ?> <?php echo $is_handler ? 'disabled' : ''; ?>>
                    <strong><?php echo $label; ?></strong>
                </label>

                <label>
                    Notes
                    <textarea name="<?php echo $key; ?>_notes" rows="2" <?php echo $is_handler ? 'readonly' : ''; ?>><?php echo esc_textarea($part_data['notes']); ?></textarea>
                </label>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Gear Checklist -->
        <div class="hm-form-section">
            <h3>Gear Checklist</h3>
            <label>
                List all gear needed for rehearsal
                <textarea name="gear_checklist" rows="5" <?php echo $is_handler ? 'readonly' : ''; ?> placeholder="e.g., Silicone bodysuit, cage, bit collar, tail, hood"><?php echo esc_textarea($rehearsal['gear_checklist']); ?></textarea>
            </label>
        </div>

        <!-- Handler Confirmation (handler only) -->
        <?php if ($is_handler): ?>
        <div class="hm-form-section hm-handler-confirm">
            <label class="hm-checkbox-large">
                <input type="checkbox" name="handler_confirmed" <?php checked($rehearsal['handler_confirmed']); ?>>
                <strong>HANDLER CONFIRMS: Rehearsal plan approved. Ready to proceed.</strong>
            </label>
        </div>
        <?php endif; ?>

        <button type="submit" class="hm-btn-primary">
            <?php echo $is_handler ? 'Confirm Rehearsal Plan' : 'Submit Rehearsal Plan'; ?>
        </button>

        <div class="hm-form-message"></div>
    </form>
</div>

<script>
document.getElementById('rehearsalForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'hm_save_rehearsal');

    const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    const messageDiv = this.querySelector('.hm-form-message');
    if (result.success) {
        messageDiv.innerHTML = '<div class="hm-success">' + result.data.message + '</div>';

        // If sequence advanced, show special notice
        if (result.data.sequence_advanced) {
            messageDiv.innerHTML += '<div class="hm-success"><strong>SEQUENCE 4 COMPLETE:</strong> Advanced to Sequence 5: DEPLOYMENT PREPARATION</div>';

            // Reload page after 2 seconds to show updated sequence
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    } else {
        messageDiv.innerHTML = '<div class="hm-error">' + (result.data || 'Error saving rehearsal plan') + '</div>';
    }
});
</script>
