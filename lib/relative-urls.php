<?php

namespace Tofino\RelativeUrls;

function relative_urls() {
  if (is_admin() || preg_match('/sitemap(_index)?\.xml/', $_SERVER['REQUEST_URI']) || in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php'])) {
    return;
  }

  $filters = array(
    'post_link',
    'post_type_link',
    'page_link',
    'attachment_link',
    'get_shortlink',
    'post_type_archive_link',
    'get_pagenum_link',
    'get_comments_pagenum_link',
    'term_link',
    'search_link',
    'day_link',
    'month_link',
    'year_link',
  );

  foreach ($filters as $filter) {
    add_filter($filter, 'wp_make_link_relative');
  }
}

add_action('template_redirect', __NAMESPACE__ . '\\relative_urls');
