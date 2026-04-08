<?php

/**
 * Single post template
 *
 * Looks for a template based on post type and post slug.
 * Falls back to the default language slug if WPML is active.
 * Falls back to a generic post type template.
 *
 * @package Tofino
 * @since 1.0.0
 */

get_header();

$post_type = get_post_type();
$template = $post_type . '-' . get_post_field('post_name');

if (locate_template('templates/content-single-' . $template . '.php') !== '') {
  get_template_part('templates/content-single', $template);
} else {
  if (function_exists('wpml_object_id_filter')) {
    $default_lang = apply_filters('wpml_default_language', null);
    $original_id = apply_filters('wpml_object_id', get_the_ID(), $post_type, false, $default_lang);
    if ($original_id) {
      $template = $post_type . '-' . get_post_field('post_name', $original_id);
    }
  }

  if (locate_template('templates/content-single-' . $template . '.php') !== '') {
    get_template_part('templates/content-single', $template);
  } else {
    get_template_part('templates/content-single', $post_type);
  }
}

get_footer();
