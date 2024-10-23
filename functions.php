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
  "inc/lib/vite.php",
  "inc/lib/AjaxForm.php",
  "inc/lib/init.php",
  "inc/lib/assets.php",
  "inc/lib/helpers.php",
  "inc/lib/clean.php",
  "inc/lib/shortcodes.php",
];

foreach ($tofino_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'tofino'), $file), E_USER_ERROR);
  }

  if (!class_exists('acf') && $GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    wp_die(missing_acf_plugin_notice(), __('An error occured.', 'tofino'));
  }

  if (class_exists('acf')) {
    require_once $filepath;
  }
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
    add_action('admin_notices', composer_error_notice());
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


function add_theme_css_to_map_app() {
  if (class_exists('DCI_WebApp_Map')) {
    $DCI_WebApp_Map = new DCI_WebApp_Map();

    echo '<link rel="stylesheet" type="text/css" href="' . $DCI_WebApp_Map->get_main_asset('css', 'js/app.ts', get_stylesheet_directory(). '/dist/.vite', get_stylesheet_directory_uri() . '/dist/') . '">';
    echo '<script type="module" src="' . $DCI_WebApp_Map->get_main_asset('file', 'js/app.ts', get_stylesheet_directory(). '/dist/.vite', get_stylesheet_directory_uri() . '/dist/') . '"></script>';
  }
}
add_action('dci_map_app_head', 'add_theme_css_to_map_app');


function add_theme_css_to_company_cluster_app() {
  if (class_exists('DCI_Company_Cluster')) {
    $DCI_Company_Cluster = new DCI_Company_Cluster();

    echo '<link rel="stylesheet" type="text/css" href="' . $DCI_Company_Cluster->get_main_asset('css', 'js/app.ts', get_stylesheet_directory(). '/dist/.vite', get_stylesheet_directory_uri() . '/dist/') . '">';
    echo '<script type="module" src="' . $DCI_Company_Cluster->get_main_asset('file', 'js/app.ts', get_stylesheet_directory(). '/dist/.vite', get_stylesheet_directory_uri() . '/dist/') . '"></script>';
  }
}
add_action('dci_company_cluster_app_head', 'add_theme_css_to_company_cluster_app');


// Increase post limit to 1000
add_filter('graphql_connection_max_query_amount', function($amount, $source, $args, $context, $info) {
  return 2000;
}, 12, 5);
