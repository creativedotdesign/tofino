<?php get_header(); ?>

<main class="container flex flex-col items-center justify-center">
  <div class="w-full text-center">
    <span class="text-7xl">
      404
    </span>

    <p>
      <?php _e('Sorry, but the page you were trying to view does not exist.', 'tofino'); ?>
    </p>

    <a href="<?php echo esc_url(home_url()); ?>">
      Back to home
    </a>
  </div>
</main>

<?php get_footer(); ?>
