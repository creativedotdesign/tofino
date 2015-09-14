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

}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup');

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
