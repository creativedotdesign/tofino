<?php

/**
 * Dynamic layout functions
 *
 * @package Tofino
 * @since 4.3.0
 */

namespace Tofino\Layouts;


// Add custom layouts to the page template dropdown
function acf_show_custom_layout_fields(array $field) {
  // If no parent is found, everything kind of breaks.
	if (!$field['parent']) {
		return $field;
	}

	// Match only the key of the group we want to change.
	if ('group_66e8b2aebe427' === $field['parent']) {
    $enabled = get_field('custom_layouts', 'option');

    if (!$enabled) {
      return false;
    }
	}

	return $field;
}
add_filter('acf/prepare_field', __NAMESPACE__ . '\\acf_show_custom_layout_fields');


// Update the select options for the layout field
function acf_load_layouts_select_options(array $field)
{
  // reset choices
  $field['choices'] = [];

  $content_modules = get_field_object('field_62586c9af1a1a');

  // Add one empty option
  $field['choices'][''] = 'Select';

  if ($content_modules) {
    foreach ($content_modules['layouts'] as $module) {
      $field['choices'][$module['name']] = $module['label'];
    }
  }

  // return the field
  return $field;
}
add_filter('acf/load_field/name=module_name', __NAMESPACE__ . '\\acf_load_layouts_select_options');


// Update the select options for the page template field
function acf_load_layout_names(array $field)
{
  // reset choices
  $field['choices'] = [];

  $layouts = get_field('layout', 'option');

  $field['choices'][''] = 'Custom';

  // Loop through layouts and add to select field
  if ($layouts) {
    foreach ($layouts as $layout) {
      $modules = $layout['modules'];

      $module_names = [];

      // Extract the module name values from the arrays
      foreach ($modules as $module) {
        $module_names[] = $module['module_name'];
      }

      $field['choices'][json_encode($module_names)] = $layout['name'];
    }
  }

  // return the field
  return $field;
}
add_filter('acf/load_field/name=page_template', __NAMESPACE__ . '\\acf_load_layout_names');


// Sort layouts alphabetically
function sort_acf_flexible_content_layouts(array $field) {
  if (is_admin() && isset($field['layouts']) && is_array($field['layouts']) && count($field['layouts']) > 1) {
    // Sort layouts alphabetically
    usort($field['layouts'], function($a, $b) {
      return strcmp($a['name'], $b['name']);
    });
  }

  return $field;
}
add_filter('acf/load_field/name=content_modules', __NAMESPACE__ . '\\sort_acf_flexible_content_layouts');
