<?php

/**
 * Media Attachments — ACF local field group registration.
 *
 * @package Tofino
 * @since 5.0.0
 */

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_64c9841305284',
  'title' => 'Media Attachments',
  'fields' => [
    [
      'key' => 'field_64c9841323a4a',
      'label' => 'Media Credit',
      'name' => 'media_credit',
      'type' => 'text',
    ],
  ],
  'location' => [
    [['param' => 'attachment', 'operator' => '==', 'value' => 'image']],
  ],
  'active' => true,
]);
