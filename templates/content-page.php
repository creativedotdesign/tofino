<main role="main">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <?php while (have_posts()) : the_post(); ?>
          <h1><?php the_title(); ?></h1>
          <?php the_content(); ?>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</main>
