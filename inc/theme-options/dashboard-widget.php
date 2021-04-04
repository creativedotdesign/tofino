<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\DashboardWidgets;

/**
 * Dashboard Widget settings
 *
 * @since 1.8.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function widget_settings($wp_customize) {
  $wp_customize->add_section('tofino_dash_widget_settings', [
    'title'    => __('Dashboard Widget', 'tofino'),
    'priority' => 160
  ]);

  $wp_customize->add_setting('dash_widget_title', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('dash_widget_title', [
    'label'   => __('Widget Title', 'tofino'),
    'section' => 'tofino_dash_widget_settings',
    'type'    => 'text'
  ]);

  $wp_customize->add_setting('dash_widget_text', [
    'default'           => '',
    'sanitize_callback' => 'wp_kses_data',
  ]);

  $wp_customize->add_control('dash_widget_text', [
    'label'   => __('Widget Text', 'tofino'),
    'section' => 'tofino_dash_widget_settings',
    'type'    => 'textarea'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\widget_settings');


/**
 * Dashboard Widgets
 *
 * Create WP Dashbaord Widget based on theme options
 *
 * @return void
 */
function dashboard_widgets() {
  $widget_id = 'tofino_theme_widget';

  // Add the widget
  wp_add_dashboard_widget(
    $widget_id,
    get_theme_mod('dash_widget_title', __('Theme Support', 'tofino')),
    __NAMESPACE__ . '\\get_widget_content'
  );

  // Re-order so our widget is first
  global $wp_meta_boxes;
  $widget = $wp_meta_boxes['dashboard']['normal']['core'][$widget_id];
  unset($wp_meta_boxes['dashboard']['normal']['core'][$widget_id]);
  $wp_meta_boxes['dashboard']['normal']['high'][$widget_id] = $widget;
}
add_action('wp_dashboard_setup', __NAMESPACE__ . '\\dashboard_widgets');


/**
 * Get Widget Content
 *
 * @return The widget content from the theme option or default text.
 */
function get_widget_content() {
  echo get_theme_mod('dash_widget_text', __('<a href ="https://github.com/lambdacreatives/tofino">Tofino</a> theme by <a href ="https://creativedotdesign.com/">Creative Dot</a>.', 'tofino'));
}
