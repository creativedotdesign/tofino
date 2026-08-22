<?php

/**
 * Admin options — ACF local field group registration.
 *
 * Registers the Options parent and its Menu settings sub-page.
 *
 * @package Tofino
 * @since 5.0.0
 */

// Preserve bookmarks to the former field-bearing parent screen. ACF points
// the visible Options menu at its first accessible child, so this hidden page
// exists only to redirect the retired slug.
add_action('admin_menu', function (): void {
  $hook = add_submenu_page(
    null,
    'Options',
    'Options',
    'edit_posts',
    'general-options',
    static function (): void {}
  );

  if (!$hook) {
    return;
  }

  add_action('load-' . $hook, static function (): void {
      wp_safe_redirect(admin_url('admin.php?page=menu-options'));
      exit;
  });
});

add_action('acf/init', function () {
  if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
      'page_title' => 'Options',
      'menu_title' => 'Options',
      'menu_slug' => 'general-options',
      'capability' => 'edit_posts',
      'icon_url' => 'dashicons-admin-generic',
      'redirect' => true,
      'autoload' => false,
      'update_button' => 'Update',
      'updated_message' => 'Options Updated',
    ]);

    acf_add_options_sub_page([
      'page_title' => 'Menu',
      'menu_title' => 'Menu',
      'menu_slug' => 'menu-options',
      'parent_slug' => 'general-options',
      'capability' => 'edit_posts',
      'autoload' => false,
    ]);
  }

  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_65a167568ac34',
    'title' => 'Menu',
    'fields' => [
      [
        'key' => 'field_6258359c5a86d',
        'label' => 'Sticky Menu',
        'name' => 'sticky_menu',
        'type' => 'true_false',
        'default_value' => 0,
        'ui' => 1,
      ],
      [
        'key' => 'field_67b784bf56a4c',
        'label' => 'Menu Scroll Reveal',
        'name' => 'menu_scroll_reveal',
        'type' => 'true_false',
        'message' => 'Hide the sticky menu while scrolling down, reveal it when scrolling up.',
        'default_value' => 0,
        'ui' => 1,
      ],
    ],
    'location' => [
      [['param' => 'options_page', 'operator' => '==', 'value' => 'menu-options']],
    ],
    'style' => 'default',
    'label_placement' => 'left',
    'instruction_placement' => 'label',
    'active' => true,
  ]);
});
