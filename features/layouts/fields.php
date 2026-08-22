<?php

/**
 * Layouts — ACF local field groups for custom layout definitions.
 *
 * Includes both the Layouts options-page group and the Page Template
 * selector shown on draft pages.
 *
 * @package Tofino
 * @since 5.0.0
 */

if (function_exists('acf_add_options_sub_page')) {
  acf_add_options_sub_page([
    'page_title' => 'Layouts',
    'menu_title' => 'Layouts',
    'menu_slug' => 'layouts',
    'parent_slug' => 'general-options',
    'capability' => 'manage_options',
    'autoload' => false,
  ]);
}

if (!function_exists('acf_add_local_field_group')) {
  return;
}

// Layouts options page
acf_add_local_field_group([
  'key' => 'group_66e8b46d1a2b6',
  'title' => 'Layouts',
  'fields' => [
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
      'key' => 'field_66e9ebadf559b',
      'label' => 'Custom Layouts',
      'name' => 'custom_layouts',
      'type' => 'true_false',
      'message' => 'Experimental, use at your own risk.',
      'default_value' => 0,
      'ui' => 1,
    ],
    [
      'key' => 'field_66e8b46db4145',
      'label' => 'Layout',
      'name' => 'layout',
      'type' => 'repeater',
      'layout' => 'block',
      'button_label' => 'Add Row',
      'collapsed' => 'field_66e8b484b4146',
      'conditional_logic' => [
        [['field' => 'field_66e9ebadf559b', 'operator' => '==', 'value' => '1']],
      ],
      'sub_fields' => [
        [
          'key' => 'field_66e8b484b4146',
          'label' => 'Name',
          'name' => 'name',
          'type' => 'text',
        ],
        [
          'key' => 'field_66e8b4afb4147',
          'label' => 'Modules',
          'name' => 'modules',
          'type' => 'repeater',
          'layout' => 'table',
          'button_label' => 'Add Row',
          'sub_fields' => [
            [
              'key' => 'field_66e8b4b8b4148',
              'label' => 'Module Name',
              'name' => 'module_name',
              'type' => 'select',
              'choices' => ['' => 'Select'],
            ],
          ],
        ],
      ],
    ],
  ],
  'location' => [
    [['param' => 'options_page', 'operator' => '==', 'value' => 'layouts']],
  ],
  'style' => 'default',
  'label_placement' => 'left',
  'instruction_placement' => 'label',
  'active' => true,
]);

// Page Template selector shown on draft pages
acf_add_local_field_group([
  'key' => 'group_66e8b2aebe427',
  'title' => 'Page Template',
  'fields' => [
    [
      'key' => 'field_66e8b2afb1015',
      'label' => 'Select Page Template',
      'name' => 'page_template',
      'type' => 'select',
      'wrapper' => ['width' => '25'],
      'choices' => ['' => 'Custom'],
      'default_value' => 'custom',
    ],
    [
      'key' => 'field_66e8b39fc2148',
      'label' => 'Update Layout',
      'name' => 'update_layout',
      'type' => 'acfe_button',
      'wrapper' => ['width' => '25'],
      'button_value' => 'Update Layout',
      'button_type' => 'button',
      'button_class' => 'button button-secondary',
      'conditional_logic' => [
        [['field' => 'field_66e8b2afb1015', 'operator' => '!=', 'value' => '']],
      ],
    ],
  ],
  'location' => [
    [
      ['param' => 'post_type', 'operator' => '==', 'value' => 'page'],
      ['param' => 'post_status', 'operator' => '==', 'value' => 'draft'],
    ],
  ],
  'position' => 'acf_after_title',
  'style' => 'seamless',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'hide_on_screen' => ['the_content'],
  'active' => true,
]);
