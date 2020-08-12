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
}
add_action('customize_register', __NAMESPACE__ . '\\advanced_settings');
