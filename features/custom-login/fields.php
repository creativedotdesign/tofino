<?php

/**
 * Custom Login — ACF local field group registration.
 *
 * @package Tofino
 * @since 5.0.0
 */

if (function_exists('acf_add_options_sub_page')) {
  acf_add_options_sub_page([
    'page_title' => 'Login',
    'menu_title' => 'Login',
    'menu_slug' => 'custom-login',
    'parent_slug' => 'general-options',
    'capability' => 'manage_options',
    'autoload' => false,
  ]);
}

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_custom_login_screen',
  'title' => 'Login',
  'fields' => [
    [
      'key' => 'field_671a51c202047',
      'label' => 'Custom Login Screen',
      'name' => 'custom_login_screen',
      'type' => 'true_false',
      'default_value' => 0,
      'ui' => 1,
    ],
    [
      'key' => 'field_671a51ef02048',
      'label' => 'Login Screen',
      'name' => 'login_screen',
      'type' => 'group',
      'conditional_logic' => [
        [['field' => 'field_671a51c202047', 'operator' => '==', 'value' => '1']],
      ],
      'layout' => 'block',
      'sub_fields' => [
        [
          'key' => 'field_62582ee46d923',
          'label' => 'Logo',
          'name' => 'logo',
          'type' => 'image',
          'instructions' => 'You might need to add some additional CSS to tweak the logo size or position.',
          'return_format' => 'id',
          'preview_size' => 'medium',
        ],
        [
          'key' => 'field_671a7ca0e9739',
          'label' => 'Logo Max Height',
          'name' => 'logo_max_height',
          'type' => 'number',
          'default_value' => 80,
          'append' => 'px',
          'conditional_logic' => [
            [['field' => 'field_62582ee46d923', 'operator' => '!=empty']],
          ],
        ],
        [
          'key' => 'field_671a522a02049',
          'label' => 'Text',
          'name' => 'text',
          'type' => 'text',
          'default_value' => 'Admin CMS',
        ],
        [
          'key' => 'field_671a52370204a',
          'label' => 'Button Color',
          'name' => 'button_color',
          'type' => 'color_picker',
          'default_value' => '#000000',
        ],
      ],
    ],
  ],
  'location' => [
    [['param' => 'options_page', 'operator' => '==', 'value' => 'custom-login']],
  ],
  'style' => 'default',
  'label_placement' => 'left',
  'instruction_placement' => 'label',
  'active' => true,
]);
