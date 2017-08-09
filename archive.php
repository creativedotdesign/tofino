<?php get_header(); ?>

<?php
$slug     = isset(get_queried_object()->slug) ? get_queried_object()->slug : null;
$taxonomy = isset(get_queried_object()->taxonomy) ? get_queried_object()->taxonomy : null;

if (locate_template('templates/archive-' . $slug . '.php') != '') { // archive-{category-slug}
  get_template_part('templates/archive', $slug); // e.g. templates/archive-category-slug.php
} elseif (locate_template('templates/archive-' . $taxonomy . '-' . $slug . '.php') != '') { // archive-{taxonomy}-{term}
  get_template_part('templates/archive', $taxonomy . '-' . $slug);
} elseif (locate_template('templates/archive-' . get_post_type() . '-' . $taxonomy . '-' . $slug . '.php') != '') { // archive-{posttype}-{taxonomy}-{term}
  get_template_part('templates/archive', get_post_type() . '-' . $taxonomy . '-' . $slug);
} elseif (locate_template('templates/archive-' . $taxonomy . '.php') != '') { // archive-{taxonomy}
  get_template_part('templates/archive', $taxonomy);
} else { // archive-{posttype}
  get_template_part('templates/archive', get_post_type());
} ?>

<?php get_footer(); ?>
