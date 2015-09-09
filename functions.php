<?php
/*
 *  Custom functions, support, custom post types and more.
 */

/*------------------------------------*\
    External Modules/Files
\*------------------------------------*/

/**
 * Composer Autoload classes
 */
require 'vendor/autoload.php';

/**
 * ACF Filters
*/

//Update ACF settings path
add_filter('acf/settings/path', 'eb_acf_settings_path');

function eb_acf_settings_path( $path ) {
  $path = get_stylesheet_directory() . "/vendor/advanced-custom-fields/advanced-custom-fields-pro/";
  return $path;
}

//Update ACF settings dir
add_filter('acf/settings/dir', 'eb_acf_settings_dir');

function eb_acf_settings_dir( $dir ) {
  $dir = get_stylesheet_directory_uri() . "/vendor/advanced-custom-fields/advanced-custom-fields-pro/";
  return $dir;
}
