<?php

/**
 *
 * Initialize and setup theme
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Init;

/**
 * PHP version check
 *
 * @since 1.5.0
 * @return void
 */
function php_version_check()
{
  $php_version = phpversion();
  if (version_compare($php_version, '8.3.0', '<')) {
    wp_die('<div class="error notice"><p>' . __('PHP version >= 8.3.0 is required for this theme to work correctly.', 'tofino') . '</p></div>', 'An error occured.');
  }
}
add_action('after_setup_theme', __NAMESPACE__ . '\\php_version_check');


/**
 * Theme setup
 *
 * @since 1.0.0
 * @return void
 */
function setup()
{
  add_theme_support('title-tag'); // Enable plugins to manage the document title
  add_theme_support('post-thumbnails'); // Enable featured images for Posts
  add_post_type_support('page', 'excerpt'); // Enable excerpts for Pages
}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup');


/**
 * Check page display settings
 *
 * Checks if the page display is set to "Latest Posts" and if the correct template
 * file exists and displays an error if missing the home.php.
 *
 * @since 1.2.0
 * @return void
 */
function check_page_display()
{
  if ((!is_admin()) && (get_option('show_on_front') === 'posts') && (locate_template('home.php') === '')) {
    wp_die('Front page display setting is set to Latest Posts but no home.php file exists. Please update the settings selecting a Static page or create the home.php as per the documentation.', 'An error occured.');
  }
}
add_action('after_setup_theme', __NAMESPACE__ . '\\check_page_display');


/**
 * Set max content width GLOBAL
 *
 * @since 1.0.0
 * @return void
 */
function content_width()
{
  $GLOBALS['content_width'] = apply_filters(__NAMESPACE__ . '\\content_width', 1440);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\content_width', 0);


/**
 * Add post_type and post_name to body class
 *
 * @since 1.0.0
 * @param array $classes array of current classes on the body tag
 * @return array updated to include the post_type and post_name
 */
function add_post_name_body_class(array $classes)
{
  global $post;
  if (isset($post) && is_single()) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }

  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array($post->post_name, $classes)) {
      $classes[] = $post->post_name;
    }
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_post_name_body_class');



/**
 * Add Google Tag Manager function call which is
 * supposed to be placed after opening body tag.
 *
 * @since 3.3.0
 */
function add_custom_body_open_code()
{
  if (function_exists('gtm4wp_the_gtm_tag')) {
    gtm4wp_the_gtm_tag();
  }
}
add_action('wp_body_open', __NAMESPACE__ . '\\add_custom_body_open_code');


/**
 * Wraps oEmbed output in a responsive aspect-ratio container.
 *
 * @param string $html Original embed HTML.
 * @return string Wrapped embed HTML.
 */
function video_embed_wrapper($html)
{
  $html = '<div class="relative my-6 aspect-video">' . $html . '</div>';

  return $html;
}
add_filter('embed_oembed_html', __NAMESPACE__ . '\\video_embed_wrapper', 10, 4);


/**
 * Sets the default excerpt length.
 *
 * @param int $length Existing excerpt length.
 * @return int
 */
function truncate_excerpt_length($length)
{
  return 55;
}
add_filter('excerpt_length', __NAMESPACE__ . '\\truncate_excerpt_length');


/**
 * Flushes object cache after ACF options save events.
 *
 * @param string|int $post_id Saved post ID or options identifier.
 * @return void
 */
function clear_cache_options_save($post_id)
{
  // Check if it's an options page
	if ($post_id === 'options') {
		// Flush the object cache
		wp_cache_flush();
	}
}
add_action('acf/save_post', __NAMESPACE__ . '\\clear_cache_options_save', 20);
