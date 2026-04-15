<?php

/**
 * Admin options — ACF local field group registration.
 *
 * Organized into tabs: General, Menu, Dashboard, Developer.
 *
 * @package Tofino
 * @since 5.0.0
 */

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
    // ── General tab ──
    [
      'key' => 'field_tab_general',
      'label' => 'General',
      'type' => 'tab',
    ],
    [
      'key' => 'field_62582eb26d922',
      'label' => 'Admin Bar',
      'name' => 'admin_bar',
      'type' => 'true_false',
      'instructions' => 'Show the admin bar on the front-end.',
      'default_value' => 0,
      'ui' => 1,
    ],
    [
      'key' => 'field_671c0c73e5f41',
      'label' => 'iFrame Resizer License Key',
      'name' => 'iframe_resizer_license_key',
      'type' => 'text',
      'instructions' => 'See https://iframe-resizer.com/ for more details.',
      'default_value' => 'GPLv3',
    ],

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
    // ── Dashboard tab ──
    [
      'key' => 'field_tab_dashboard',
      'label' => 'Dashboard',
      'type' => 'tab',
    ],
    [
      'key' => 'field_62583945a5b17',
      'label' => 'Dashboard Widget',
      'name' => 'dashboard_widget',
      'type' => 'group',
      'layout' => 'block',
      'sub_fields' => [
        [
          'key' => 'field_62586874293b0',
          'label' => 'Enabled',
          'name' => 'enabled',
          'type' => 'true_false',
          'default_value' => 0,
          'ui' => 1,
        ],
        [
          'key' => 'field_6258394fa5b18',
          'label' => 'Title',
          'name' => 'title',
          'type' => 'text',
          'default_value' => 'Website Support',
        ],
        [
          'key' => 'field_62583958a5b19',
          'label' => 'Text',
          'name' => 'text',
          'type' => 'wysiwyg',
          'default_value' => '<a href="https://github.com/creativedotdesign/tofino">Tofino</a> theme by <a href="https://creativedotdesign.com/">Creative Dot</a>.',
          'toolbar' => 'basic',
          'media_upload' => 0,
        ],
      ],
    ],

    // ── Developer tab ──
    [
      'key' => 'field_tab_developer',
      'label' => 'Developer',
      'type' => 'tab',
    ],
    [
      'key' => 'field_66ead4240eb46',
      'label' => 'Show ACF Admin',
      'name' => 'show_acf_admin',
      'type' => 'true_false',
      'instructions' => 'Turn on access to ACF in the admin area.',
      'default_value' => 0,
      'ui' => 1,
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
    [
      'key' => 'field_66e86caf93f4e',
      'label' => 'Hide Preview Button',
      'name' => 'hide_preview_button',
      'type' => 'true_false',
      'instructions' => 'Hide the preview button in the editor.',
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
