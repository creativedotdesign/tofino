<?php
/**
 *
 * Initialize and setup theme
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Init;

/**
 * PHP version check
 *
 * @since 1.5.0
 * @return void
 */
function php_version_check() {
  $php_version = phpversion();
  if (version_compare($php_version, '5.5.9', '<')) {
    wp_die('<div class="error notice"><p>' . __('PHP version >= 5.5.9 is required for this theme to work correctly.', 'tofino') . '</p></div>', 'An error occured.');
  }
}
add_action('after_setup_theme', __NAMESPACE__ . '\\php_version_check');


/**
 * Theme setup
 *
 * @since 1.0.0
 * @return void
 */
function setup() {
  add_theme_support('title-tag'); // Enable plugins to manage the document title
  add_theme_support('post-thumbnails'); // Enable featured images for Posts

  // Register wp_nav_menu() menus
  register_nav_menus([
    'primary_navigation' => __('Primary Navigation', 'tofino')
  ]);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup');


/**
 * Check page display settings
 *
 * Checks if the page display is set to "Latest Posts" and if the correct template
 * file exists and displays an error if missing the home.php.
 *
 * @since 1.2.0
 * @return void
 */
function check_page_display() {
  if ((!is_admin()) && (get_option('show_on_front') === 'posts') && (locate_template('home.php') === '')) {
    wp_die('Front page display setting is set to Latest Posts but no home.php file exists. Please update the settings selecting a Static page or create the home.php as per the documentation.', 'An error occured.');
  }
}
add_action('after_setup_theme', __NAMESPACE__ . '\\check_page_display');


/**
 * Show maintenance mode message in admin area
 *
 * Check the maintenance_mode_enabled Theme Option. If enabled display a notice in
 * the admin area at the top of every screen. Also show a popup window to the user
 * and set a cookie.
 *
 * @since 1.0.0
 * @return void
 */
function show_maintenance_message() {
  if (get_theme_mod('maintenance_mode') === true) {?>
    <div class="error notice">
      <p><strong><?php echo __('Maintenance Mode', 'tofino') . '</strong> - ' . get_theme_mod('maintenance_mode_text', __('This site is currently in maintenance mode. Any changes you make may be overwritten or removed.', 'tofino')); ?></p>
    </div><?php

    if (!isset($_COOKIE['tofino_maintenance_alert_dismissed'])) {
      echo '<div class="maintenance-mode-alert"><h1>' . __('Maintenance Mode', 'tofino') . '</h1><p>' . get_theme_mod('maintenance_mode_text', __('This site is currently in maintenance mode. Any changes you make may be overwritten or removed.', 'tofino')) . '</p><button>' . __('I understand', 'tofino') . '</button></div>';
    }
  }
}
add_action('admin_notices', __NAMESPACE__ . '\\show_maintenance_message');


/**
 * Set max content width GLOBAL
 *
 * @since 1.0.0
 * @return void
 */
function content_width() {
  $GLOBALS['content_width'] = apply_filters(__NAMESPACE__ . '\\content_width', 640);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\content_width', 0);


/**
 * Remove admin bar
 */
add_filter('show_admin_bar', '__return_false');


/**
 * Add post_type and post_name to body class
 *
 * @since 1.0.0
 * @param array $classes array of current classes on the body tag
 * @return array updated to include the post_type and post_name
 */
function add_post_name_body_class($classes) {
  global $post;
  if (isset($post) && is_single()) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }

  if (get_theme_mod('no_fout')) {
    $classes[] = 'no-fout';
  }
  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_post_name_body_class');
