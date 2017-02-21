<main>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <?php while (have_posts()) : the_post(); ?>
          <h1><?php echo \Tofino\Helpers\title(); ?></h1>
          <?php the_content(); ?>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</main>
