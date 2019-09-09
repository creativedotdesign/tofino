<?php
/**
 *
 * Single post template
 *
 * Looks for template based on post name and post type.
 * Falls back to standard post type template.
 *
 * @package Tofino
 * @since 1.0.0
 */

use \Tofino\Helpers as h;

get_header();

$template = get_post_type() . '-' . h\get_page_name(); // (post)-(my-post-name).

if (function_exists('icl_object_id')) { // WPML installed
  $original_page_id = apply_filters('wpml_object_id', get_the_ID(), 'page', false, 'en'); //Assumes english is the primary language.
  if ($original_page_id) {
    $template = get_post_type() . '-' . h\get_page_name($original_page_id);
  }
}

if (locate_template('templates/content-single-' . $template . '.php') != '') {
  get_template_part('templates/content-single', $template); // e.g. templates/content-single-post-my-post-name.php
} else {
  get_template_part('templates/content-single', get_post_type());
}

get_footer(); ?>
