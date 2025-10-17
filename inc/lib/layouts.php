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
	if (!array_key_exists('$field', $field) || !$field['parent']) {
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

  if (get_field('auto_generate_page_modules', 'option')) {
    $field_key = 'field_content_modules';
  } else {
    $field_key = 'field_62586c9af1a1a';
  }

  $content_modules = get_field_object($field_key);

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
    // Temporarily convert associative array (with layout keys) to a numerically indexed array
    $layouts_with_keys = $field['layouts'];
    $indexed_layouts = array_values($field['layouts']);

    // Sort the indexed array
    usort($indexed_layouts, function($a, $b) {
      return strcmp($a['name'], $b['name']);
    });

    // Rebuild the associative array to maintain original keys
    $sorted_layouts = [];

    foreach ($indexed_layouts as $layout) {
      // Use the original layout key to reconstruct the associative array
      $key = array_search($layout, $layouts_with_keys);
      $sorted_layouts[$key] = $layout;
    }

    // Update the field layouts with the sorted associative array
    $field['layouts'] = $sorted_layouts;
  }

  return $field;
}
add_filter('acf/load_field/name=content_modules', __NAMESPACE__ . '\\sort_acf_flexible_content_layouts');


// Automatically generate page modules
function auto_generate_page_modules() {
  if (function_exists('acf_add_local_field_group')) {
    // Check if the option is enabled
    if (!get_field('auto_generate_page_modules', 'option')) {
      return;
    }

    $args = [
      'post_type' => 'acf-field-group',
      'post_status' => ['acf-disabled'],
      'posts_per_page' => -1,
    ];

    $field_groups = get_posts($args);

    $dynamic_page_modules = [];

    // Loop through all the field groups and find the terms attached to them
    foreach ($field_groups as $field_group) {
      $post_name = $field_group->post_name;

      // Get the terms attached to the field group
      $group = acf_get_field_group($post_name);

      if ($group && array_key_exists('acfe_categories', $group)) {
        $categories = $group['acfe_categories'];

        // extract the keys from the categories array
        $keys = array_keys($categories);

        if (in_array('page-module', $keys)) {
          $dynamic_page_modules[] = $group;
        }
      }
    }

    // var_dump($dynamic_page_modules);

    $layouts = [];

    foreach ($dynamic_page_modules as $module) {
      $slug = str_replace( '-', '_', sanitize_title($module['title']));

      $layouts[] = [
        'key' => 'layout_' . $slug,
        'name' => $slug,
        'label' => $module['title'],
        'display' => 'block',
        'sub_fields' => [
          [
            'key' => 'field_' . $slug,
            'label' => $module['title'],
            'name' => $slug,
            'type' => 'clone',
            'clone' => [
              $module['key'],
            ],
            'layout' => 'block',
            'display' => 'seamless',
          ],
        ]
      ];
    }

    // var_dump($layouts);

    // Create Flexible Content field
    $flexible_content = [
      'key' => 'field_content_modules',
      'label' => 'Content Modules',
      'name' => 'content_modules',
      'type' => 'flexible_content',
      'layouts' => $layouts,
      'button_label' => 'Add Module',
      'parent' => 'group_page_modules',
    ];

    // Remove the local file field
    $page_modules = [
      'key' => 'group_page_modules',
      'title' => 'Dynamic Page Modules',
      'fields' => [
        $flexible_content,
      ],
      'location' => [
        [
          [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'page',
          ],
        ],
      ],
      'private' => true,
      'style' => 'seamless',
      'hide_on_screen' => [
        0 => 'the_content',
        1 => 'discussion',
        2 => 'comments',
        3 => 'revisions',
        4 => 'slug',
        5 => 'author',
        6 => 'format',
        8 => 'categories',
        9 => 'tags',
        10 => 'send-trackbacks',
      ],
    ];
    
    // var_dump($page_modules);
    // print_r(json_encode($page_modules, JSON_PRETTY_PRINT));

    // Register the field group
    acf_add_local_field_group($page_modules);

    // Update the reserved field groups
    acf_update_setting('acfe/reserved_field_groups', [
      'group_page_modules',
    ]);
  }
}
add_filter('acf/include_fields', __NAMESPACE__ . '\\auto_generate_page_modules');


// Add terms to the acf-field-group-category taxonomy
function auto_add_acf_field_group_categories() {
  if (function_exists('acf_add_local_field_group')) {
    $acf_taxonomy = 'acf-field-group-category';

    if (taxonomy_exists($acf_taxonomy)) {
      $categories = [
        'page-module' => 'Page Module',
        'options' => 'Options',
        'media' => 'Media',
      ];
  
      foreach ($categories as $slug => $name) {
        $term = term_exists($slug, $acf_taxonomy);
  
        if (!$term) {
          wp_insert_term($name, $acf_taxonomy, [
            'slug' => $slug,
          ]);
        }
      }
    }
  }
}
add_filter('init', __NAMESPACE__ . '\\auto_add_acf_field_group_categories', 10);


// Assign ACF field groups to terms by key
function assign_acf_field_group_to_terms_by_key() {
	// Get cached results to avoid repeated scans
	$cached_results = get_transient('acfe_categories_checked');

  // $cached_results = false;

	if ($cached_results !== false) {
		return;
	}

	// Scan the local ACF JSON files
	$json_path = get_stylesheet_directory() . '/inc/acf-json/';
	$files = glob($json_path . '*.json');

	foreach ($files as $file) {
		// Decode the JSON data
		$json_data = json_decode(file_get_contents($file), true);

		if (isset($json_data['acfe_categories']) && isset($json_data['title'])) {
			$acfe_categories = $json_data['acfe_categories'];
			$field_group_title = $json_data['title']; // Get the title from the JSON file

			// Find the field group by its title in the database
			$field_group = get_posts([
				'post_type' => 'acf-field-group',
				'title' => $field_group_title, // Query by title instead of key
				'fields' => 'ids',
				'posts_per_page' => 1,
        'post_status' => ['publish', 'acf-disabled'],
			]);

			if (!empty($field_group)) {
				$field_group_id = $field_group[0]; // This is the post ID of the field group

				// Loop through acfe_categories and match them with taxonomy terms
				foreach ($acfe_categories as $category_slug) {
					$term = term_exists($category_slug, 'acf-field-group-category');

					if ($term && !is_wp_error($term)) {
						$term_id = $term['term_id']; // Ensure we are using the term ID, not the slug or name

						// Assign the term to the ACF field group post using the term ID
						wp_set_object_terms($field_group_id, (int) $term_id, 'acf-field-group-category', false);
					}
				}
			}
		}
	}

	// Cache the results to prevent repeated scans
	set_transient('acfe_categories_checked', true, 86400); // Cache for 24 hours
}
add_action('init', __NAMESPACE__ . '\\assign_acf_field_group_to_terms_by_key');
