<?php
/**
 * ISO Submit Form - Multi-Step Wizard
 *
 * Multi-step form for submitting ISO Board listings.
 * Used by [unmask_iso_submit_form] shortcode.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Must be logged in
if (!is_user_logged_in()) {
    echo '<div class="iso-alert-banner">
        <span class="iso-alert-text">sign in to post a listing</span>
        <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="iso-alert-cta">[sign in]</a>
    </div>';
    return;
}

// Calculate date constraints
$min_date = date('Y-m-d', strtotime('+1 day'));
$max_date = date('Y-m-d', strtotime('+30 days'));
$default_date = $max_date;

// Get nonce for AJAX
$nonce = wp_create_nonce('unmask_iso_submit');
?>

<div class="iso-form-page">

    <!-- Form Header -->
    <header class="iso-form-header">
        <h1 class="iso-form-title">post to iso board</h1>
        <p class="iso-form-subtitle">seeking or offering something?</p>
    </header>

    <!-- Progress Bar -->
    <div class="iso-progress-bar" id="isoProgressBar">
        <div class="iso-progress-step active" data-step="1"></div>
        <div class="iso-progress-step" data-step="2"></div>
        <div class="iso-progress-step" data-step="3"></div>
        <div class="iso-progress-step" data-step="4"></div>
        <div class="iso-progress-step" data-step="5"></div>
    </div>

    <!-- Error Message -->
    <div class="iso-form-error" id="isoFormError">
        <p class="iso-form-error-text" id="isoErrorText">please complete all required fields</p>
    </div>

    <!-- Step 1: Type -->
    <div class="iso-form-step active" data-step="1">
        <span class="iso-step-label">step 1 of 5</span>
        <h2 class="iso-step-question">are you seeking or offering?</h2>

        <div class="iso-type-toggle">
            <button type="button" class="iso-type-btn" data-type="seeking">
                <span class="iso-type-btn-label">seeking</span>
                <span class="iso-type-btn-hint">you need something</span>
            </button>
            <button type="button" class="iso-type-btn" data-type="offering">
                <span class="iso-type-btn-label">offering</span>
                <span class="iso-type-btn-hint">you have something</span>
            </button>
        </div>

        <div class="iso-form-nav">
            <a href="<?php echo esc_url(home_url('/iso-board/')); ?>" class="iso-btn iso-btn-secondary">[cancel]</a>
            <button type="button" class="iso-btn iso-btn-primary" id="step1Next" disabled>[next]</button>
        </div>
    </div>

    <!-- Step 2: Category -->
    <div class="iso-form-step" data-step="2">
        <span class="iso-step-label">step 2 of 5</span>
        <h2 class="iso-step-question">what category?</h2>

        <div class="iso-category-grid">
            <button type="button" class="iso-category-btn" data-category="photographer">
                <span class="iso-category-btn-label">photographer</span>
            </button>
            <button type="button" class="iso-category-btn" data-category="model">
                <span class="iso-category-btn-label">model</span>
            </button>
            <button type="button" class="iso-category-btn" data-category="collaborator">
                <span class="iso-category-btn-label">collaborator</span>
            </button>
            <button type="button" class="iso-category-btn" data-category="connection">
                <span class="iso-category-btn-label">connection</span>
            </button>
        </div>

        <div class="iso-form-nav">
            <button type="button" class="iso-btn iso-btn-secondary iso-btn-back">[back]</button>
            <button type="button" class="iso-btn iso-btn-primary" id="step2Next" disabled>[next]</button>
        </div>
    </div>

    <!-- Step 3: Title + Description -->
    <div class="iso-form-step" data-step="3">
        <span class="iso-step-label">step 3 of 5</span>
        <h2 class="iso-step-question">describe what you need</h2>

        <div class="iso-form-group">
            <label class="iso-form-label" for="isoTitle">title <span class="required">*</span></label>
            <input type="text" class="iso-form-input" id="isoTitle" name="iso_title"
                   placeholder="issue 2 cover shoot — seeking someone bold"
                   maxlength="100">
            <p class="iso-form-hint">keep it specific. what are you seeking or offering?</p>
        </div>

        <div class="iso-form-group">
            <label class="iso-form-label" for="isoDescription">description <span class="required">*</span></label>
            <textarea class="iso-form-input iso-form-textarea" id="isoDescription" name="iso_description"
                      placeholder="Looking for a model comfortable with artistic nudity for the second issue cover. The concept involves chiaroscuro lighting and sculptural poses..."
                      maxlength="1000"></textarea>
            <div class="iso-char-count" id="isoCharCount">0 / 1000</div>
        </div>

        <div class="iso-form-nav">
            <button type="button" class="iso-btn iso-btn-secondary iso-btn-back">[back]</button>
            <button type="button" class="iso-btn iso-btn-primary" id="step3Next" disabled>[next]</button>
        </div>
    </div>

    <!-- Step 4: Location + Factory + Expiration -->
    <div class="iso-form-step" data-step="4">
        <span class="iso-step-label">step 4 of 5</span>
        <h2 class="iso-step-question">logistics</h2>

        <div class="iso-form-group">
            <label class="iso-form-label">location <span class="required">*</span></label>
            <div class="iso-location-grid">
                <button type="button" class="iso-location-btn selected" data-location="chicago">
                    <span class="iso-location-btn-label">chicago</span>
                </button>
                <button type="button" class="iso-location-btn" data-location="remote">
                    <span class="iso-location-btn-label">remote</span>
                </button>
                <button type="button" class="iso-location-btn" data-location="other">
                    <span class="iso-location-btn-label">other</span>
                </button>
            </div>
        </div>

        <div class="iso-form-group">
            <label class="iso-form-label">hosting at the factory?</label>
            <div class="iso-factory-options">
                <div class="iso-factory-option" data-factory="yes">
                    <div class="iso-factory-radio"></div>
                    <div class="iso-factory-option-content">
                        <div class="iso-factory-option-label">yes</div>
                        <div class="iso-factory-option-hint">studio in Back of the Yards. $40/hr (Drones: $32/hr)</div>
                    </div>
                </div>
                <div class="iso-factory-option" data-factory="preferred">
                    <div class="iso-factory-radio"></div>
                    <div class="iso-factory-option-content">
                        <div class="iso-factory-option-label">preferred</div>
                        <div class="iso-factory-option-hint">would like to, open to other locations</div>
                    </div>
                </div>
                <div class="iso-factory-option selected" data-factory="open">
                    <div class="iso-factory-radio"></div>
                    <div class="iso-factory-option-content">
                        <div class="iso-factory-option-label">open</div>
                        <div class="iso-factory-option-hint">no preference on location</div>
                    </div>
                </div>
                <div class="iso-factory-option" data-factory="no">
                    <div class="iso-factory-radio"></div>
                    <div class="iso-factory-option-content">
                        <div class="iso-factory-option-label">not needed</div>
                        <div class="iso-factory-option-hint">remote or already have a space</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="iso-form-group">
            <label class="iso-form-label" for="isoExpiration">expires on <span class="required">*</span></label>
            <input type="date" class="iso-form-date" id="isoExpiration" name="iso_expiration"
                   min="<?php echo esc_attr($min_date); ?>"
                   max="<?php echo esc_attr($max_date); ?>"
                   value="<?php echo esc_attr($default_date); ?>">
            <p class="iso-form-hint">max 30 days from today. listing will be archived after this date.</p>
        </div>

        <div class="iso-form-nav">
            <button type="button" class="iso-btn iso-btn-secondary iso-btn-back">[back]</button>
            <button type="button" class="iso-btn iso-btn-primary" id="step4Next">[next]</button>
        </div>
    </div>

    <!-- Step 5: Review -->
    <div class="iso-form-step" data-step="5">
        <span class="iso-step-label">step 5 of 5</span>
        <h2 class="iso-step-question">review your listing</h2>

        <div class="iso-review-section">
            <div class="iso-review-label">type</div>
            <div class="iso-review-value" id="reviewType">—</div>
        </div>

        <div class="iso-review-section">
            <div class="iso-review-label">category</div>
            <div class="iso-review-value" id="reviewCategory">—</div>
        </div>

        <div class="iso-review-section">
            <div class="iso-review-label">title</div>
            <div class="iso-review-value" id="reviewTitle">—</div>
        </div>

        <div class="iso-review-section">
            <div class="iso-review-label">description</div>
            <div class="iso-review-value iso-review-description" id="reviewDescription">—</div>
        </div>

        <div class="iso-review-section">
            <div class="iso-review-label">location</div>
            <div class="iso-review-value" id="reviewLocation">—</div>
        </div>

        <div class="iso-review-section">
            <div class="iso-review-label">factory</div>
            <div class="iso-review-value" id="reviewFactory">—</div>
        </div>

        <div class="iso-review-section">
            <div class="iso-review-label">expires</div>
            <div class="iso-review-value" id="reviewExpiration">—</div>
        </div>

        <div class="iso-form-nav">
            <button type="button" class="iso-btn iso-btn-secondary iso-btn-back">[back]</button>
            <button type="button" class="iso-btn iso-btn-primary iso-btn-submit" id="isoSubmitBtn">[post listing]</button>
        </div>
    </div>

    <!-- Success State -->
    <div class="iso-success-message" id="isoSuccessMessage">
        <div class="iso-success-icon"></div>
        <h2 class="iso-success-title">listing posted</h2>
        <p class="iso-success-text">your iso is now live on the board</p>
        <p class="iso-success-redirect">redirecting to your listing...</p>
    </div>

</div>

<!-- Hidden nonce field -->
<input type="hidden" id="isoNonce" value="<?php echo esc_attr($nonce); ?>">
<input type="hidden" id="isoAjaxUrl" value="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
