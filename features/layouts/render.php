<?php

/**
 * Dynamic layout functions.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Layouts;

/**
 * Hides the custom layout field group from ACF unless the custom_layouts option is enabled.
 * Bails early if the field has no parent, to avoid breaking unrelated fields.
 *
 * @param array<string, mixed> $field The ACF field array.
 * @return array<string, mixed>|false The field array, or false to hide the field.
 */
function acf_show_custom_layout_fields(array $field): array|false
{
  if (!isset($field['parent']) || !$field['parent']) {
    return $field;
  }

  if ('group_66e8b2aebe427' === $field['parent']) {
    $enabled = get_field('custom_layouts', 'option');

    if (!$enabled) {
      return false;
    }
  }

  return $field;
}
add_filter('acf/prepare_field', __NAMESPACE__ . '\\acf_show_custom_layout_fields');

/**
 * Populates the module_name select field with available layouts from the
 * content modules flexible content field.
 *
 * @param array<string, mixed> $field The ACF field array.
 * @return array<string, mixed> The field array with updated choices.
 */
function acf_load_layouts_select_options(array $field): array
{
  $field['choices'] = [];

  $content_modules = get_field_object('field_62586c9af1a1a');

  $field['choices'][''] = 'Select';

  if ($content_modules) {
    foreach ($content_modules['layouts'] as $module) {
      $field['choices'][$module['name']] = $module['label'];
    }
  }

  return $field;
}
add_filter('acf/load_field/name=module_name', __NAMESPACE__ . '\\acf_load_layouts_select_options');

/**
 * Populates the page_template select field with pre-defined layout options
 * stored in the theme options, serialised as JSON arrays of module names.
 *
 * @param array<string, mixed> $field The ACF field array.
 * @return array<string, mixed> The field array with updated choices.
 */
function acf_load_layout_names(array $field): array
{
  $field['choices'] = [];

  $layouts = get_field('layout', 'option');

  $field['choices'][''] = 'Custom';

  if ($layouts) {
    foreach ($layouts as $layout) {
      $module_names = array_column($layout['modules'], 'module_name');
      $field['choices'][json_encode($module_names)] = $layout['name'];
    }
  }

  return $field;
}
add_filter('acf/load_field/name=page_template', __NAMESPACE__ . '\\acf_load_layout_names');

/**
 * Sorts a flexible content field's layouts alphabetically by name in the admin.
 * Uses uasort to preserve the original associative keys required by ACF.
 *
 * @param array<string, mixed> $field The ACF field array.
 * @return array<string, mixed> The field array with sorted layouts.
 */
function sort_acf_flexible_content_layouts(array $field): array
{
  if (!is_admin() || !isset($field['layouts']) || !is_array($field['layouts']) || count($field['layouts']) <= 1) {
    return $field;
  }

  uasort($field['layouts'], fn($a, $b) => strcmp($a['name'], $b['name']));

  return $field;
}
add_filter('acf/load_field/name=content_modules', __NAMESPACE__ . '\\sort_acf_flexible_content_layouts');
