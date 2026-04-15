<?php

/**
 * Visual Page Links module — ACF local field group registration.
 */

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_module_visual_page_links',
  'title' => 'Visual Page Links',
  'fields' => [
    [
      'key' => 'field_visual_page_links_links',
      'label' => 'Links',
      'name' => 'links',
      'type' => 'repeater',
      'min' => 1,
      'button_label' => 'Add Link',
      'sub_fields' => [
        [
          'key' => 'field_visual_page_links_link',
          'label' => 'Link',
          'name' => 'link',
          'type' => 'link',
        ],
        [
          'key' => 'field_visual_page_links_image',
          'label' => 'Image',
          'name' => 'image',
          'type' => 'image',
          'return_format' => 'array',
          'preview_size' => 'medium',
        ],
      ],
    ],
  ],
  'location' => [],
  'active' => true,
]);
