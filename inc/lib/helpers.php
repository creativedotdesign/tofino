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
function title() {
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
function hm_get_template_part($file, $template_args = []) {
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
function get_page_name($page_id = null) {
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
function get_id_by_slug($slug, $post_type = 'page') {
  $page = get_page_by_path($slug, 'OBJECT', $post_type);
  if ($page) {
    return $page->ID;
  } else {
    if (function_exists('icl_object_id')) { //WPML installed
      $page = get_page(icl_object_id($page->ID, 'page', true, ICL_LANGUAGE_CODE));
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
function get_complete_meta($post_id, $meta_key) {
  global $wpdb;
  $mid = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, $meta_key));
  if ($mid != '') {
    return $mid;
  } else {
    return false;
  }
}


/**
 * Sanitizes choices (selects / radios)
 *
 * Checks that the input matches one of the available choices

 * @param string $input The input.
 * @param object $setting The setting to validate.
 * @since 1.2.0
 */
function sanitize_choices($input, $setting) {
  global $wp_customize;
  $control = $wp_customize->get_control($setting->id);
  if (array_key_exists($input, $control->choices)) {
    return $input;
  } else {
    return $setting->default;
  }
}


/**
 * Sanitize textarea
 *
 * Keeps line breaks
 * Replace once this patch merged: https://core.trac.wordpress.org/ticket/32257
 *
 * @since 1.6.0
 * @param string $input The input.
 * @return string Sanitized string.
 */
function sanitize_textarea($input) {
  return implode("\n", array_map('sanitize_text_field', explode("\n", $input)));
}


/**
 * Fix text orphan
 *
 * Make last space in a sentence a non breaking space to prevent typographic widows.
 *
 * @since 3.2.0
 * @param type $str
 * @return string
 */
function fix_text_orphan($str = '') {
  $str   = trim($str); // Strip spaces.
  $space = strrpos($str, ' '); // Find the last space.

  // If there's a space then replace the last on with a non breaking space.
  if (false !== $space) {
    $str = substr($str, 0, $space) . '&nbsp;' . substr($str, $space + 1);
  }

  // Return the string.
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
function responsive_image_attribute_values($image_id = null, $size = 'full') {
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
    'src' => $url[0],
    'alt' => $alt
  ];
}
