<?php

/**
 * Footer — ACF local field group registration.
 *
 * @package Tofino
 * @since 5.0.0
 */

add_action('acf/init', function () {
  if (function_exists('acf_add_options_sub_page')) {
    acf_add_options_sub_page([
      'page_title' => 'Footer',
      'menu_title' => 'Footer',
      'menu_slug' => 'footer',
      'parent_slug' => 'general-options',
      'capability' => 'edit_posts',
      'autoload' => false,
    ]);
  }

  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_653fdd4a51868',
    'title' => 'Footer',
    'fields' => [
      [
        'key' => 'field_653fdd4a8347c',
        'label' => 'Text',
        'name' => 'footer_text',
        'type' => 'wysiwyg',
        'toolbar' => 'full',
        'media_upload' => 0,
        'show_in_graphql' => 1,
        'graphql_field_name' => 'footerText',
      ],
    ],
    'location' => [
      [['param' => 'options_page', 'operator' => '==', 'value' => 'footer']],
    ],
    'style' => 'default',
    'label_placement' => 'left',
    'instruction_placement' => 'label',
    'show_in_graphql' => 1,
    'graphql_field_name' => 'footer',
    'active' => true,
  ]);
});
