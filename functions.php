<?php
/**
 * Tofino includes
 *
 * The $tofino_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed.
 *
 * Missing files will produce a fatal error.
 *
 */
$tofino_includes = [
  "inc/lib/init.php",
  "inc/lib/assets.php",
  "inc/lib/helpers.php",
  "inc/lib/clean.php",
  "inc/lib/CustomizrTextEditor.php",
  "inc/shortcodes/copyright.php",
  "inc/shortcodes/social-icons.php",
  "inc/shortcodes/svg.php",
  "inc/shortcodes/theme-option.php",
  "inc/theme-options/admin.php",
  "inc/theme-options/advanced.php",
  "inc/theme-options/client-data.php",
  "inc/theme-options/dashboard-widget.php",
  "inc/theme-options/footer.php",
  "inc/theme-options/google-analytics.php",
  "inc/theme-options/init.php",
  "inc/theme-options/maintenance-mode.php",
  "inc/theme-options/menu.php",
  "inc/theme-options/notifications.php",
  "inc/theme-options/social-networks.php",
  "inc/theme-options/theme-tracker.php",
];

foreach ($tofino_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'tofino'), $file), E_USER_ERROR);
  }
  require_once $filepath;
}
unset($file, $filepath);


/**
 * Composer dependencies
 *
 * External dependencies are defined in the composer.json and autoloaded.
 * Use 'composer dump-autoload -o' after adding new files.
 *
 */
if (file_exists(get_template_directory() . '/vendor/autoload.php')) { // Check composer autoload file exists. Result is cached by PHP.
  require_once 'vendor/autoload.php';
} else {
  if (is_admin()) {
    add_action('admin_notices', 'composer_error_notice');
  } else {
    wp_die(composer_error_notice(), __('An error occured.', 'tofino'));
  }
}

// Check for missing dist directory. Result is cached by PHP.
if (!is_dir(get_template_directory() . '/dist')) {
  if (is_admin()) {
    add_action('admin_notices', 'missing_dist_error_notice');
  } else {
    wp_die(missing_dist_error_notice(), __('An error occured.', 'tofino'));
  }
}

// Admin notice for missing composer autoload.
function composer_error_notice() {
?><div class="error notice">
    <p><?php _e('Composer autoload file not found. Run composer install on the command line.', 'tofino'); ?></p>
  </div><?php
}

// Admin notice for missing dist directory.
function missing_dist_error_notice() {
?><div class="error notice">
    <p><?php _e('/dist directory not found. You probably want to run yarn install and npm run dev on the command line.', 'tofino'); ?></p>
  </div><?php
}

// Set ACF JSON save path
function acf_json_save_point($path) {
  $path = get_stylesheet_directory() . '/inc/acf-json'; // Update path

  return $path;
}
add_filter('acf/settings/save_json', 'acf_json_save_point');

// Set ACF JSON load path
function acf_json_load_point($paths) {
  unset($paths[0]); // Remove original path (optional)

  $paths[] = get_stylesheet_directory() . '/inc/acf-json';

  return $paths;
}
add_filter('acf/settings/load_json', 'acf_json_load_point');


/**
 * Turn off YYYY/MM Media folders
 *
 */
add_filter('option_uploads_use_yearmonth_folders', '__return_false', 100);