<?php
get_header();

// Override the post ID if we are on the main Blog page.
// This is to ensure that the content modules can be called
// with the correct post ID
if (is_home()) {
  $post_id = get_option('page_for_posts');

  global $post;
  $post = get_post($post_id, OBJECT);
  setup_postdata($post);
}

get_template_part('templates/content', 'page');

get_footer();
