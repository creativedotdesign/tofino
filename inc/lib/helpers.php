<?php

/**
 *
 * Helper functions
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Helpers;

/**
 * Page titles
 *
 * @since 1.7.0
 * @return string
 */
function title()
{
  if (is_home()) {
    if ($home = get_option('page_for_posts', true)) {
      return get_the_title($home);
    }
    return __('Latest Posts', 'tofino');
  }

  if (is_archive()) {
    return get_the_archive_title();
  }

  if (is_search()) {
    return sprintf(__('Search Results for %s', 'tofino'), get_search_query());
  }

  if (is_404()) {
    return __('Not Found', 'tofino');
  }
  return get_the_title();
}


/**
 * Like get_template_part() put lets you pass args to the template file
 * Args are available in the tempalte as $template_args array
 * From https://github.com/humanmade/hm-core/blob/master/hm-core.functions.php
 *
 * @since 1.7.0
 *
 * @param string filepart
 * @param mixed wp_args style argument list
 */
function hm_get_template_part($file, $template_args = [])
{
  $template_args = wp_parse_args($template_args);

  if (file_exists(get_template_directory() . '/' . $file . '.php')) {
    $file = get_template_directory() . '/' . $file . '.php';
  }

  ob_start();

  $return = require($file);
  $data   = ob_get_clean();

  if (!empty($template_args['return'])) {
    if ($return === false) {
      return false;
    } else {
      return $data;
    }
  }

  echo $data;
}


/**
 * Gets the page name
 *
 * Looks up the pagename (slug). If not found in the query_var it uses the $page_id
 * if passed or falls back to the get_the_ID() function. Used by templates.
 *
 * @since 1.0.0
 *
 * @global string $pagename The pagename string (slug) from the query_var
 *
 * @param integer $page_id The id of the page
 * @return string pagename (slug)
 */
function get_page_name($page_id = null)
{
  global $pagename;
  if (!$pagename || $page_id) { // Not found in the query_var. Permalinks probably not enabled.
    $page_id  = ($page_id ? $page_id : get_the_ID());
    $post     = get_post($page_id);
    $pagename = $post->post_name;
  }
  return $pagename;
}


/**
 * Gets the page / post ID from slug.
 *
 * If WPML is active and the function fails to find a valid page ID it will look
 * for the translated version.
 *
 * @since 1.0.0
 *
 * @param  string $slug The slug to search against
 * @param  string $post_type page, post etc
 * @return integer|null
 */
function get_id_by_slug($slug, $post_type = 'page')
{
  $page = get_page_by_path($slug, 'OBJECT', $post_type);
  if ($page) {
    return $page->ID;
  } else {
    if (function_exists('icl_object_id')) { //WPML installed
      $page = get_post(icl_object_id($page->ID, 'page', true, ICL_LANGUAGE_CODE));
      if ($page) {
        return $page->ID;
      } else {
        return null;
      }
    } else {
      return null;
    }
  }
}


/**
 * Get Complete Meta
 *
 * Gets the complete meta data attached to a post for a meta key.
 *
 * @since 1.6.0
 *
 * @param  integer $post_id  The post id
 * @param  string  $meta_key The meta key to search
 * @return object  A PHP Object containing the meta data with key value pairs
 */
function get_complete_meta($post_id, $meta_key)
{
  global $wpdb;
  $mid = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, $meta_key));
  if ($mid != '') {
    return $mid;
  } else {
    return false;
  }
}


/**
 * Fix text orphans by adding a non-breaking space before the last word,
 * while ignoring HTML tags such as <a>, <strong>, etc.
 * 
 * @since 3.2.0
 * @param string $str The input string which may contain HTML.
 * @param int $min_word_count The minimum word count required to apply the fix.
 * @return string The processed string with a non-breaking space before the last word in plain text.
 */
function fix_text_orphan($str = '', $min_word_count = 3)
{
	// Trim the input string to remove excess spaces.
	$str = trim($str);

	// Regex to match all words, ignoring anything inside HTML tags
	$pattern = '/>([^<]+)</'; // Matches the text between HTML tags

	// Callback function to apply the non-breaking space logic to text content
	$str = preg_replace_callback($pattern, function ($matches) use ($min_word_count) {
		// Get the text between HTML tags
		$text = $matches[1];

		// Find the last space
		$space = strrpos($text, ' ');

		// Count words in the text
		$word_count = str_word_count($text);

		// Apply the non-breaking space if word count is above the threshold and a space exists
		if ($space !== false && $word_count > $min_word_count) {
			$text = substr($text, 0, $space) . '&nbsp;' . substr($text, $space + 1);
		}

		// Return the processed text between the original HTML tags
		return '>' . $text . '<';
	}, $str);

	return $str;
}


/**
 * Responsive Image Attrs
 *
 * Returns a clean array of the values needed for a responsive image
 *
 * @since 3.2.1
 * @param integer $image_id (optional) Defaults to post featured image id
 * @param string $size (optional) The image sized used for the main src
 * @return array Values to populate into an img tag
 */
function responsive_image_attribute_values($image_id = null, $size = 'full')
{
  if (!$image_id) {
    $image_id = get_post_thumbnail_id();
  }

  $meta = wp_get_attachment_metadata($image_id);
  $url = wp_get_attachment_image_src($image_id, $size);
  $sizes = wp_calculate_image_sizes($size, $url, $meta, $image_id);
  $srcset = wp_get_attachment_image_srcset($image_id, $size);
  $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);

  return [
    'srcset' => $srcset,
    'sizes' => $sizes,
    'src' => ($url ? $url[0] : null),
    'alt' => esc_attr($alt)
  ];
}


/**
 * Wrap last word with span
 * https://stackoverflow.com/questions/18612872/get-the-last-word-of-a-string
 */
function wrap_last_word($string, $class)
{
  // Breaks string to pieces
  $pieces = explode(" ", $string);

  // Modifies the last word
  $pieces[count($pieces) - 1] = '<span class="' . $class . '">' . $pieces[count($pieces) - 1] . '</span>';

  // Returns the glued pieces
  return implode(" ", $pieces);
}


// Get a sub field from within a layout of a flexible content field on a specific page
function get_flex_field_by_page_id($flex_field, $flex_layout, $flex_field_layout = '', $page_id = null) {
  $page_id = $page_id ? $page_id : get_the_ID();

  $fields = get_field($flex_field, $page_id);

  if ($fields) {
    foreach($fields as $field) {
      if ($flex_field_layout) {
        if ($field['acf_fc_layout'] === $flex_layout) {
          return $field[$flex_field_layout];
        }
      } else { // No flex_field_layout
        return $field;
      }
    }
  }
}


// Check if a page contains only a single module
function is_single_module_page($module_name, $page_id) {
  // Get the ACF Flexible Content field value for the specified page
  $modules = get_field('content_modules', $page_id);

  // Check if the field exists and contains only one module
  if ($modules && is_array($modules) && count($modules) === 1) {
    // Check if the module name matches the specified module
    if ($modules[0]['acf_fc_layout'] === $module_name) {
      return true; // The page contains only the specified module
    }
  }

  return false; // The page does not contain only the specified module
}


// Check acf link target
function check_target($target)
{
  if ($target === '_blank') {
    return $target;
  } else {
    return '_self';
  }
}


/**
 * Function to render the module layout file
 * 
 * @param string $layout Name of the layout file
 */
function render_module($layout)
{
  // Get paths where modules exist
  $paths = apply_filters('tofino_custom_module_paths', [get_template_directory() . '/templates/modules/']);

  foreach ($paths as $path) {
    $file_path = $path . $layout . '.php';

    if (file_exists($file_path)) { 
      include $file_path;

      // Set plugin flag if loaded from plugin directory
      $is_from_plugin = strpos($path, get_template_directory()) === false;

      if ($is_from_plugin) {
        echo "<!-- Module loaded from plugin -->";
      }
    }
  }
}
