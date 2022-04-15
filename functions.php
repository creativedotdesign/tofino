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
  "inc/lib/AjaxForm.php",
  "inc/lib/contact-form.php",
  "inc/lib/init.php",
  "inc/lib/vite.php",
  "inc/lib/assets.php",
  "inc/lib/helpers.php",
  "inc/lib/clean.php",
  "inc/lib/FragmentCache.php",
  "inc/lib/shortcodes.php",
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
    add_action('admin_notices', function () {
      echo '<div class="error"><p>' . __('Composer autoload file not found. Run composer install on the command line.', 'tofino') . '</p></div>';
    });
  } else {
    wp_die(__('Composer autoload file not found. Run composer install on the command line.', 'tofino'), __('An error occured.', 'tofino'));
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


// Check for ACF Plugin.
if (!class_exists('acf')) {
  if (is_admin()) {
    add_action('admin_notices', 'missing_acf_plugin_notice');
  }
}


// Admin notice for missing composer autoload.
function composer_error_notice()
{
  echo '<div class="error notice"><p><strong>' . __('Theme Error', 'tofino') . '</strong> - ' . __('Composer autoload file not found. Run composer install on the command line.', 'tofino') . '</p></div>';
}


// Admin notice for missing dist directory.
function missing_dist_error_notice()
{
  echo '<div class="error notice"><p><strong>' . __('Theme Error', 'tofino') . '</strong> - ' . __('/dist directory not found. You probably want to run npm install and npm run prod on the command line.', 'tofino') . '</p></div>';
}


// Admin notice for missing ACF plugin.
function missing_acf_plugin_notice()
{
  echo '<div class="error notice"><p><strong>' . __('Missing Plugin', 'tofino') . '</strong> - ' . __('Advanced Custom Fields Pro plugin not found. Please install it.', 'tofino') . '</p></div>';
}
