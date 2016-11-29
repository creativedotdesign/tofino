<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\GoogleRecaptcha;

/**
 * Google settings
 *
 * reCAPTCHA etc.
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function google_recaptcha_settings($wp_customize) {
  $wp_customize->add_section('tofino_google_recaptcha', [
    'title'    => __('Google reCAPTCHA', 'tofino'),
    'priority' => 135
  ]);

  // Captcha site key
  $wp_customize->add_setting('captcha_site_key', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('captcha_site_key', [
    'label'       => __('reCAPTCHA Site Key', 'tofino'),
    'section'     => 'tofino_google_recaptcha',
    'type'        => 'text'
  ]);

  // Captcha secret
  $wp_customize->add_setting('captcha_secret', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('captcha_secret', [
    'label'       => __('reCAPTCHA Secret Key', 'tofino'),
    'section'     => 'tofino_google_recaptcha',
    'type'        => 'text'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\google_recaptcha_settings');
