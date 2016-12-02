<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions;

/**
 * Add theme options link
 *
 * Add the Customize link to the admin menu.
 *
 * @since 1.2.0
 * @return void
 */
function add_theme_options_link() {
  add_menu_page('Theme Options', 'Theme Options', 'edit_theme_options', 'customize.php');
}
add_action('admin_menu', __NAMESPACE__ . '\\add_theme_options_link');
