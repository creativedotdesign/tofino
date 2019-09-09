<?php
/**
 * Load CSS and JS files
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Assets;

/**
 * Load styles
 *
 * Register and enqueue the main stylesheet.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 * Called using call_css() function.
 *
 * @see call_css()
 * @since 1.0.0
 * @return void
 */
function styles() {
  $main_css = mix('css/styles.css', 'dist');
  wp_register_style('tofino/css', $main_css);
  wp_enqueue_style('tofino/css');
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\styles');


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
function admin_styles() {
  $admin_css = mix('css/wp-admin.css', 'dist');
  wp_register_style('tofino/css/admin', $admin_css);
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
function main_script() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    $main_js = mix('js/scripts.js', 'dist');

    wp_register_script('tofino/js', $main_js, [], null, true);
    wp_enqueue_script('tofino/js');
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\main_script');


/**
 * Head JS script
 *
 * Register and enqueue the head js used in front end.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 *
 * @since 1.1.0
 * @return void
 */
function head_script() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    $head_js = mix('js/head-scripts.js', 'dist');
    wp_register_script('tofino/js/head', $head_js);
    wp_enqueue_script('tofino/js/head');
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\head_script');


/**
 * Localize script
 *
 * Make data available to JS scripts via global JS variables.
 *
 * @link https://codex.wordpress.org/Function_Reference/wp_localize_script
 * @since 1.1.0
 * @return void
 */
function localize_scripts() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    wp_localize_script('tofino/js', 'tofinoJS', [
      'ajaxUrl'        => admin_url('admin-ajax.php'),
      'nextNonce'      => wp_create_nonce('next_nonce'),
      'cookieExpires'  => (get_theme_mod('notification_expires') ? get_theme_mod('notification_expires'): 999),
      'themeUrl'       => get_template_directory_uri(),
      'notificationJS' => (get_theme_mod('notification_use_js') ? 'true' : 'false')
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
function admin_scripts() {
  $admin_js = mix('dist/js/wp-admin.js', './');
  wp_register_script('tofino/js/admin', $admin_js);
  wp_enqueue_script('tofino/js/admin');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts');
