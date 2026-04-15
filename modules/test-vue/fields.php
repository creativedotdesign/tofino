<?php

/**
 * Test Vue module — ACF local field group registration.
 */

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_module_test_vue',
  'title' => 'Test Vue',
  'fields' => [
    [
      'key' => 'field_690930667390a',
      'label' => 'Test Vue',
      'name' => 'test_vue_message',
      'type' => 'message',
      'message' => 'This module mounts a Vue app from modules/test-vue.',
    ],
  ],
  'location' => [],
  'active' => true,
]);
