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

  // Add no-fount class if theme option set to true
  if (get_field('no_fout', 'option')) {
    $classes[] = 'no-fout';
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



function admin_body_class(string $classes)
{
  if (get_field('hide_preview_button', 'option')) {
    $classes .= ' hide-preview-button';
  }

  if (get_field('auto_generate_page_modules', 'option')) {
    $classes .= ' auto-generate-page-modules';
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


// Show or hide the admin bar
function admin_bar()
{
  if (!get_field('admin_bar', 'option')) {
    add_filter('show_admin_bar', '__return_false');
  }
}
add_action('init', __NAMESPACE__ . '\\admin_bar');


/**
 * Show maintenance mode message in admin area
 *
 * Check the maintenance_mode_enabled Theme Option. If enabled display a notice in
 * the admin area at the top of every screen. Also show a popup window to the user
 * and set a cookie.
 *
 * @since 1.0.0
 * @return void
 */
function show_maintenance_message()
{
  if (get_field('maintenance_mode', 'option')) {
    echo '<div class="error notice"><p><strong>' . __('Maintenance Mode', 'tofino') . '</strong> ' . get_field('maintenance_mode_text', 'option') . '</p></div>';

    if (!isset($_COOKIE['tofino_maintenance_alert_dismissed'])) {
      echo '<div class="maintenance-mode-alert"><div><h1>' . __('Maintenance Mode', 'tofino') . '</h1><p>' . get_field('maintenance_mode_text', 'option') . '</p><button type="button">' . __('I understand', 'tofino') . '</button></div></div>';
    }
  }
}
add_action('admin_notices', __NAMESPACE__ . '\\show_maintenance_message');


/**
 * Alerts
 *
 * Display Alerts Top/Bottom based on theme option setting.
 *
 * @since 1.0.0
 * @param string $position The position of the alert e.g. Top, Bottom
 * @return void
 */
function alerts($position)
{
  $alerts = get_field('alerts', 'option');

  if ($alerts) {
    $i = 1;

    foreach ($alerts as $alert) {
      if ($alert['enabled']) {
        $alert_position = strtolower($alert['position']);

        if ($alert['message'] && !isset($_COOKIE['tofino-alert-' . $i . '-closed']) && $position === $alert_position) {
          \Tofino\Helpers\hm_get_template_part('templates/partials/alert', [
            'position' => $alert_position,
            'message' => $alert['message'],
            'id' => $i
          ]);
        }
      }

      $i++;
    }
  }
}


/**
 * Menu Sticky
 *
 * Returns menu sticky class based on theme option setting.
 *
 * @since 1.0.0
 * @return void
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
 * @return void
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
        echo '<p>' . $widget_data['text'] . '</p>';
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


// Set ACF JSON save path
function acf_json_save_point($path)
{
  $path = get_stylesheet_directory() . '/inc/acf-json'; // Update path

  return $path;
}
add_filter('acf/settings/save_json', __NAMESPACE__ . '\\acf_json_save_point');


// Set ACF JSON load path
function acf_json_load_point($paths)
{
  unset($paths[0]); // Remove original path (optional)

  $paths[] = get_stylesheet_directory() . '/inc/acf-json';

  return $paths;
}
add_filter('acf/settings/load_json', __NAMESPACE__ . '\\acf_json_load_point');


/**
 * Turn off YYYY/MM Media folders
 *
 */
add_filter('option_uploads_use_yearmonth_folders', '__return_false', 100);


// Responsive Embed
function video_embed_wrapper($html)
{
  $html = '<div class="relative my-6 aspect-video">' . $html . '</div>';

  return $html;
}
add_filter('embed_oembed_html', __NAMESPACE__ . '\\video_embed_wrapper', 10, 4);


// Truncate excerpt length
function truncate_excerpt_length($length)
{
  return 55;
}
add_filter('excerpt_length', __NAMESPACE__ . '\\truncate_excerpt_length');


// Clear cache on options save
function clear_cache_options_save($post_id)
{
  // Check if it's an options page
	if ($post_id === 'options') {
		// Flush the object cache
		wp_cache_flush();
	}
}
add_action('acf/save_post', __NAMESPACE__ . '\\clear_cache_options_save', 20);


// Add excerpt to pages always
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


// Add excerpt to pages always
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


// Update ACF settings
function acf_update_settings() 
{
  acf_update_setting('rest_api_enabled', false);
  acf_update_setting('rest_api_embed_links', false);
  acf_update_setting('enqueue_google_maps', false);
  acf_update_setting('preload_blocks', false);
  acf_update_setting('enable_shortcode', false);
  acf_update_setting('acfe/php', false);
  acf_update_setting('acfe/modules/block_types', false);
  acf_update_setting('acfe/modules/forms', false);
  acf_update_setting('acfe/modules/post_types', false);
  acf_update_setting('acfe/modules/taxonomies', false);
  acf_update_setting('acfe/modules/options', false);
  acf_update_setting('acfe/modules/options_pages', false);
}
add_action('acf/init', __NAMESPACE__ . '\\acf_update_settings');


// Toggle ACF admin based on environment and ACF Option
function acf_toggle_admin()
{
  if (in_array(wp_get_environment_type(), ['local', 'development']) || get_field('show_acf_admin', 'option')) {
    return true;
  } else {
    return false;
  }
}
add_filter('acf/settings/show_admin', __NAMESPACE__ . '\\acf_toggle_admin');


// Toggle GraphQL admin based on environment
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


// Clear maintenance mode cookie
function maintenance_mode_clear_cookie($value, $post_id, $field, $original)
{
  if (!$value) {
    // Delete the cookie
    setcookie('tofino_maintenance_alert_dismissed', '', time() - 3600, '/');
  }

  // Check value has changed from false to true
  if ($value && !$original) {
    // Delete the cookie
    setcookie('tofino_maintenance_alert_dismissed', '', time() - 3600, '/');
  }

  return $value;
}
add_action('acf/update_value/name=maintenance_mode', __NAMESPACE__ . '\\maintenance_mode_clear_cookie', 10, 4);


// Add SVG to allowed mime types
function add_svg_to_mime_types($mimes)
{
	// Allow SVG file upload
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}
add_filter('upload_mimes', __NAMESPACE__ . '\\add_svg_to_mime_types');


function failed_login_401()
{
  status_header(401);
}
add_action('wp_login_failed', __NAMESPACE__ . '\\failed_login_401');


// Increase GraphQL query limit
function increase_graphql_query_limit($limit)
{
  return 2000;
}
add_filter('graphql_connection_max_query_amount', __NAMESPACE__ . '\\increase_graphql_query_limit', 12, 5);
