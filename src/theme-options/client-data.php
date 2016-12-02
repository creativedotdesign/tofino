<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\ClientData;

/**
 * Client data settings
 *
 * Commonly used data. Tel number, company number, address etc.
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function client_data_settings($wp_customize) {
  $wp_customize->add_section('tofino_client_data_settings', [
    'title'    => __('Client Data', 'tofino'),
    'priority' => 125
  ]);

  // Telephone number
  $wp_customize->add_setting('telephone_number', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('telephone_number', [
    'label'   => __('Telephone Number', 'tofino'),
    'section' => 'tofino_client_data_settings',
    'type'    => 'text'
  ]);

  // Email address
  $wp_customize->add_setting('email_address', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_email',
  ]);

  $wp_customize->add_control('email_address', [
    'label'   => __('Email address', 'tofino'),
    'section' => 'tofino_client_data_settings',
    'type'    => 'text'
  ]);

  // Address
  $wp_customize->add_setting('address', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\Helpers\sanitize_textarea',
  ]);

  $wp_customize->add_control('address', [
    'label'   => __('Address', 'tofino'),
    'section' => 'tofino_client_data_settings',
    'type'    => 'textarea'
  ]);

  // Company number
  $wp_customize->add_setting('company_number', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('company_number', [
    'label'   => __('Company number', 'tofino'),
    'section' => 'tofino_client_data_settings',
    'type'    => 'text'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\client_data_settings');
