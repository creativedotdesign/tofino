<main><?php
  $show_module_names = (bool) get_field('show_module_names', 'option');

  // Check if the flexible content field has rows of data
  if (have_rows('content_modules')) :
    $i = 1;
    // Loop ACF layouts and display the matching partial
    while (have_rows('content_modules')) : the_row();
      $row_layout_raw = get_row_layout();
      $row_layout = str_replace('_', '-', $row_layout_raw); // Replace _ with - for the filename ?>

      <!-- Start <?php echo $row_layout; ?> -->
      <div class="module relative module-<?php echo $row_layout; ?> module-<?php echo $i; ?>"><?php
        if ($show_module_names) :
          $row_label = \Tofino\Helpers\get_module_name($row_layout_raw); ?>
          <div class="p-2 hidden md:block text-sm font-bold absolute top-4 left-4 z-10 bg-gray-50">
            <span>Module: <?php echo esc_html($row_label); ?></span>
          </div><?php
        endif; ?>
        <?php \Tofino\Helpers\render_module($row_layout); ?>
      </div>
      <!-- End <?php echo $row_layout; ?> --><?php
      $i++;
    endwhile;
  else :
    the_content();
  endif;
?></main>