<?php

/**
 * Social Media Links — ACF local field group registration.
 *
 * @package Tofino
 * @since 5.0.0
 */

if (function_exists('acf_add_options_sub_page')) {
  acf_add_options_sub_page([
    'page_title' => 'Social Media',
    'menu_title' => 'Social Media',
    'menu_slug' => 'social-icons',
    'parent_slug' => 'general-options',
    'capability' => 'edit_posts',
    'autoload' => false,
  ]);
}

if (!function_exists('acf_add_local_field_group')) {
  return;
}

$sub_fields = [];

foreach (social_icons_platforms() as $name => $label) {
  $sub_fields[] = [
    'key' => 'field_social_' . $name,
    'label' => $label,
    'name' => $name,
    'type' => 'url',
  ];
}

acf_add_local_field_group([
  'key' => 'group_653fd7cec6a73',
  'title' => 'Social Media Links',
  'fields' => [
    [
      'key' => 'field_66e879b37ed58',
      'label' => 'Social Media Links',
      'name' => 'social_media_links',
      'type' => 'group',
      'layout' => 'row',
      'show_in_graphql' => 1,
      'graphql_field_name' => 'socialMediaLinks',
      'sub_fields' => $sub_fields,
    ],
  ],
  'location' => [
    [['param' => 'options_page', 'operator' => '==', 'value' => 'social-icons']],
  ],
  'style' => 'seamless',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'active' => true,
]);
