<?php

/**
 * Dynamic layout functions
 *
 * @package Tofino
 * @since 4.3.0
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

  $field_key = get_field('auto_generate_page_modules', 'option')
    ? 'field_content_modules'
    : 'field_62586c9af1a1a';

  $content_modules = get_field_object($field_key);

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


/**
 * Dynamically generates and registers an ACF flexible content field group
 * from all field groups tagged with the `page-module` ACFE category.
 * Only runs when the auto_generate_page_modules option is enabled.
 *
 * @return void
 */
function auto_generate_page_modules(): void
{
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  if (!get_field('auto_generate_page_modules', 'option')) {
    return;
  }

  $field_groups = get_posts([
    'post_type'      => 'acf-field-group',
    'post_status'    => ['acf-disabled'],
    'posts_per_page' => -1,
  ]);

  $dynamic_page_modules = [];

  foreach ($field_groups as $field_group) {
    $group = acf_get_field_group($field_group->post_name);

    if ($group && isset($group['acfe_categories']) && in_array('page-module', array_keys($group['acfe_categories']), true)) {
      $dynamic_page_modules[] = $group;
    }
  }

  $layouts = [];

  foreach ($dynamic_page_modules as $module) {
    $slug = str_replace('-', '_', sanitize_title($module['title']));

    $layouts[] = [
      'key'        => 'layout_' . $slug,
      'name'       => $slug,
      'label'      => $module['title'],
      'display'    => 'block',
      'sub_fields' => [
        [
          'key'     => 'field_' . $slug,
          'label'   => $module['title'],
          'name'    => $slug,
          'type'    => 'clone',
          'clone'   => [$module['key']],
          'layout'  => 'block',
          'display' => 'seamless',
        ],
      ],
    ];
  }

  $flexible_content = [
    'key'          => 'field_content_modules',
    'label'        => 'Content Modules',
    'name'         => 'content_modules',
    'type'         => 'flexible_content',
    'layouts'      => $layouts,
    'button_label' => 'Add Module',
    'parent'       => 'group_page_modules',
  ];

  $page_modules = [
    'key'    => 'group_page_modules',
    'title'  => 'Dynamic Page Modules',
    'fields' => [$flexible_content],
    'location' => [
      [
        [
          'param'    => 'post_type',
          'operator' => '==',
          'value'    => 'page',
        ],
      ],
    ],
    'private' => true,
    'style'   => 'seamless',
    'hide_on_screen' => [
      0  => 'the_content',
      1  => 'discussion',
      2  => 'comments',
      3  => 'revisions',
      4  => 'slug',
      5  => 'author',
      6  => 'format',
      8  => 'categories',
      9  => 'tags',
      10 => 'send-trackbacks',
    ],
  ];

  acf_add_local_field_group($page_modules);

  acf_update_setting('acfe/reserved_field_groups', [
    'group_page_modules',
  ]);
}
add_action('acf/include_fields', __NAMESPACE__ . '\\auto_generate_page_modules');


/**
 * Registers the required ACF field group categories in the
 * `acf-field-group-category` taxonomy if they do not already exist.
 *
 * @return void
 */
function auto_add_acf_field_group_categories(): void
{
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  $acf_taxonomy = 'acf-field-group-category';

  if (!taxonomy_exists($acf_taxonomy)) {
    return;
  }

  $categories = [
    'page-module' => 'Page Module',
    'options'     => 'Options',
    'media'       => 'Media',
  ];

  foreach ($categories as $slug => $name) {
    if (!term_exists($slug, $acf_taxonomy)) {
      wp_insert_term($name, $acf_taxonomy, ['slug' => $slug]);
    }
  }
}
add_action('init', __NAMESPACE__ . '\\auto_add_acf_field_group_categories', 10);


/**
 * Scans the local ACF JSON files and assigns each field group to its
 * corresponding taxonomy terms based on the `acfe_categories` property.
 * Results are cached in a transient for 24 hours to avoid repeated file scans.
 *
 * @return void
 */
function assign_acf_field_group_to_terms_by_key(): void
{
  if (get_transient('acfe_categories_checked') !== false) {
    return;
  }

  $json_path = get_stylesheet_directory() . '/inc/acf-json/';
  $files     = glob($json_path . '*.json') ?: [];

  foreach ($files as $file) {
    $json_data = json_decode(file_get_contents($file), true);

    if (!isset($json_data['acfe_categories'], $json_data['title'])) {
      continue;
    }

    $field_group = get_posts([
      'post_type'      => 'acf-field-group',
      'title'          => $json_data['title'],
      'fields'         => 'ids',
      'posts_per_page' => 1,
      'post_status'    => ['publish', 'acf-disabled'],
    ]);

    if (empty($field_group)) {
      continue;
    }

    $field_group_id = $field_group[0];

    foreach ($json_data['acfe_categories'] as $category_slug) {
      $term = term_exists($category_slug, 'acf-field-group-category');

      if (!empty($term)) {
        wp_set_object_terms($field_group_id, (int) $term['term_id'], 'acf-field-group-category', false);
      }
    }
  }

  set_transient('acfe_categories_checked', true, DAY_IN_SECONDS);
}
add_action('init', __NAMESPACE__ . '\\assign_acf_field_group_to_terms_by_key');
