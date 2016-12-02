<?php
/**
 * WordPress Bootstrap Pagination
 */
namespace Tofino\Pagination;

function wp_bootstrap_pagination($args = []) {
  $defaults = [
    'range'           => 4,
    'custom_query'    => false,
    'previous_string' => '<span aria-hidden="true">&laquo;</span> ' . __('Previous Page', 'tofino'),
    'next_string'     => __('Next Page', 'tofino') . ' <span aria-hidden="true">&raquo;</span>',
    'before_output'   => '<ul class="pagination">',
    'after_output'    => '</ul>'
  ];

  $args = wp_parse_args(
    $args,
    apply_filters('wp_bootstrap_pagination_defaults', $defaults)
  );

  $args['range'] = (int) $args['range'] - 1;

  if (!$args['custom_query']) {
    $args['custom_query'] = @$GLOBALS['wp_query'];
    $count = (int) $args['custom_query']->max_num_pages;
    $page  = intval(get_query_var('paged'));
    $ceil  = ceil($args['range'] / 2);
  }

  if ($count <= 1) {
    return false;
  }

  if (!$page) {
    $page = 1;
  }

  if ($count > $args['range']) {
    if ($page <= $args['range']) {
      $min = 1;
      $max = $args['range'] + 1;
    } elseif ($page >= ($count - $ceil)) {
      $min = $count - $args['range'];
      $max = $count;
    } elseif ($page >= $args['range'] && $page < ($count - $ceil)) {
      $min = $page - $ceil;
      $max = $page + $ceil;
    }
  } else {
    $min = 1;
    $max = $count;
  }

  $echo = '';
  $previous = intval($page) - 1;
  $previous = esc_attr(get_pagenum_link($previous));

  // $firstpage = esc_attr(get_pagenum_link(1));
  // if ($firstpage && (1 != $page)) {
  //   $echo .= '<li class="previous"><a href="' . $firstpage . '">' . __( 'First', 'text-domain' ) . '</a></li>';
  // }

  if ($previous && (1 != $page)) {
    $echo .= '<li class="page-item"><a href="' . $previous . '" title="' . __('Previous', 'tofino') . '" class="page-link">' . $args['previous_string'] . '</a></li>';
  }

  if (!empty($min) && !empty($max)) {
    for ($i = $min; $i <= $max; $i++) {
      if ($page == $i) {
        $echo .= '<li class="active page-item"><a class="page-link" href="#">' . (int)$i . '</a></li>';
      } else {
        $echo .= sprintf('<li class="page-item"><a href="%s" class="page-link">%2d</a></li>', esc_attr(get_pagenum_link($i)), $i);
      }
    }
  }

  $next = intval($page) + 1;
  $next = esc_attr(get_pagenum_link($next));

  if ($next && ($count != $page)) {
    $echo .= '<li class="page-item"><a href="' . $next . '" title="' . __('next', 'tofino') . '" class="page-link">' . $args['next_string'] . '</a></li>';
  }

  // $lastpage = esc_attr(get_pagenum_link($count));
  // if ($lastpage) {
  //    $echo .= '<li class="next"><a href="' . $lastpage . '">' . __( 'Last', 'text-domain' ) . '</a></li>';
  // }

  if (isset($echo)) {
    echo $args['before_output'] . $echo . $args['after_output'];
  }
}
