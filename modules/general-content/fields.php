<?php

/**
 * General Content module — ACF local field group registration.
 */

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_module_general_content',
  'title' => 'General Content',
  'fields' => [
    [
      'key' => 'field_62586c16b4c24',
      'label' => 'General Content',
      'name' => 'general_content',
      'type' => 'wysiwyg',
      'toolbar' => 'full',
      'media_upload' => 0,
    ],
  ],
  'location' => [],
  'active' => true,
]);
