<?php

namespace Tofino\Init;

/**
 * Theme setup
 */
function setup() {
  // Enable plugins to manage the document title
  add_theme_support('title-tag');

  // Enable featured images for Posts
  add_theme_support('post-thumbnails', array('post'));

  // Register wp_nav_menu() menus
  register_nav_menus([
    'primary_navigation' => __('Primary Navigation', 'tofino')
  ]);

  // Allow editor role to edit theme options.
  get_role('editor')->add_cap('edit_theme_options');

  // Load language file
  load_theme_textdomain('tofino', get_template_directory() . '/languages');
}

add_action('after_setup_theme', __NAMESPACE__ . '\\setup');


/**
 * Show maintenance mode message
 */
function show_maintenance_message() {
  if (ot_get_option('maintenance_mode_enabled')) { ?>
    <div class="error notice">
      <p><strong><?php echo __('Maintenance Mode', 'tofino') . '</strong> - ' . ot_get_option('maintenance_mode_text'); ?></p>
    </div><?php

    if (!isset($_COOKIE['tofino_maintenance_alert_dismissed'])) {
      echo '<div class="maintenance-mode-alert"><h1>' . __('Maintenance Mode', 'tofino') . '</h1><p>' . ot_get_option('maintenance_mode_text') . '</p><button>' . __('I understand', 'tofino') . '</button></div>';
    }
  }
}
add_action('admin_notices', __NAMESPACE__ . '\\show_maintenance_message');


/**
 * Set max content width
 */
function content_width() {
  $GLOBALS['content_width'] = apply_filters(__NAMESPACE__ . '\\content_width', 640);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\content_width', 0);


/**
 * Remove admin bar
**/
add_filter('show_admin_bar', '__return_false');


/**
 * Add post_name to body class
 */
function add_post_name_body_class($classes) {
  global $post;
  if (isset($post)) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_post_name_body_class');
