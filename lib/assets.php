<?php

namespace Tofino\Assets;

// Load styles
function styles() {
  $main_css = '/dist/css/main.css';
  wp_register_style('tofino/css', get_template_directory_uri() . $main_css . '?v=' . filemtime(get_template_directory() . $main_css), array(), '', 'all');
  wp_enqueue_style('tofino/css');
}


/**
 * Load CSS in footer using JS function
 */
function load_css() {
  $main_css = '/dist/css/main.css'; ?>
  <script id="loadcss">
    loadCSS('<?php echo get_template_directory_uri() . $main_css . '?v=' . filemtime(get_template_directory() . $main_css); ?>', document.getElementById("loadcss"));
  </script>
  <noscript><link href="<?php echo get_template_directory_uri() . $main_css . '?v=' . filemtime(get_template_directory() . $main_css); ?>" rel="stylesheet"></noscript><?php
}


/**
 * Load CSS in header or footer
 */
function call_css() {
  // If Critical CSS enabled load CSS in the footer
  if (ot_get_option('critical_css_checkbox') && file_exists(get_template_directory() . '/dist/css/critical.css')) {
    add_action('wp_footer', __NAMESPACE__ . '\\load_css');
  } else {
    add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\styles');
  }
}
add_action('init', __NAMESPACE__ . '\\call_css');


/**
 * Inline css from critical CSS file
 */
function inline_critical_css() {
  if (ot_get_option('critical_css_checkbox') && file_exists(get_template_directory() . '/dist/css/critical.css')) {?>
    <style>
    <?php echo file_get_contents(get_template_directory_uri() . '/dist/css/critical.css'); ?>
    </style><?php
  }
}
add_action('wp_head', __NAMESPACE__ . '\\inline_critical_css');


/**
 * Load admin styles
 */
function admin_styles() {
  $admin_css = '/dist/css/wp-admin.css';
  wp_register_style('tofino/css/admin', get_template_directory_uri() . $admin_css . '?v=' . filemtime(get_template_directory() . $admin_css), array(), '', 'all');
  wp_enqueue_style('tofino/css/admin');
}
add_action('login_head', __NAMESPACE__ . '\\admin_styles');
add_action('admin_head', __NAMESPACE__ . '\\admin_styles');


/**
 * Load scripts
 */
function scripts() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    $main_js = '/dist/js/main.js';
    wp_register_script('tofino/js', get_template_directory_uri() . $main_js . '?v=' . filemtime(get_template_directory() . $main_js), array('jquery'), '', true);
    wp_enqueue_script('tofino/js');

    //Set vars for ajax and nonce
    wp_localize_script('tofino/js', 'tofinoJS', array(
      'ajaxUrl'       => admin_url('admin-ajax.php'),
      'nextNonce'     => wp_create_nonce('next_nonce'),
      'cookieExpires' => (ot_get_option('cookie_expires') ? ot_get_option('cookie_expires') : "")
    ));

    $head_js = '/dist/js/head.js';
    wp_register_script('tofino/js/head', get_template_directory_uri() . $head_js . '?v=' . filemtime(get_template_directory() . $head_js), array(), '', false);
    wp_enqueue_script('tofino/js/head');
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\scripts');

/**
 * Load admin scripts
 */
function admin_scripts() {
  $admin_js  = '/dist/js/wp-admin.js';
  wp_register_script('tofino/js/admin', get_template_directory_uri() . $admin_js . '?=' . filemtime(get_template_directory() . $admin_js), array(), '', false);
  wp_enqueue_script('tofino/js/admin');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts');
