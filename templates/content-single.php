<main>
  <div id="post-<?php the_ID(); ?>" <?php post_class('container'); ?>><?php
    while (have_posts()) : the_post(); ?>
      <h1><?php echo get_the_title(); ?></h1>
      <?php the_content(); ?><?php
    endwhile; ?>
  </div>
</main>
