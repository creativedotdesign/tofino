<main>
  <div class="container"><?php
    while (have_posts()) : the_post(); ?>
      <h1><?php echo \Tofino\Helpers\title(); ?></h1>
      <?php the_content(); ?><?php
    endwhile; ?>
  </div>
</main>
