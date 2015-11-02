<?php

namespace Tofino\Assets;

// Load styles
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\styles');

/**
 * @todo: Allow for deregistering via an array
 * @todo: Maybe replace filemtime with another solution
 */
function styles() {
  $stylesheet_base = '/dist/css/main.css';
  wp_register_style('base', get_template_directory_uri() . $stylesheet_base . '?v=' . filemtime(get_template_directory() . $stylesheet_base), array(), '', 'all');
  wp_enqueue_style('base'); // Enqueue it!
}


/**
 * Load admin styles
 */
add_action('login_head', __NAMESPACE__ . '\\admin_styles');
add_action('admin_head', __NAMESPACE__ . '\\admin_styles');

function admin_styles() {
  $stylesheet_base = '/dist/css/wp-admin.css';
  wp_register_style('admin', get_template_directory_uri() . $stylesheet_base . '?v=' . filemtime(get_template_directory() . $stylesheet_base), array(), '', 'all');
  wp_enqueue_style('admin');
}


/**
 * Load scripts
 */
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\scripts');

function scripts() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    wp_deregister_script('jquery');
    wp_register_script('jquery', get_template_directory_uri() . '/dist/js/jquery.js', false, null);
    wp_enqueue_script('jquery');

    $js_all = '/dist/js/main.js';
    wp_register_script('js-all', get_template_directory_uri() . $js_all . '?v=' . filemtime(get_template_directory() . $js_all), array('jquery'), '', true); // Custom scripts
    wp_enqueue_script('js-all'); // Enqueue it!

    //Set vars for ajax and nonce
    wp_localize_script('js-all', 'tofinoJS', array(
      'ajaxUrl'       => admin_url('admin-ajax.php'),
      'nextNonce'     => wp_create_nonce('next_nonce'),
      'cookieExpires' => (ot_get_option('cookie_expires') ? ot_get_option('cookie_expires') : "")
    ));

    $js_head = '/dist/js/head.js';
    wp_register_script('init', get_template_directory_uri() . '/dist/js/head.js' . '?v=' . filemtime(get_template_directory() . $js_head), array(), '', false); // Head scripts
    wp_enqueue_script('init'); // Enqueue it!
  }
}
