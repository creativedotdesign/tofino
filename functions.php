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
require_once 'vendor/autoload.php';

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

//This added the menu-sticky and/or the footer sticky classes to the body.
function add_body_class( $classes ) {
  //Menu Sticky
  $menu_sticky_disabled = ot_get_option( 'menu_sticky_checkbox' );
  if ( !$menu_sticky_disabled ) {
    $classes[] = 'menu-sticky';
  }

  //Footer Sticky
  $footer_sticky_enabled = ot_get_option( 'footer_sticky_checkbox' );
  if ( $footer_sticky_enabled ) {
    $classes[] = 'footer-sticky';
  }
  return $classes;
}

add_filter( 'body_class', 'add_body_class' );
