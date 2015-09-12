<?php

namespace Tofino\Assets;

// Load styles
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\styles');

//TODO: Allow for deregistering via an array.
//TODO: Get the file names from the manifest file or assets.json
//TODO: Fuzzy match on filename to enable dev and production version (aka .min)
function styles() {
  $stylesheet_base = '/dist/css/main.css';
  wp_register_style('base',  get_template_directory_uri() . $stylesheet_base . '?v=' . filemtime(get_template_directory() . $stylesheet_base), array(), '', 'all');
  wp_enqueue_style('base'); // Enqueue it!
}

// Load scripts
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\scripts');

function scripts() {
  if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
    wp_deregister_script('jquery');

    $js_all = '/dist/js/main.js';
    wp_register_script('js-all', get_template_directory_uri() . $js_all . '?v=' . filemtime(get_template_directory() . $js_all), array(), '', true); // Custom scripts
    wp_enqueue_script('js-all'); // Enqueue it!

    //wp_register_script('init', get_template_directory_uri() . '/dist/init.js' . '?v=' . filemtime(get_template_directory() . $js_all), array(), '', false); // Init scripts
    //wp_enqueue_script('init'); // Enqueue it!
  }
}
