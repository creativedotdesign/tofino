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
  if (version_compare($php_version, '8.2.0', '<')) {
    wp_die('<div class="error notice"><p>' . __('PHP version >= 8.2.0 is required for this theme to work correctly.', 'tofino') . '</p></div>', 'An error occured.');
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

  // Register wp_nav_menu() menus
  register_nav_menus([
    'header_navigation' => __('Header Navigation', 'tofino'),
    'footer_navigation' => __('Footer Navigation', 'tofino')
  ]);
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
 * Adds admin body classes and dashboard-specific admin UI tweaks.
 *
 * @param string $classes Existing admin body classes.
 * @return string Updated admin body classes.
 */
function admin_body_class(string $classes)
{
  if (get_field('hide_preview_button', 'option')) {
    $classes .= ' hide-preview-button';
  }

  if (is_admin()) {
    $current_screen = get_current_screen();

    // Check if the screen is the dashboard
    if ($current_screen && 'dashboard' === $current_screen->id) {
      $classes .= ' admin-dashboard';

      // Hide the screen options tab
      add_filter('screen_options_show_screen', '__return_false');
    }
  }

  return $classes;
}
add_filter('admin_body_class', __NAMESPACE__ . '\\admin_body_class');


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
 * Toggles frontend admin bar visibility based on theme options.
 *
 * @return void
 */
function admin_bar()
{
  if (!get_field('admin_bar', 'option')) {
    add_filter('show_admin_bar', '__return_false');
  }
}
add_action('init', __NAMESPACE__ . '\\admin_bar');


/**
 * Menu Sticky
 *
 * Returns menu sticky class based on theme option setting.
 *
 * @since 1.0.0
 * @return string|null Sticky class when enabled, otherwise null.
 */
function menu_sticky()
{
  if (get_field('sticky_menu', 'option') == 1) {
    return 'sticky-top';
  }
}


/**
 * Add theme options to body class
 *
 * Adds the menu-sticky classes to the body.
 *
 * @since 1.0.0
 * @param array $classes Array of classes passed to the body tag by WP.
 * @return array Updated body classes.
 */
function add_menu_sticky_class($classes)
{
  if (get_field('sticky_menu', 'option') == 1) {
    $classes[] = 'menu-fixed';
  }
  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_menu_sticky_class');


/**
 * Dashboard Widgets
 *
 * Create WP Dashbaord Widget based on theme options
 *
 * @return void
 */
function dashboard_widgets()
{
  $widget_id = 'tofino_theme_widget';

  $widget_data = get_field('dashboard_widget', 'option');

  if ($widget_data && array_key_exists('enabled', $widget_data) && $widget_data['enabled']) {
    // Add the widget
    wp_add_dashboard_widget(
      $widget_id,
      $widget_data['title'],
      function () use ($widget_data) {
        echo '<p>' . acf_esc_html($widget_data['text']) . '</p>';
      }
    );

    // Re-order so our widget is first
    global $wp_meta_boxes;
    $widget = $wp_meta_boxes['dashboard']['normal']['core'][$widget_id];
    unset($wp_meta_boxes['dashboard']['normal']['core'][$widget_id]);
    $wp_meta_boxes['dashboard']['normal']['high'][$widget_id] = $widget;
  }
}
add_action('wp_dashboard_setup', __NAMESPACE__ . '\\dashboard_widgets');




/**
 * Turn off YYYY/MM Media folders
 *
 */
add_filter('option_uploads_use_yearmonth_folders', '__return_false', 100);


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


/**
 * Forces the excerpt metabox to remain visible for the current user.
 *
 * @return void
 */
function always_show_edit_post_show_excerpt() {
  $user = wp_get_current_user();
  $unchecked = get_user_meta($user->ID, 'metaboxhidden_post', true);

  if (!empty($unchecked)) {
    $key = array_search('postexcerpt', $unchecked);

    if (false !== $key) {
      array_splice($unchecked, $key, 1);
      update_user_meta($user->ID, 'metaboxhidden_post', $unchecked);
    }
  }
}
add_action('admin_init', __NAMESPACE__ . '\\always_show_edit_post_show_excerpt', 10);


/**
 * Ensures the excerpt metabox is not hidden on post edit screens.
 *
 * @param array     $hidden Hidden metabox IDs.
 * @param \WP_Screen $screen Current screen object.
 * @return array Updated hidden metabox IDs.
 */
function show_excerpt_meta_box($hidden, $screen) {
  if ('post' == $screen->base) {
    foreach ($hidden as $key => $value) {
      if ('postexcerpt' == $value) {
        unset($hidden[$key]);
        break;
      }
    }
  }

  return $hidden;
}
add_filter('default_hidden_meta_boxes', __NAMESPACE__ . '\\show_excerpt_meta_box', 10, 2);


/**
 * Applies hardened/default ACF and ACFE runtime settings.
 *
 * @return void
 */
function acf_update_settings() 
{
  acf_update_setting('rest_api_enabled', false);
  acf_update_setting('rest_api_embed_links', false);
  acf_update_setting('enqueue_google_maps', false);
  acf_update_setting('preload_blocks', false);
  acf_update_setting('enable_shortcode', false);
  // acf_update_setting('acfe/php', false);
  acf_update_setting('acfe/modules/block_types', false);
  acf_update_setting('acfe/modules/forms', false);
  acf_update_setting('acfe/modules/post_types', false);
  acf_update_setting('acfe/modules/taxonomies', false);
  acf_update_setting('acfe/modules/options', false);
  acf_update_setting('acfe/modules/options_pages', false);
}
add_action('acf/init', __NAMESPACE__ . '\\acf_update_settings');


/**
 * Controls whether ACF admin UI is visible.
 *
 * Option value takes priority when explicitly saved, otherwise environment
 * determines visibility.
 *
 * @return bool
 */
function acf_toggle_admin()
{
  // Read the raw saved options value here because this filter is evaluated
  // during ACF boot, before get_field('show_acf_admin', 'option') is reliable.
  $saved_value = get_option('options_show_acf_admin', null);

  // If the option has been explicitly saved, it should win regardless of
  // environment so the theme setting actually controls visibility.
  if ($saved_value !== null) {
    return (bool) $saved_value;
  }

  return in_array(wp_get_environment_type(), ['local', 'development'], true);
}
add_filter('acf/settings/show_admin', __NAMESPACE__ . '\\acf_toggle_admin');


/**
 * Limits GraphQL admin visibility to local/development environments.
 *
 * @return bool
 */
function graphql_show_admin()
{
  if (in_array(wp_get_environment_type(), ['local', 'development'])) {
    return true;
  } else {
    return false;
  }
}
add_filter('graphql_show_admin', __NAMESPACE__ . '\\graphql_show_admin');


// Disable ACF field browser
add_filter('acf/field_group/enable_field_browser', '__return_false');


/**
 * Adds SVG mime type support to uploads.
 *
 * @param array $mimes Registered mime types.
 * @return array Updated mime types.
 */
function add_svg_to_mime_types($mimes)
{
	// Allow SVG file upload
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}
add_filter('upload_mimes', __NAMESPACE__ . '\\add_svg_to_mime_types');


/**
 * Sanitizes uploaded SVG files to reduce XSS risk.
 *
 * @param array $file Upload file metadata.
 * @return array Filtered upload file metadata.
 */
function sanitize_svg_upload($file)
{
	if (($file['type'] ?? '') !== 'image/svg+xml' || !class_exists(\enshrined\svgSanitize\Sanitizer::class)) {
		return $file;
	}

	$contents = file_get_contents($file['tmp_name']);
	if ($contents === false) {
		return $file;
	}

	$sanitizer = new \enshrined\svgSanitize\Sanitizer();
	$clean = $sanitizer->sanitize($contents);

	if ($clean === false) {
		$file['error'] = __('This SVG could not be sanitized and was rejected.', 'tofino');
		return $file;
	}

	file_put_contents($file['tmp_name'], $clean);
	return $file;
}
add_filter('wp_handle_upload_prefilter', __NAMESPACE__ . '\\sanitize_svg_upload');


/**
 * Sets a 401 status code after failed login attempts.
 *
 * @return void
 */
function failed_login_401()
{
  status_header(401);
}
add_action('wp_login_failed', __NAMESPACE__ . '\\failed_login_401');


/**
 * Increases GraphQL connection query limits.
 *
 * @param int $limit Existing query limit.
 * @return int
 */
function increase_graphql_query_limit($limit)
{
  return 2000;
}
add_filter('graphql_connection_max_query_amount', __NAMESPACE__ . '\\increase_graphql_query_limit', 12, 5);
