<?php

namespace Tofino\Helpers;

/**
 * Helper function to get the page_name.
 */
function get_page_name($page_id = null) {
  global $pagename;
  if (!$pagename) { // Not found in the query_var. Permalinks is probably not enabled.
    global $wp_query;
    $page_id  = ($page_id ? $page_id : get_the_ID());
    $post     = $wp_query->get_queried_object();
    $pagename = $post->post_name;
  }
  return $pagename;
}
