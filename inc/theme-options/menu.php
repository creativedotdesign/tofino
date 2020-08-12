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
}
add_action('customize_register', __NAMESPACE__ . '\\menu_settings');


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
    return 'sticky-top';
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
