<?php

/**
 * Hide Admin Notices — ACF local field group registration.
 *
 * @package Tofino
 * @since 5.0.0
 */

if (function_exists('acf_add_options_sub_page')) {
  acf_add_options_sub_page([
    'page_title' => 'Hide Admin Notices',
    'menu_title' => 'Hide Admin Notices',
    'menu_slug' => 'hide-admin-notices',
    'parent_slug' => 'general-options',
    'capability' => 'manage_options',
    'autoload' => false,
  ]);
}

if (!function_exists('acf_add_local_field_group')) {
  return;
}

acf_add_local_field_group([
  'key' => 'group_hide_admin_notices',
  'title' => 'Hide Admin Notices',
  'fields' => [
    [
      'key' => 'field_hide_notices_roles',
      'label' => 'Hide Notices for Roles',
      'name' => 'hide_notices_roles',
      'type' => 'select',
      'instructions' => 'Admin notices are hidden from regular admin screens for users in these roles.',
      'allow_null' => 1,
      'multiple' => 1,
      'ui' => 1,
      'return_format' => 'value',
      'default_value' => ['administrator'],
    ],
    [
      'key' => 'field_notices_panel_roles',
      'label' => 'Show Notices Panel for Roles',
      'name' => 'notices_panel_roles',
      'type' => 'select',
      'instructions' => 'Users in these roles can access the dedicated Notices page via the admin bar.',
      'allow_null' => 1,
      'multiple' => 1,
      'ui' => 1,
      'return_format' => 'value',
      'default_value' => ['administrator'],
    ],
  ],
  'location' => [
    [['param' => 'options_page', 'operator' => '==', 'value' => 'hide-admin-notices']],
  ],
  'style' => 'seamless',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'active' => true,
]);

/**
 * Populates the role-select fields with the site's registered roles.
 */
add_filter('acf/load_field/key=field_hide_notices_roles', 'tofino_hide_admin_notices_load_roles');
add_filter('acf/load_field/key=field_notices_panel_roles', 'tofino_hide_admin_notices_load_roles');

function tofino_hide_admin_notices_load_roles(array $field): array
{
  $field['choices'] = [];
  $roles = wp_roles()->get_names();

  foreach ($roles as $slug => $label) {
    $field['choices'][$slug] = translate_user_role($label);
  }

  return $field;
}
