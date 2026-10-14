<?php

/**
 * Alerts — ACF local field group registration.
 *
 * @package Tofino
 * @since 5.0.0
 */

if (function_exists('acf_add_options_sub_page')) {
  acf_add_options_sub_page([
    'page_title' => 'Alerts',
    'menu_title' => 'Alerts',
    'menu_slug' => 'alerts',
    'parent_slug' => 'general-options',
    'capability' => 'edit_posts',
    'autoload' => false,
  ]);
}

if (!function_exists('acf_add_local_field_group')) {
  return;
}

add_filter('acf/fields/wysiwyg/toolbars', static function (array $toolbars): array {
  $toolbars['Alerts'] = [
    1 => ['bold', 'italic', 'link', 'unlink', 'alignleft', 'aligncenter', 'alignright', 'removeformat'],
  ];

  return $toolbars;
});

acf_add_local_field_group([
  'key' => 'group_653fde8f6b970',
  'title' => 'Alerts',
  'fields' => [
    [
      'key' => 'field_653fde8fdf14f',
      'label' => 'Alerts',
      'name' => 'alerts',
      'type' => 'repeater',
      // Block layout: the short controls pair up two per line (50% wrappers
      // below) and instructions run full width beneath their labels, so each
      // alert stays compact instead of one table row per sub-field.
      'layout' => 'block',
      'min' => 0,
      'max' => 2,
      'instructions' => 'Up to two alerts. The first uses the Alert 1 brand colour, the second uses the darker Alert 2 colour.',
      'button_label' => 'Add Alert',
      'sub_fields' => [
        [
          'key' => 'field_653fdeacdf150',
          'label' => 'Enabled',
          'name' => 'enabled',
          'type' => 'true_false',
          'default_value' => 0,
          'ui' => 1,
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_653fded0df151',
          'label' => 'Position',
          'name' => 'position',
          'type' => 'select',
          'instructions' => 'Top sits above the menu; Bottom is fixed over the footer.',
          'choices' => ['Top' => 'Top', 'Bottom' => 'Bottom'],
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_653fdf15df152',
          'label' => 'Expires',
          'name' => 'expires',
          'type' => 'number',
          'instructions' => 'Days before a dismissed alert reappears.',
          'default_value' => 999,
          'max' => 999,
          'wrapper' => ['width' => '50'],
        ],
        [
          'key' => 'field_653fdf4adf153',
          'label' => 'Message',
          'name' => 'message',
          'type' => 'wysiwyg',
          'instructions' => '[maxchars:180]',
          'default_value' => '<p>This is a <a href="https://google.ca">test alert</a>.</p>',
          'toolbar' => 'alerts',
          'media_upload' => 0,
        ],
        [
          'key' => 'field_68f26a5ae6bf3',
          'label' => 'Hide Alert on Specific Pages',
          'name' => 'hide_alert_on_specific_pages',
          'type' => 'relationship',
          'post_type' => ['page'],
          'filters' => ['search', 'post_type', 'taxonomy'],
          'return_format' => 'id',
        ],
      ],
    ],
  ],
  'location' => [
    [['param' => 'options_page', 'operator' => '==', 'value' => 'alerts']],
  ],
  'style' => 'default',
  // Label on top: a left label column only holds the word "Alerts" and would
  // take a fifth of the width away from the repeater rows.
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'active' => true,
]);
