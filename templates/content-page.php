<main>
  <div class="container">
    <div class="w-11/12 mx-auto md:w-full"><?php
      while (have_posts()) : the_post(); ?>
        <h1><?php echo \Tofino\Helpers\title(); ?></h1>

        <?php echo do_shortcode('[social_icons platforms="soundcloud,twitter,facebook,pinterest"]'); ?>

        <?php the_content(); ?><?php
      endwhile; ?>
    </div>
  </div>
</main>
