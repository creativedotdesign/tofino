<?php get_header(); ?>

<?php
$template = get_post_type() . '-' . Tofino\Helpers\get_page_name(); // (post)-(my-post-name).

if (locate_template('templates/content-single-' . $template . '.php') != '') {
  get_template_part('templates/content-single', $template); // e.g. templates/content-single-post-my-post-name.php
} else {
  get_template_part('templates/content-single', get_post_type());
} ?>

<?php get_footer(); ?>
