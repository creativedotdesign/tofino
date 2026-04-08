<?php

/**
 * Blog page template
 *
 * Overrides the global $post to the "Posts page" set in Settings > Reading,
 * so ACF content modules load from the correct page.
 *
 * @package Tofino
 * @since 1.0.0
 */

get_header();

$post_id = get_option('page_for_posts');

if ($post_id) {
  global $post;
  $post = get_post($post_id);
  setup_postdata($post);
}

get_template_part('templates/content', 'page');

wp_reset_postdata();

get_footer();
