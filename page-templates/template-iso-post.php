<?php
/**
 * Template Name: Iso Post
 * Description: Custom template for Iso Post page
 *
 * @package UNMASK
 */

get_header(); ?>

<main id="main" class="site-main iso-post-page">
    <div class="unmask-container">

        <?php while (have_posts()) : the_post(); ?>

            <header class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </header>

            <div class="page-content">
                <?php the_content(); ?>
            </div>

        <?php endwhile; ?>

    </div>
</main>

<?php get_footer(); ?>
