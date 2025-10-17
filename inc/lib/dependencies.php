<?php

/**
 * Theme dependencies functions
 *
 * @package Tofino
 * @since 4.3.0
 */

namespace Tofino\Dependencies;


/**
 * Composer dependencies
 *
 * External dependencies are defined in the composer.json and autoloaded.
 * Use 'composer dump-autoload -o' after adding new files.
 *
 */
if (file_exists(get_template_directory() . '/vendor/autoload.php')) { // Check composer autoload file exists. Result is cached by PHP.
  require_once get_template_directory() . '/vendor/autoload.php';
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
    add_action('admin_notices', __NAMESPACE__ . '\\missing_dist_error_notice');
  } else {
    wp_die(missing_dist_error_notice(), __('An error occured.', 'tofino'));
  }
}


// Check for ACF Plugin.
if (!class_exists('acf')) {
  if (is_admin()) {
    add_action('admin_notices', __NAMESPACE__ . '\\missing_acf_plugin_notice');
  } elseif ($GLOBALS['pagenow'] != 'wp-login.php') {
    wp_die(missing_acf_plugin_notice(), __('An error occured.', 'tofino'));
  }
}


// Check for ACF Extended Plugin.
function check_acf_extended_plugin() 
{
  if (!is_plugin_active('acf-extended/acf-extended.php')) {
    add_action('admin_notices', __NAMESPACE__ . '\\missing_acf_extended_plugin_notice');
  }
}
add_action('admin_init', __NAMESPACE__ . '\\check_acf_extended_plugin');


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


// Admin notice for missing ACF Extended plugin.
function missing_acf_extended_plugin_notice()
{
  echo '<div class="error notice"><p><strong>' . __('Missing Plugin', 'tofino') . '</strong> - ' . __('Advanced Custom Fields: Extended plugin not found. Please install it.', 'tofino') . '</p></div>';
}
