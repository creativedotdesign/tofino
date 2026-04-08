<?php

/**
 * Page template
 *
 * Looks for a template based on the page slug.
 * Falls back to the default language slug if WPML is active.
 *
 * @package Tofino
 * @since 1.0.0
 */

get_header();

$template = get_post_field('post_name');

if (locate_template('templates/content-page-' . $template . '.php') !== '') {
  get_template_part('templates/content-page', $template);
} else {
  if (function_exists('wpml_object_id_filter')) {
    $default_lang = apply_filters('wpml_default_language', null);
    $original_id = apply_filters('wpml_object_id', get_the_ID(), 'page', false, $default_lang);
    if ($original_id) {
      $template = get_post_field('post_name', $original_id);
    }
  }
  get_template_part('templates/content-page', $template);
}

get_footer();
