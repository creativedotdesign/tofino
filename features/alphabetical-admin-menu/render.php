<?php

/**
 * Alphabetical Admin Menu — keeps Dashboard first and sorts every other
 * visible top-level menu by its displayed label.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Features\AlphabeticalAdminMenu;

if (!defined('ABSPATH')) {
  exit;
}

if (!is_admin()) {
  return;
}

add_filter('custom_menu_order', '__return_true');

add_filter('menu_order', function (array $order): array {
  global $menu;

  $labels = array_column($menu, 0, 2);
  $dashboard = array_shift($order);

  $separators = array_values(array_filter(
    $order,
    static fn(string $slug): bool => str_starts_with($slug, 'separator')
  ));

  $order = array_values(array_filter(
    $order,
    static fn(string $slug): bool => !str_starts_with($slug, 'separator')
  ));

  usort(
    $order,
    static fn(string $a, string $b): int => strnatcasecmp(
      wp_strip_all_tags($labels[$a] ?? $a),
      wp_strip_all_tags($labels[$b] ?? $b)
    )
  );

  return [
    $dashboard,
    ...array_slice($separators, 0, 1),
    ...$order,
  ];
}, PHP_INT_MAX);
