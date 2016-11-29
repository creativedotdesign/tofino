<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\Menu;

/**
 * Menu settings
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function menu_settings($wp_customize) {
  $wp_customize->add_section('tofino_menu_settings', [
    'title'    => __('Menu Options', 'tofino'),
    'priority' => 100
  ]);

  $wp_customize->add_setting('menu_sticky', [
    'default'           => 'disabled',
    'sanitize_callback' => '\Tofino\Helpers\sanitize_choices',
  ]);

  $wp_customize->add_control('menu_sticky', [
    'label'       => __('Sticky Menu', 'tofino'),
    'description' => '',
    'section'     => 'tofino_menu_settings',
    'type'        => 'select',
    'choices'     => [
      'enabled'  => __('Enabled', 'tofino'),
      'disabled' => __('Disabled', 'tofino')
    ]
  ]);

  $wp_customize->add_setting('menu_position', [
    'default'           => 'center',
    'sanitize_callback' => '\Tofino\Helpers\sanitize_choices',
  ]);

  $wp_customize->add_control('menu_position', [
    'label'       => __('Menu Position', 'tofino'),
    'description' => '',
    'section'     => 'tofino_menu_settings',
    'type'        => 'select',
    'choices'     => [
      'left'   => __('Left', 'tofino'),
      'center' => __('Center', 'tofino'),
      'right'  => __('Right', 'tofino')
    ]
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\menu_settings');


/**
 * Menu position
 *
 * Returns menu position classes based on theme option setting.
 *
 * @since 1.0.0
 * @return void
 */
function menu_position() {
  $position = get_theme_mod('menu_position');
  switch ($position) {
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


/**
 * Menu Sticky
 *
 * Returns menu sticky class based on theme option setting.
 *
 * @since 1.0.0
 * @return void
 */
function menu_sticky() {
  if (get_theme_mod('menu_sticky') === 'enabled') {
    return 'navbar-sticky-top';
  }
}


/**
 * Add theme options to body class
 *
 * Adds the menu-sticky classes to the body.
 *
 * @since 1.0.0
 * @param array $classes Array of classes passed to the body tag by WP.
 * @return void
 */
function add_menu_sticky_class($classes) {
  if (get_theme_mod('menu_sticky') === 'enabled') {
    $classes[] = 'menu-fixed';
  }
  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_menu_sticky_class');
