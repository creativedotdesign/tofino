<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\SocialNetworks;

/**
 * Social icons
 *
 * Facebook, Twitter, Instagram, LinkedIn, YouTube, Pinterest, Vimeo, SoundCloud
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function social_settings($wp_customize) {
  $wp_customize->add_section('tofino_social_settings', [
    'title'       => __('Social Networks', 'tofino'),
    'description' => 'Links to various social networks. Remember to add the SVG icon for any new networks you add.',
    'priority'    => 140
  ]);

  $wp_customize->add_setting('social[facebook]', ['default' => '']);

  $wp_customize->add_control('social[facebook]', [
    'label'   => __('Facebook', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[twitter]', ['default' => '']);

  $wp_customize->add_control('social[twitter]', [
    'label'   => __('Twitter', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[instagram]', ['default' => '']);

  $wp_customize->add_control('social[instagram]', [
    'label'   => __('Instagram', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[linkedin]', ['default' => '']);

  $wp_customize->add_control('social[linkedin]', [
    'label'   => __('LinkedIn', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[pinterest]', ['default' => '']);

  $wp_customize->add_control('social[pinterest]', [
    'label'   => __('Pinterest', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[youtube]', ['default' => '']);

  $wp_customize->add_control('social[youtube]', [
    'label'   => __('YouTube', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[vimeo]', ['default' => '']);

  $wp_customize->add_control('social[vimeo]', [
    'label'   => __('Vimeo', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[soundcloud]', ['default' => '']);

  $wp_customize->add_control('social[soundcloud]', [
    'label'   => __('Soundcloud', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\social_settings');
