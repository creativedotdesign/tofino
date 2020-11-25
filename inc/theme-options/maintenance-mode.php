<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\Maintenance;

/**
 * Maintenance mode settings
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function maintenance_settings($wp_customize) {
  $wp_customize->add_section('tofino_maintenance_settings', [
    'title'    => __('Maintenance Mode', 'tofino'),
    'priority' => 155
  ]);

  $wp_customize->add_setting('maintenance_mode', ['default' => '']);

  $wp_customize->add_control('maintenance_mode', [
    'label'       => __('Maintenance Mode', 'tofino'),
    'description' => __('Enabling maintenance mode shows a message on each page in the admin area.', 'tofino'),
    'section'     => 'tofino_maintenance_settings',
    'type'        => 'checkbox'
  ]);

  $wp_customize->add_setting('maintenance_mode_text', ['default' => __('This site is currently in maintenance mode. Any changes you make may be overwritten or removed.', 'tofino')]);

  $wp_customize->add_control('maintenance_mode_text', [
    'label'       => __('Maintenance Mode Text', 'tofino'),
    'description' => __('Notification is shown until dismissed (at which point a cookie is set).', 'tofino'),
    'section'     => 'tofino_maintenance_settings',
    'type'        => 'textarea'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\maintenance_settings');


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
function show_maintenance_message() {
  if (get_theme_mod('maintenance_mode') === true) {?>
    <div class="error notice">
      <p><strong><?php echo __('Maintenance Mode', 'tofino') . '</strong> - ' . get_theme_mod('maintenance_mode_text', __('This site is currently in maintenance mode. Any changes you make may be overwritten or removed.', 'tofino')); ?></p>
    </div><?php

    if (!isset($_COOKIE['tofino_maintenance_alert_dismissed'])) {
      echo '<div class="maintenance-mode-alert"><h1>' . __('Maintenance Mode', 'tofino') . '</h1><p>' . get_theme_mod('maintenance_mode_text', __('This site is currently in maintenance mode. Any changes you make may be overwritten or removed.', 'tofino')) . '</p><button>' . __('I understand', 'tofino') . '</button></div>';
    }
  }
}
add_action('admin_notices', __NAMESPACE__ . '\\show_maintenance_message');
