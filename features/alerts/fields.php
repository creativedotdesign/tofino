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

acf_add_local_field_group([
  'key' => 'group_653fde8f6b970',
  'title' => 'Alerts',
  'fields' => [
    [
      'key' => 'field_653fde8fdf14f',
      'label' => 'Alerts',
      'name' => 'alerts',
      'type' => 'repeater',
      'layout' => 'row',
      'min' => 0,
      'button_label' => 'Add Alert',
      'show_in_graphql' => 1,
      'graphql_field_name' => 'alerts',
      'sub_fields' => [
        [
          'key' => 'field_653fdeacdf150',
          'label' => 'Enabled',
          'name' => 'enabled',
          'type' => 'true_false',
          'default_value' => 0,
          'ui' => 1,
        ],
        [
          'key' => 'field_653fded0df151',
          'label' => 'Position',
          'name' => 'position',
          'type' => 'select',
          'instructions' => 'Alert position. Bottom = Fixed over footer. Top = Fixed above top menu.',
          'choices' => ['Top' => 'Top', 'Bottom' => 'Bottom'],
        ],
        [
          'key' => 'field_653fdf15df152',
          'label' => 'Expires',
          'name' => 'expires',
          'type' => 'number',
          'instructions' => 'Number of days until the alert expires. Set via a cookie.',
          'default_value' => 999,
          'max' => 999,
        ],
        [
          'key' => 'field_653fdf4adf153',
          'label' => 'Message',
          'name' => 'message',
          'type' => 'wysiwyg',
          'default_value' => '<p>This is a <a href="https://google.ca">test alert</a>.</p>',
          'toolbar' => 'basic',
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
  'style' => 'seamless',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'active' => true,
]);
