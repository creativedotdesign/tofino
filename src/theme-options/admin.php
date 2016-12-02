<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\Admin;

/**
 * Admin settings
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function admin_settings($wp_customize) {
  $wp_customize->add_section('tofino_admin_settings', [
    'title'    => __('Admin', 'tofino'),
    'priority' => 160
  ]);

  $wp_customize->add_setting('admin_logo', ['default' => '']);

  $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, 'admin_logo', [
    'label'       => __('Admin Login Logo', 'tofino'),
    'description' => __('You might need to add some additional css to tweak the logo size / position. Add your CSS in to the file assets/styles/base/wp-admin.scss.'),
    'section'     => 'tofino_admin_settings',
    'settings'    => 'admin_logo'
  ]));
}
add_action('customize_register', __NAMESPACE__ . '\\admin_settings');


/**
 * Admin login logo
 *
 * Displays the logo uplaoded via theme options to the login screen.
 *
 * @since 1.0.0
 * @return void
 */
function admin_login_logo() {
  $admin_logo = get_theme_mod('admin_logo');
  if ($admin_logo) { ?>
    <style type="text/css">
      .login h1 a {
        background-image: url(<?php echo $admin_logo; ?>);
        padding-bottom: 30px;
      }
    </style><?php
  }
}
add_action('login_head', __NAMESPACE__ . '\\admin_login_logo');
