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
