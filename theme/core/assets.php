<?php

/**
 * Load CSS and JS files
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Assets;


/**
 * Enqueues the main front-end Vite assets.
 *
 * @since 1.1.0
 *
 * @return void
 */
function main_script(): void
{
  if ($GLOBALS['pagenow'] === 'wp-login.php' || is_admin()) {
    return;
  }

  \Tofino\Vite::use_vite();
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\main_script', 10);


/**
 * Passes PHP data to front-end JS via an inline script.
 *
 * @since 1.1.0
 *
 * @return void
 */
function localize_scripts(): void
{
  if ($GLOBALS['pagenow'] === 'wp-login.php' || is_admin()) {
    return;
  }

  $data = [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nextNonce' => wp_create_nonce('next_nonce'),
    'themeUrl' => get_template_directory_uri(),
    'siteURL' => home_url(),
  ];

  if (function_exists('wpml_object_id_filter')) {
    $data['language'] = apply_filters('wpml_current_language', null);
  }

  if (function_exists('graphql_get_endpoint')) {
    $data['graphqlEndpoint'] = graphql_get_endpoint();
  }

  $iframe_resizer_license = get_field('iframe_resizer_license_key', 'option');

  if ($iframe_resizer_license) {
    $data['iframeResizerLicense'] = $iframe_resizer_license;
  }

  wp_register_script('tofino-data', false);
  wp_enqueue_script('tofino-data');
  wp_add_inline_script(
    'tofino-data',
    'const tofinoJS = ' . wp_json_encode($data) . ';',
  );
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\localize_scripts', 10);


/**
 * Enqueues admin and login Vite assets.
 *
 * @since 1.0.0
 *
 * @return void
 */
function admin_scripts(): void
{
  \Tofino\Vite::use_vite('js/admin.ts');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts');
add_action('login_head', __NAMESPACE__ . '\\admin_scripts');


/**
 * Removes unused default image sizes.
 *
 * @since 3.2.0
 *
 * @return void
 */
function remove_unused_image_sizes(): void
{
  remove_image_size('1536x1536');
}
add_action('init', __NAMESPACE__ . '\\remove_unused_image_sizes');


/**
 * Sets custom image size dimensions on theme activation.
 *
 * @since 3.2.0
 *
 * @return void
 */
function set_image_sizes(): void
{
  update_option('thumbnail_size_w', 250);
  update_option('thumbnail_size_h', 0);

  update_option('medium_size_w', 565);
  update_option('medium_size_h', 0);

  update_option('medium_large_size_w', 0);
  update_option('medium_large_size_h', 0);

  update_option('large_size_w', 1152);
  update_option('large_size_h', 0);

  update_option('2048x2048_size_w', 2048);
  update_option('2048x2048_size_h', 0);
}
add_action('after_switch_theme', __NAMESPACE__ . '\\set_image_sizes');


/**
 * Populates image attachment metadata from the filename and EXIF data on upload.
 *
 * Sets the post title from the filename, and extracts the copyright
 * and image description from EXIF data if available.
 *
 * @since 3.2.0
 *
 * @param int $post_id The attachment post ID.
 * @return void
 */
function populate_img_meta(int $post_id): void
{
  if (!str_starts_with((string) get_post_mime_type($post_id), 'image/')) {
    return;
  }

  $file_path = get_attached_file($post_id);

  if (!$file_path) {
    return;
  }

  $post_title = pathinfo($file_path, PATHINFO_FILENAME);

  wp_update_post([
    'ID' => $post_id,
    'post_excerpt' => '',
    'post_title' => $post_title,
  ]);

  if (!function_exists('exif_read_data') || !in_array(mime_content_type($file_path), ['image/jpeg', 'image/tiff'], true)) {
    return;
  }

  $exif = @exif_read_data($file_path);

  if (!$exif) {
    return;
  }

  if (!empty($exif['Copyright'])) {
    $credit = wp_slash(wp_strip_all_tags($exif['Copyright']));
    if ($credit) {
      update_field('media_credit', $credit, $post_id);
    }
  }

  if (!empty($exif['ImageDescription'])) {
    $alt = wp_slash(wp_strip_all_tags($exif['ImageDescription']));
    if ($alt) {
      update_post_meta($post_id, '_wp_attachment_image_alt', $alt);
    }
  }
}
add_filter('add_attachment', __NAMESPACE__ . '\\populate_img_meta');


/**
 * Outputs the SVG spritemap inline in the footer.
 *
 * @since 3.2.0
 *
 * @return void
 */
function add_svg_sprite_to_footer(): void
{
  $svg_sprite = get_template_directory() . '/dist/sprite.svg';

  if (file_exists($svg_sprite)) {
    echo file_get_contents($svg_sprite);
  }
}
add_action('wp_footer', __NAMESPACE__ . '\\add_svg_sprite_to_footer');
