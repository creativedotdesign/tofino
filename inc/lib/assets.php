<?php

/**
 * Load CSS and JS files
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Assets;


/**
 * Main JS script
 *
 * Register and enqueue the mains js used in front end.
 *
 * @since 1.1.0
 * @return void
 */
function main_script()
{
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    \Tofino\Vite::useVite();
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\main_script');


/**
 * Localize script
 *
 * Make data available to JS scripts via global JS variables.
 *
 * @link https://codex.wordpress.org/Function_Reference/wp_localize_script
 * @since 1.1.0
 * @return void
 */
function localize_scripts()
{
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    $alerts = get_field('alerts', 'option');

    if ($alerts) {
      $expires = [];

      $i = 1;
      foreach ($alerts as $alert) {
        // Get the expires from each alert and add to array.
        $expires[$i] = $alert['expires'];
        $i++;
      }
    }

    $data = [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nextNonce' => wp_create_nonce('next_nonce'),
      'cookieExpires' => isset($expires) ? $expires : null,
      'themeUrl' => get_template_directory_uri(),
      'siteURL' => site_url(),
    ];

    // Add WPML language code to JS
    if (function_exists('icl_object_id')) {
      $data['language'] = apply_filters('wpml_current_language', null);
    }

    wp_localize_script('tofino', 'tofinoJS', $data);
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\localize_scripts');


/**
 * Load admin scripts
 *
 * Register and enqueue the scripts and css used in the admin area.
 *
 * @since 1.0.0
 * @return void
 */
function admin_scripts()
{
  \Tofino\Vite::useVite('js/admin.ts');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts');
add_action('login_head', __NAMESPACE__ . '\\admin_scripts');


/**
 * Correct Image Sizes
 *
 * Set the images sizes to ones we really use.
 *
 * @since 3.2.0
 * @return void
 */
function correct_image_sizes()
{
  remove_image_size('medium_large');
  remove_image_size('large');
  remove_image_size('1536x1536');

  update_option('thumbnail_size_h', 0);
  update_option('thumbnail_size_w', 250);

  update_option('medium_size_h', 0);
  update_option('medium_size_w', 565);

  update_option('medium_large_size_h', 0);
  update_option('medium_large_size_w', 0);

  update_option('large_size_h', 0);
  update_option('large_size_w', 1152);

  update_option('1536x1536_size_h', 0);
  update_option('1536x1536_size_w', 0);

  update_option('2048x2048_size_h', 0);
  update_option('2048x2048_size_w', 2048);
}
add_action('init', __NAMESPACE__ . '\\correct_image_sizes');


// Automatically populate image attachment metadata
function populate_img_meta($post_id) {
  // Only run if the attachment is an image
  if (get_post_mime_type($post_id) !== 'image/jpeg') {
    return;
  }

  // set post title to be the file name
  $post_title = basename(get_attached_file($post_id));

  // Remove the file extension from the post title
  $post_title = preg_replace('/\\.[^.\\s]{3,4}$/', '', $post_title);

  $attachment_post = [
    'ID' => $post_id,
    'post_excerpt' => '', // Empty the caption
    'post_title' => $post_title
  ];
 
  wp_update_post($attachment_post);

  // Get EXIF data from the attachment file
  $exif = exif_read_data(get_attached_file($post_id));

  if ($exif) {
    if (array_key_exists('Copyright', $exif)) {
      $exif_credit = wp_slash(wp_strip_all_tags($exif['Copyright']));
  
      // Update the Media Credit
      if (!empty($exif_credit)) {
        update_field('media_credit', $exif_credit, $post_id);
      }
    }
  
    if (array_key_exists('ImageDescription', $exif)) {
      $exif_img_alt = wp_slash(wp_strip_all_tags($exif['ImageDescription']));
  
      // Update the alternative text, which is stored in post meta table
      if (!empty($exif_img_alt)) {
        update_post_meta($post_id, '_wp_attachment_image_alt', $exif_img_alt);
      }
    }
  }
}
add_filter('add_attachment', __NAMESPACE__ . '\\populate_img_meta');
