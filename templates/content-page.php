<main><?php
  // Check if the flexible content field has rows of data
  if (have_rows('content_modules')) :
    // Loop ACF layouts and display the matching partial
    while (have_rows('content_modules')) : the_row();
      $row_layout = str_replace('_', '-', get_row_layout()); // Replace _ with - for the filename ?>

      <!-- Start <?php echo $row_layout; ?> -->
      <div class="module module-<?php echo $row_layout; ?>">
        <?php \Tofino\Helpers\render_module($row_layout); ?>
      </div>
      <!-- End <?php echo $row_layout; ?> --><?php
    endwhile;
  else :
    the_content();
  endif;
?></main>
