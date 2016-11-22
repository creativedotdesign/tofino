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


/**
 * Create WP Customizer panel
 *
 * Create new panel in WP Customizer for Theme options
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function create_panel($wp_customize) {
  $wp_customize->add_panel('tofino_options', [
    'title' => __('Theme Options', 'tofino')
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\create_panel');
