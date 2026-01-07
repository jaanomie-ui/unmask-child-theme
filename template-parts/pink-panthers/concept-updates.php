<section class="pp-concept pp-updates">
  <header class="pp-concept__header">
    <h2 class="pp-concept__title">updates</h2>
  </header>

  <?php
  // Admin-only postbox
  if (current_user_can('administrator')):
  ?>
  <div class="pp-admin-postbox">
    <p class="pp-admin-postbox__label">post update</p>
    <?php
    // BuddyBoss activity post form
    if (function_exists('bp_get_activity_post_form')):
      bp_get_template_part('activity/post-form');
    else:
    ?>
    <form id="pp-update-form" method="post">
      <textarea class="pp-admin-postbox__textarea" name="pp_update_content" placeholder="what is happening with pink panthers..."></textarea>
      <div class="pp-admin-postbox__actions">
        <button type="submit" class="pp-admin-postbox__btn">[post]</button>
      </div>
    </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="pp-card-track">
    <?php
    // Method 1: Use BuddyBoss activity loop
    // Filter to show only admin's updates
    $admin_id = 1; // Replace with ja's actual user ID

    if (function_exists('bp_has_activities') && bp_has_activities(array(
      'user_id' => $admin_id,
      'per_page' => 10,
      'max' => 10,
      'scope' => false,
      'filter' => array(
        'action' => array('activity_update')
      )
    ))):

      $counter = 1;
      while (bp_activities()): bp_the_activity();
    ?>

    <article class="pp-card pp-card--grid">
      <div class="pp-grid-cell pp-grid-cell--number">
        <span class="pp-grid-number">/<?php echo str_pad($counter, 3, '0', STR_PAD_LEFT); ?></span>
      </div>
      <div class="pp-grid-cell pp-grid-cell--author">
        <span class="pp-grid-author"><?php bp_activity_user_link(); ?></span>
      </div>
      <div class="pp-grid-cell pp-grid-cell--time">
        <span class="pp-grid-time"><?php echo bp_core_time_since(bp_get_activity_date_recorded()); ?></span>
      </div>
      <div class="pp-grid-cell pp-grid-cell--content">
        <p class="pp-grid-content"><?php bp_activity_content_body(); ?></p>
      </div>
      <div class="pp-grid-cell pp-grid-cell--footer">
        <span class="pp-grid-footer-label">update</span>
        <span class="pp-grid-footer-status">live</span>
      </div>
    </article>

    <?php
      $counter++;
      endwhile;
    else:
    ?>

    <!-- Fallback: Static placeholder cards -->
    <article class="pp-card pp-card--grid">
      <div class="pp-grid-cell pp-grid-cell--number">
        <span class="pp-grid-number">/001</span>
      </div>
      <div class="pp-grid-cell pp-grid-cell--author">
        <span class="pp-grid-author">ja</span>
      </div>
      <div class="pp-grid-cell pp-grid-cell--time">
        <span class="pp-grid-time">jan 3, 2025</span>
      </div>
      <div class="pp-grid-cell pp-grid-cell--content">
        <p class="pp-grid-content">Pink Panthers /001 is officially announced. March 1 at the Factory. Call for performers is open.</p>
      </div>
      <div class="pp-grid-cell pp-grid-cell--footer">
        <span class="pp-grid-footer-label">update</span>
        <span class="pp-grid-footer-status">live</span>
      </div>
    </article>

    <?php endif; ?>
  </div>

  <div class="pp-scroll-hint">
    <span class="pp-scroll-hint__dot pp-scroll-hint__dot--active"></span>
    <span class="pp-scroll-hint__dot"></span>
    <span class="pp-scroll-hint__dot"></span>
  </div>
</section>
