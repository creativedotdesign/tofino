<main><?php
  // Check if the flexible content field has rows of data
  if (have_rows('content_modules')) :
    $i = 1;
    while (have_rows('content_modules')) : the_row(); // Loop ACF layouts and display the matching partial
      $row_layout = str_replace('_', '-', get_row_layout()); // Replace _ with - for the filename ?>
      <!-- Start <?php echo $row_layout; ?> -->
      <div class="module module-<?php echo $row_layout; ?> module-<?php echo $i; ?>">
        <?php get_template_part('templates/modules/' . $row_layout); ?>
      </div>
      <!-- End <?php echo $row_layout; ?> --><?php
      $i++;
    endwhile;
  else :
    the_content();
  endif; ?>
</main>
