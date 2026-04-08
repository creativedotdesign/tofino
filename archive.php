<?php

/**
 * Archive template
 *
 * Resolves the most specific archive template from the templates/ directory.
 * Checks in order: slug, taxonomy-term, posttype-taxonomy-term, taxonomy, posttype.
 *
 * @package Tofino
 * @since 1.0.0
 */

get_header();

$queried  = get_queried_object();
$slug     = $queried->slug ?? null;
$taxonomy = $queried->taxonomy ?? null;
$post_type = $queried->name ?? null;

$candidates = array_filter([
  $slug,                                          // archive-{slug}
  $taxonomy && $slug ? "$taxonomy-$slug" : null,  // archive-{taxonomy}-{term}
  $post_type && $taxonomy && $slug ? "$post_type-$taxonomy-$slug" : null, // archive-{posttype}-{taxonomy}-{term}
  $taxonomy,                                      // archive-{taxonomy}
  $post_type,                                     // archive-{posttype}
]);

$template_found = false;

foreach ($candidates as $candidate) {
  if (locate_template("templates/archive-$candidate.php")) {
    get_template_part('templates/archive', $candidate);
    $template_found = true;
    break;
  }
}

if (!$template_found) {
  echo '<div class="error notice"><p>' . esc_html__('Error: Unable to locate an archive template. Did you create the file in /templates?', 'tofino') . '</p></div>';
}

get_footer();
