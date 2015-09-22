<?php
/**
 * Tofino includes
 *
 * The $tofino_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 */
$tofino_includes = [
  'vendor/autoload.php',  // Composer Autoload classes
  'lib/assets.php',
  'lib/init.php',
  'lib/theme-options.php',
  'lib/required-plugins.php'
];
foreach ($tofino_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'tofino'), $file), E_USER_ERROR);
  }
  require_once $filepath;
}
unset($file, $filepath);


//TODO: Move this function out of functions.php
function menu_position() {
  $position = ot_get_option( 'menu_position_select' );
  switch ( $position ) {
    case 'left':
      $class = '';
      break;
    case 'center':
      $class = 'menu-center';
      break;
    case 'right';
      $class = 'menu-right';
      break;
    default:
      $class = null;
  }
  return $class;
}

//TODO: Move this function out of functions.php
function menu_sticky() {
  $is_disabled = ot_get_option( 'menu_sticky_checkbox' );
  if ( !$is_disabled ) {
    $class = 'navbar-fixed-top';
  } else {
    $class = null;
  }
  return $class;
}

function add_body_class( $classes ) {
  $is_disabled = ot_get_option( 'menu_sticky_checkbox' );
  if ( !$is_disabled ) {
    $classes[] = 'menu-sticky';
  }
  return $classes;
}

add_filter( 'body_class', 'add_body_class' );
