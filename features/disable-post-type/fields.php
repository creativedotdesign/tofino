<?php

/**
 * Disable Post Type — ACF local field group registration.
 *
 * @package Tofino
 * @since 5.0.0
 */

if (function_exists('acf_add_options_sub_page')) {
  acf_add_options_sub_page([
    'page_title' => 'Posts',
    'menu_title' => 'Posts',
    'menu_slug' => 'disable-post-type',
    'parent_slug' => 'general-options',
    'capability' => 'edit_posts',
    'autoload' => false,
  ]);
}

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_disable_post_type',
  'title' => 'Posts',
  'fields' => [
    [
      'key' => 'field_66eadfd454ff0',
      'label' => 'Disable Post Type',
      'name' => 'disable_post_type',
      'type' => 'true_false',
      'instructions' => 'Removes the default post type.',
      'default_value' => 0,
      'ui' => 1,
    ],
  ],
  'location' => [
    [['param' => 'options_page', 'operator' => '==', 'value' => 'disable-post-type']],
  ],
  'style' => 'seamless',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'active' => true,
]);
