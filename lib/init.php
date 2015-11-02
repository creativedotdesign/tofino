<?php

namespace Tofino\Init;

/**
 * Theme setup
 */
function setup() {

  // Enable plugins to manage the document title
  add_theme_support('title-tag');

  // Register wp_nav_menu() menus
  register_nav_menus([
    'primary_navigation' => __('Primary Navigation', 'tofino')
  ]);

  //Allow editor role to edit theme options.
  get_role('editor')->add_cap('edit_theme_options');

}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup');

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
  //Post name
  global $post;
  if (isset($post)) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  return $classes;
}

add_filter('body_class', __NAMESPACE__ . '\\add_post_name_body_class');
