<main><?php
  // Get paths where modules might exist
	$module_paths = apply_filters('tofino_custom_module_paths', [get_template_directory() . '/templates/modules/']);

  // Check if the flexible content field has rows of data
  if (have_rows('content_modules')) :
    $i = 1;
    while (have_rows('content_modules')) : the_row(); // Loop ACF layouts and display the matching partial
      $row_layout = str_replace('_', '-', get_row_layout()); // Replace _ with - for the filename ?>
      <!-- Start <?php echo $row_layout; ?> -->
      <div class="module module-<?php echo $row_layout; ?> module-<?php echo $i; ?>"><?php
        // Look for the module file in the paths
        foreach ($module_paths as $path) {
          if (file_exists($path . $row_layout . '.php')) {
            if (strpos($path, get_template_directory()) === false) {
              $is_from_plugin = true; // Mark as loaded from a plugin
            }

            // Load the module file
            include $path . $row_layout . '.php';

            break; // Exit loop if the file is found
          }
        }
        
        if (isset($is_from_plugin)) : ?>
          <!-- Loaded from plugin module --><?php 
        endif; ?>
      </div>
      <!-- End <?php echo $row_layout; ?> --><?php
      $i++;
    endwhile;
  else :
    the_content();
  endif; ?>
</main>