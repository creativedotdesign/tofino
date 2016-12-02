<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\Advanced;

/**
 * Advacned settings
 *
 * Inline critical css, move jQuery to footer etc.
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function advanced_settings($wp_customize) {
  $wp_customize->add_section('tofino_advanced_settings', [
    'title'    => __('Advanced', 'tofino'),
    'priority' => 150
  ]);

  $wp_customize->add_setting('no_fout', ['default' => '']);

  $wp_customize->add_control('no_fout', [
    'label'       => __('No FOUT', 'tofino'),
    'description' => __('Enable the body class to remove the FOUT (Flash of unstyled text).', 'tofino'),
    'section'     => 'tofino_advanced_settings',
    'type'        => 'checkbox'
  ]);

  $wp_customize->add_setting('critical_css', ['default' => '']);

  $wp_customize->add_control('critical_css', [
    'label'       => __('Enable Critical CSS', 'tofino'),
    'description' => __('Inject the critical.css file as inline styles in the head tag. Defer the main CSS file in to loadCSS in the footer. Remember to run the styles:critical gulp task.', 'tofino'),
    'section'     => 'tofino_advanced_settings',
    'type'        => 'checkbox'
  ]);

  $wp_customize->add_setting('jquery_footer', ['default' => '']);

  $wp_customize->add_control('jquery_footer', [
    'label'       => __('Move jQuery to Footer', 'tofino'),
    'description' => __('Move jQuery to the footer. Uncheck if you have compatibility issues with plugins.', 'tofino'),
    'section'     => 'tofino_advanced_settings',
    'type'        => 'checkbox'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\advanced_settings');
