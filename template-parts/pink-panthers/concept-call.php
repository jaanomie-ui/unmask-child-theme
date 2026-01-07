<section class="pp-concept">
  <header class="pp-concept__header">
    <h2 class="pp-concept__title">the call</h2>
  </header>

  <div class="pp-card-track">

    <!-- Card 1: Intro -->
    <article class="pp-card pp-card--style-a">
      <div class="pp-card__image" style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/assets/images/pink-panthers/call-01.jpg');"></div>
      <div class="pp-card__content">
        <p class="pp-card__label">001</p>
        <h3 class="pp-card__title">a performance night</h3>
        <p class="pp-card__text">Drag. Burlesque. Live music. Whatever you call what you do. The first edition happens in March.</p>
      </div>
    </article>

    <!-- Card 2: Premise -->
    <article class="pp-card pp-card--style-a">
      <div class="pp-card__image" style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/assets/images/pink-panthers/call-02.jpg');"></div>
      <div class="pp-card__content">
        <p class="pp-card__label">002</p>
        <h3 class="pp-card__title">the lineup is not set</h3>
        <p class="pp-card__text">That is the point. The show builds itself. Submit below. The roster updates as performers confirm.</p>
      </div>
    </article>

    <!-- Card 3: Form -->
    <article class="pp-card pp-card--form">
      <div class="pp-card__content">
        <div class="pp-form-header">
          <h3 class="pp-form-title">submit your act</h3>
          <p class="pp-form-subtitle">response does not guarantee a slot</p>
        </div>

        <?php
        // Option 1: Gravity Forms shortcode
        // echo do_shortcode('[gravityform id="X" title="false" description="false" ajax="true"]');

        // Option 2: Custom form (wire to your preferred handler)
        ?>
        <form class="pp-performer-form" method="post" action="">
          <?php wp_nonce_field('pp_performer_submit', 'pp_nonce'); ?>

          <div class="pp-form-group">
            <label class="pp-form-label">name / stage name</label>
            <input type="text" name="performer_name" class="pp-form-input" placeholder="what do you go by" required>
          </div>

          <div class="pp-form-group">
            <label class="pp-form-label">act type</label>
            <select name="act_type" class="pp-form-select" required>
              <option value="" disabled selected>select one</option>
              <option value="drag">drag</option>
              <option value="burlesque">burlesque</option>
              <option value="dj">dj / music</option>
              <option value="live">live performance</option>
              <option value="performance-art">performance art</option>
              <option value="other">other</option>
            </select>
          </div>

          <div class="pp-form-group">
            <label class="pp-form-label">instagram / link</label>
            <input type="text" name="performer_link" class="pp-form-input" placeholder="@handle or url">
          </div>

          <div class="pp-form-group">
            <label class="pp-form-label">describe your act</label>
            <textarea name="act_description" class="pp-form-textarea" placeholder="what should we expect"></textarea>
          </div>

          <button type="submit" class="pp-btn-submit">[submit]</button>
        </form>
      </div>
    </article>

  </div>

  <div class="pp-scroll-hint">
    <span class="pp-scroll-hint__dot pp-scroll-hint__dot--active"></span>
    <span class="pp-scroll-hint__dot"></span>
    <span class="pp-scroll-hint__dot"></span>
  </div>
</section>
