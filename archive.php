<?php get_header(); ?>

<?php
if (locate_template('templates/archive-' . get_queried_object()->slug . '.php') != '') { // archive-{category-slug}
  get_template_part('templates/archive', get_queried_object()->slug); // e.g. templates/archive-category-slug.php
} elseif (locate_template('templates/archive-' . get_queried_object()->taxonomy . '-' . get_queried_object()->slug . '.php') != '') { // archive-{taxonomy}-{term}
  get_template_part('templates/archive', get_queried_object()->taxonomy . '-' . get_queried_object()->slug);
} elseif (locate_template('templates/archive-' . get_post_type() . '-' . get_queried_object()->taxonomy . '-' . get_queried_object()->slug . '.php') != '') { // archive-{posttype}-{taxonomy}-{term}
  get_template_part('templates/archive', get_post_type() . '-' . get_queried_object()->taxonomy . '-' . get_queried_object()->slug);
} else { // archive-{posttype}
  get_template_part('templates/archive', get_post_type());
} ?>

<?php get_footer(); ?>
