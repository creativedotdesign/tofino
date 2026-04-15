<?php

/**
 * iFrame module — ACF local field group registration.
 */

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_module_iframe',
  'title' => 'iFrame',
  'fields' => [
    [
      'key' => 'field_67102392ffa8e',
      'label' => 'URL',
      'name' => 'iframe_url',
      'type' => 'url',
    ],
    [
      'key' => 'field_6710243cffa8f',
      'label' => 'Add CSS container class',
      'name' => 'iframe_container',
      'type' => 'true_false',
      'default_value' => 0,
      'ui' => 1,
    ],
  ],
  'location' => [],
  'active' => true,
]);
