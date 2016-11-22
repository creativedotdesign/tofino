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
