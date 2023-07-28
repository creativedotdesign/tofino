<?php

/**
 * Load CSS and JS files
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Assets;

/**
 * Load admin styles
 *
 * Register and enqueue the stylesheet used in the admin area.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 * Function added to both the login_head (Login page) and admin_head (Admin pages)
 *
 * @since 1.0.0
 * @return void
 */
function admin_styles()
{
  wp_register_style('tofino/css/admin', get_stylesheet_directory_uri() . '/dist/admin.css', [], false);
  wp_enqueue_style('tofino/css/admin');
}
add_action('login_head', __NAMESPACE__ . '\\admin_styles');
add_action('admin_head', __NAMESPACE__ . '\\admin_styles');


/**
 * Main JS script
 *
 * Register and enqueue the mains js used in front end.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 *
 * @since 1.1.0
 * @return void
 */
function main_script()
{
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    \Tofino\Vite::useVite();
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\main_script');


/**
 * Localize script
 *
 * Make data available to JS scripts via global JS variables.
 *
 * @link https://codex.wordpress.org/Function_Reference/wp_localize_script
 * @since 1.1.0
 * @return void
 */
function localize_scripts()
{
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    $alert = get_field('alert', 'general-options');

    wp_localize_script('tofino', 'tofinoJS', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nextNonce' => wp_create_nonce('next_nonce'),
      'cookieExpires' => $alert['expires'] ? $alert['expires'] : 999,
      'themeUrl' => get_template_directory_uri(),
      'alertJS' => $alert['display_with_javascript'] ? 'true' : 'false',
      'siteURL' => site_url(),
    ]);
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\localize_scripts');


/**
 * Load admin scripts
 *
 * Register and enqueue the scripts used in the admin area.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 *
 * @since 1.0.0
 * @return void
 */
function admin_scripts()
{
  wp_register_script('tofino/js/admin',  get_stylesheet_directory_uri() . '/dist/admin.js', [], null);
  wp_enqueue_script('tofino/js/admin');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts');


/**
 * Correct Image Sizes
 *
 * Set the images sizes to ones we really use.
 *
 * @since 3.2.0
 * @return void
 */
function correct_image_sizes()
{
  remove_image_size('medium_large');
  remove_image_size('large');
  remove_image_size('1536x1536');

  update_option('thumbnail_size_h', 0);
  update_option('thumbnail_size_w', 250);

  update_option('medium_size_h', 0);
  update_option('medium_size_w', 565);

  update_option('medium_large_size_h', 0);
  update_option('medium_large_size_w', 0);

  update_option('large_size_h', 0);
  update_option('large_size_w', 1152);

  update_option('1536x1536_size_h', 0);
  update_option('1536x1536_size_w', 0);

  update_option('2048x2048_size_h', 0);
  update_option('2048x2048_size_w', 2048);
}
add_action('init', __NAMESPACE__ . '\\correct_image_sizes');
