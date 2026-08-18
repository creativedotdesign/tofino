<main><?php
  $page_id = get_queried_object_id() ?: get_the_ID();
  $show_module_names = (bool) get_field('show_module_names', 'option');

  // Check if the flexible content field has rows of data
  if ($page_id && have_rows('content_modules', $page_id)) :
    $i = 1;
    // Loop ACF layouts and display the matching partial
    while (have_rows('content_modules', $page_id)) : the_row();
      $row_layout_raw = get_row_layout();
      $row_layout_slug = str_replace('_', '-', $row_layout_raw); ?>

      <!-- Start <?php echo $row_layout_slug; ?> -->
      <div class="module relative module-<?php echo $row_layout_slug; ?> module-<?php echo $i; ?>"><?php
        if ($show_module_names) :
          $row_label = \Tofino\Helpers\get_module_name($row_layout_raw);
          $row_label = apply_filters('tofino/module_name_label', $row_label, $row_layout_raw); ?>
          <div class="p-2 hidden md:block text-sm font-bold absolute top-4 left-4 z-10 bg-gray-50">
            <span>Module: <?php echo wp_kses_post($row_label); ?></span>
          </div><?php
        endif; ?>
        <?php \Tofino\Helpers\render_module($row_layout_raw); ?>
      </div>
      <!-- End <?php echo $row_layout_slug; ?> --><?php
      $i++;
    endwhile;
  else :
    the_content();
  endif;
?></main>
