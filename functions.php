<?php


$theme_config = [
  'svg' => [
    'sprite_file' => get_template_directory_uri() . '/dist/svg/sprite.symbol.svg'
  ]
];


/**
 * Tofino includes
 *
 * Library includes are now defined in the composer.json and autloaded.
 * Use 'composer dump-autoload -o' after adding new files.
 *
 */
if (file_exists(get_template_directory() . '/vendor/autoload.php')) { //Check composer autoload file exists. Result is cached by PHP.
  require_once 'vendor/autoload.php';
} else {
  if (is_admin()) {
    add_action('admin_notices', 'composer_error_notice');
  } else {
    wp_die(composer_error_notice(), 'An error occured.');
  }
}

//Check for missing dist directory. Result is cached by PHP.
if (!is_dir(get_template_directory() . '/dist')) {
  if (is_admin()) {
    add_action('admin_notices', 'missing_dist_error_notice');
  } else {
    wp_die(missing_dist_error_notice(), 'An error occured.');
  }
}

//Admin notice for missing composer autoload.
function composer_error_notice() {
  ?>
  <div class="error notice">
    <p><?php _e('Composer autoload file not found. Run composer install on the command line.', 'tofino'); ?></p>
  </div><?php
}

//Admin notice for missing dist directory.
function missing_dist_error_notice() {
  ?>
  <div class="error notice">
    <p><?php _e('/dist directory not found. You probably want to run npm install and gulp on the command line.', 'tofino'); ?></p>
  </div><?php
}

//TODO: Move this function out of functions.php
function menu_position() {
  $position = ot_get_option('menu_position_select');
  switch ($position) {
    case 'left':
      $class = '';
      break;
    case 'center':
      $class = 'menu-center';
      break;
    case 'right':
      $class = 'menu-right';
      break;
    default:
      $class = null;
  }
  return $class;
}

//TODO: Move this function out of functions.php
function menu_fixed() {
  $is_disabled = ot_get_option('menu_fixed_checkbox');
  if (!$is_disabled) {
    $class = 'navbar-fixed-top';
  } else {
    $class = null;
  }
  return $class;
}

/**
 * Helper function to get the pagename.
 * @todo: Move out of functions.php
 */
function get_page_name($page_id = null) {
  global $pagename;
  if (! $pagename) { //Not found in the query_var. Permalinks is probably not enabled.
    global $wp_query;
    $page_id  = ($page_id ? $page_id : get_the_ID());
    $post     = $wp_query->get_queried_object();
    $pagename = $post->post_name;
  }
  return $pagename;
}

//This adds menu-sticky and/or the footer sticky classes to the body.
function add_body_class($classes) {
  //Menu Sticky
  $menu_sticky_disabled = ot_get_option('menu_fixed_checkbox');
  if (!$menu_sticky_disabled) {
    $classes[] = 'menu-fixed';
  }

  //Footer Sticky
  $footer_sticky_enabled = ot_get_option('footer_sticky_checkbox');
  if ($footer_sticky_enabled) {
    $classes[] = 'footer-sticky';
  }

  //Post name
  global $post;
  if (isset($post)) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  return $classes;
}

add_filter('body_class', 'add_body_class');
