<?php
/**
 * Tofino includes
 *
 * The $tofino_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 */
$tofino_includes = [
  //'vendor/autoload.php',  // Composer Autoload classes
  'lib/assets.php',
  'lib/init.php'
];
foreach ($tofino_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'tofino'), $file), E_USER_ERROR);
  }
  require_once $filepath;
}
unset($file, $filepath);

/**
 * ACF Filters
*/

//Update ACF settings path
add_filter('acf/settings/path', 'tf_acf_settings_path');

function tf_acf_settings_path( $path ) {
  $path = get_stylesheet_directory() . "/vendor/advanced-custom-fields/advanced-custom-fields-pro/";
  return $path;
}

//Update ACF settings dir
add_filter('acf/settings/dir', 'tf_acf_settings_dir');

function tf_acf_settings_dir( $dir ) {
  $dir = get_stylesheet_directory_uri() . "/vendor/advanced-custom-fields/advanced-custom-fields-pro/";
  return $dir;
}

function add_styles() {
  wp_enqueue_style('main', get_template_directory_uri().'/dist/css/main.css', [] /* @todo - versioning */);
}

function add_scripts () {
  wp_enqueue_script('main', get_template_directory_uri().'/dist/js/main.js', [], null);  
}

add_action('wp_enqueue_scripts', 'add_scripts');

