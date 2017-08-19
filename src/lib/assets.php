<?php
/**
 * Load CSS and JS files
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Assets;

/**
 * Load styles
 *
 * Register and enqueue the main stylesheet.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 * Called using call_css() function.
 *
 * @see call_css()
 * @since 1.0.0
 * @return void
 */
function styles() {
  $main_css = '/dist/css/main.css';
  wp_register_style('tofino/css', get_template_directory_uri() . $main_css . '?v=' . filemtime(get_template_directory() . $main_css));
  wp_enqueue_style('tofino/css');
}


/**
 * Load main CSS using javascript
 *
 * Loads the main CSS file asynchronously using the loadCSS javascript function (filamentgroup).
 *
 * @link https://github.com/filamentgroup/loadCSS
 * @see call_css()
 * @since 1.1.0
 * @return void
 */
function load_css() {
  $main_css = '/dist/css/main.css'; ?>
  <script id="loadcss">
    loadCSS('<?php echo get_template_directory_uri() . $main_css . '?v=' . filemtime(get_template_directory() . $main_css); ?>', document.getElementById("loadcss"));
  </script>
  <noscript><link href="<?php echo get_template_directory_uri() . $main_css . '?v=' . filemtime(get_template_directory() . $main_css); ?>" rel="stylesheet"></noscript><?php
}


/**
 * Load main CSS in header or footer
 *
 * Checks the critical CSS theme option. If true and critical.css file exists
 * then add the load_css function to the wp_footer action. Otherwise add the styles
 * function to the wp_enqueue_scripts action which adds the main CSS in the head tags.
 *
 * @uses 'wp_footer'
 * @uses 'wp_enqueue_scripts'
 * @since 1.1.0
 * @return void
 */
function call_css() {
  if (get_theme_mod('critical_css') && file_exists(get_template_directory() . '/dist/css/critical.css')) {
    add_action('wp_footer', __NAMESPACE__ . '\\load_css');
  } else {
    add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\styles');
  }
}
add_action('init', __NAMESPACE__ . '\\call_css');


/**
 * Inline CSS from critical CSS file
 *
 * Check the critical CSS theme option. If true and critical.css file exists
 * output the file contents of critical.css between styles tags and add to wp_head action.
 *
 * @since 1.1.0
 * @return void
 */
function inline_critical_css() {
  if (get_theme_mod('critical_css') && file_exists(get_template_directory() . '/dist/css/critical.css')) {?>
    <style>
    <?php echo file_get_contents(get_template_directory_uri() . '/dist/css/critical.css'); ?>
    </style><?php
  }
}
add_action('wp_head', __NAMESPACE__ . '\\inline_critical_css');


/**
 * Load admin styles
 *
 * Register and enqueue the stylesheet used in the admin area.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 * Function added to both the login_head (Login page) and admin_head (Admin pages)
 *
 * @since 1.0.0
 * @return void
 */
function admin_styles() {
  $admin_css = '/dist/css/wp-admin.css';
  wp_register_style('tofino/css/admin', get_template_directory_uri() . $admin_css . '?v=' . filemtime(get_template_directory() . $admin_css));
  wp_enqueue_style('tofino/css/admin');
}
add_action('login_head', __NAMESPACE__ . '\\admin_styles');
add_action('admin_head', __NAMESPACE__ . '\\admin_styles');


/**
 * Main JS script
 *
 * Register and enqueue the mains js used in front end.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 *
 * @since 1.1.0
 * @return void
 */
function main_script() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    $main_js = '/dist/js/main.js';
    wp_register_script('tofino/js', get_template_directory_uri() . $main_js . '?v=' . filemtime(get_template_directory() . $main_js), ['jquery'], null, true);
    wp_enqueue_script('tofino/js');
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\main_script');


/**
 * Head JS script
 *
 * Register and enqueue the head js used in front end.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 *
 * @since 1.1.0
 * @return void
 */
function head_script() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    $head_js = '/dist/js/head.js';
    wp_register_script('tofino/js/head', get_template_directory_uri() . $head_js . '?v=' . filemtime(get_template_directory() . $head_js));
    wp_enqueue_script('tofino/js/head');
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\head_script');


/**
 * Localize script
 *
 * Make data available to JS scripts via global JS variables.
 *
 * @link https://codex.wordpress.org/Function_Reference/wp_localize_script
 * @since 1.1.0
 * @return void
 */
function localize_scripts() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    wp_localize_script('tofino/js', 'tofinoJS', [
      'ajaxUrl'        => admin_url('admin-ajax.php'),
      'nextNonce'      => wp_create_nonce('next_nonce'),
      'cookieExpires'  => (get_theme_mod('notification_expires') ? get_theme_mod('notification_expires'): 999),
      'themeUrl'       => get_template_directory_uri(),
      'notificationJS' => (get_theme_mod('notification_use_js') ? 'true' : 'false')
    ]);
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\localize_scripts');


/**
 * Move jQuery to the footer
 *
 * Check the jQuery move to footer Theme Option. If checked degresiter jQuery
 * and re-register it in the footer. Only in the front end (Not admin).
 *
 * @since 1.1.0
 * @return void
 */
function jquery_footer() {
  if (get_theme_mod('jquery_footer')) {
    if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
      wp_deregister_script('jquery');
      wp_register_script('jquery', includes_url('/js/jquery/jquery.js'), false, null, true);
      wp_enqueue_script('jquery');
    }
  }
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\jquery_footer');


/**
 * Load admin scripts
 *
 * Register and enqueue the scripts used in the admin area.
 * Filemtime added as a querystring to ensure correct version is sent to the client.
 *
 * @since 1.0.0
 * @return void
 */
function admin_scripts() {
  $admin_js = '/dist/js/wp-admin.js';
  wp_register_script('tofino/js/admin', get_template_directory_uri() . $admin_js . '?=' . filemtime(get_template_directory() . $admin_js));
  wp_enqueue_script('tofino/js/admin');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts');
