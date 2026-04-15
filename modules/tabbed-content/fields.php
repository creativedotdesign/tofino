<?php

/**
 * Tabbed Content module — ACF local field group registration.
 */

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_module_tabbed_content',
  'title' => 'Tabbed Content',
  'fields' => [
    [
      'key' => 'field_tabbed_content_tabs',
      'label' => 'Tabs',
      'name' => 'tabs',
      'type' => 'repeater',
      'min' => 1,
      'button_label' => 'Add Tab',
      'sub_fields' => [
        [
          'key' => 'field_tabbed_content_tab_title',
          'label' => 'Tab Title',
          'name' => 'tab_title',
          'type' => 'text',
          'required' => 1,
        ],
        [
          'key' => 'field_tabbed_content_tab_content',
          'label' => 'Tab Content',
          'name' => 'tab_content',
          'type' => 'wysiwyg',
          'toolbar' => 'full',
          'media_upload' => 1,
        ],
      ],
    ],
  ],
  'location' => [],
  'active' => true,
]);
