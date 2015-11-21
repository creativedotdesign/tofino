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
  if (ot_get_option('maintenance_mode_enabled')) {
    echo '<div class="maintenance-mode-alert"><h1>' . __('Maintenance Mode', 'tofino') . '</h1><p>' . ot_get_option('maintenance_mode_text') . '</p><button>' . __('I understand', 'tofino') . '</button></div>';
    // @todo - Move this to a separate admin js file
    echo '<script>jQuery(\'.maintenance-mode-alert button\').on(\'click\', function () {jQuery(\'.maintenance-mode-alert\').fadeOut(\'fast\');});</script>';
  }
}

add_action('admin_footer', __NAMESPACE__ . '\\show_maintenance_message');


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
 * Register sidebars
 */
function widgets_init() {
  register_sidebar([
    'name'          => __('Above Content', 'tofino'),
    'id'            => 'sidebar-above-content',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>'
  ]);
  register_sidebar([
    'name'          => __('Below Content', 'tofino'),
    'id'            => 'sidebar-below-content',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>'
  ]);
}

add_action('widgets_init', __NAMESPACE__ . '\\widgets_init');


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
