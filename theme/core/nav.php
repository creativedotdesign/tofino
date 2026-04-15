<?php

/**
 * Navigation menu registration and behavior.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Nav;


/**
 * Register the theme's navigation menu locations.
 *
 * @return void
 */
function register_menus(): void
{
  register_nav_menus([
    'header_navigation' => __('Header Navigation', 'tofino'),
    'footer_navigation' => __('Footer Navigation', 'tofino')
  ]);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\register_menus');


/**
 * Return the sticky-menu CSS class when the theme option is enabled.
 *
 * @return string|null Sticky class when enabled, otherwise null.
 */
function menu_sticky(): ?string
{
  if (get_field('sticky_menu', 'option') == 1) {
    return 'sticky-top';
  }

  return null;
}


/**
 * Append the `menu-fixed` class to the body when the sticky-menu option is on.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function add_menu_sticky_class(array $classes): array
{
  if (get_field('sticky_menu', 'option') == 1) {
    $classes[] = 'menu-fixed';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_menu_sticky_class');


/**
 * Normalize menu item CSS classes to a constrained set.
 *
 * @param array    $classes Existing menu item classes.
 * @param \WP_Post $item    Menu item object.
 * @return array Filtered menu item classes.
 */
function clean_nav_classes(array $classes, \WP_Post $item): array
{
  $new_classes = ['menu-item'];

  if ($item->current) {
    $new_classes[] = 'menu-item-current';
  }

  if (in_array('menu-item-has-children', $classes)) {
    $new_classes[] = 'menu-item-has-children';
  }

  if ($item->menu_item_parent == 0) {
    $new_classes[] = 'menu-item-top-level';
  }

  if ($item->menu_item_parent == 0 && in_array('current-menu-parent', $classes)) {
    $new_classes[] = 'menu-item-current-parent';
  }

  $custom_classes = get_post_meta($item->ID, '_menu_item_classes', true);

  if (!empty(array_filter($custom_classes))) {
    return array_merge($new_classes, $custom_classes);
  }

  return $new_classes;
}
add_filter('nav_menu_css_class', __NAMESPACE__ . '\\clean_nav_classes', 10, 2);
