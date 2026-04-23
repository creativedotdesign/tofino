<?php

/**
 * Admin options — ACF local field group registration.
 *
 * Organized into tabs: General, Menu, Developer.
 *
 * @package Tofino
 * @since 5.0.0
 */

add_action('acf/init', function () {
  if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
      'page_title' => 'Options',
      'menu_title' => 'Options',
      'menu_slug' => 'general-options',
      'capability' => 'edit_posts',
      'icon_url' => 'dashicons-admin-generic',
      'redirect' => false,
      'autoload' => false,
      'update_button' => 'Update',
      'updated_message' => 'Options Updated',
    ]);
  }

  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_65a167568ac34',
    'title' => 'Admin',
    'fields' => [
      // ── Menu tab ──
      [
        'key' => 'field_tab_menu',
        'label' => 'Menu',
        'type' => 'tab',
      ],
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
      // ── Developer tab ──
      [
        'key' => 'field_tab_developer',
        'label' => 'Developer',
        'type' => 'tab',
      ],
      [
        'key' => 'field_67f7f9e7e7d01',
        'label' => 'Show Module Names',
        'name' => 'show_module_names',
        'type' => 'true_false',
        'instructions' => 'Display the current module name label on the front-end for editors.',
        'default_value' => 0,
        'ui' => 1,
      ],
    ],
    'location' => [
      [['param' => 'options_page', 'operator' => '==', 'value' => 'general-options']],
    ],
    'style' => 'seamless',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
  ]);
});
