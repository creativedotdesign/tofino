<?php

/**
 * Client Data — ACF local field group registration.
 *
 * @package Tofino
 * @since 5.0.0
 */

add_action('acf/init', function () {
  if (function_exists('acf_add_options_sub_page')) {
    acf_add_options_sub_page([
      'page_title' => 'Client Data',
      'menu_title' => 'Client Data',
      'menu_slug' => 'client-data',
      'parent_slug' => 'general-options',
      'capability' => 'edit_posts',
      'autoload' => false,
    ]);
  }

  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_653fdbc6d93b5',
    'title' => 'Client Data',
    'fields' => [
      [
        'key' => 'field_653fdbc721c1c',
        'label' => 'Telephone Number',
        'name' => 'telephone_number',
        'type' => 'text',
      ],
      [
        'key' => 'field_653fdc00ceac3',
        'label' => 'Email Address',
        'name' => 'email_address',
        'type' => 'text',
      ],
      [
        'key' => 'field_653fdc0aceac4',
        'label' => 'Company Name',
        'name' => 'company_name',
        'type' => 'text',
      ],
      [
        'key' => 'field_653fdc17ceac5',
        'label' => 'Address',
        'name' => 'address',
        'type' => 'textarea',
        'rows' => 4,
      ],
      [
        'key' => 'field_653fdc3dceac6',
        'label' => 'Company Number',
        'name' => 'company_number',
        'type' => 'text',
      ],
    ],
    'location' => [
      [['param' => 'options_page', 'operator' => '==', 'value' => 'client-data']],
    ],
    'style' => 'seamless',
    'label_placement' => 'left',
    'instruction_placement' => 'label',
    'active' => true,
  ]);
});
