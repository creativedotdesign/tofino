<?php
/**
 * Shortcodes
 *
 * @package Tofino
 * @since 1.0.0
 */


/**
 * SVG Shortcode
 *
 * @since 1.0.0
 * @param mixed $atts options attributes array or string with sprite reference
 * @return string HTML SVG sprite code populated with parameters.
 */
function svg($atts) {
  global $theme_config;

  if (gettype($atts) === 'string') {
    $atts = array(
      'sprite' => $atts
    );
  }

  $atts = shortcode_atts(array(
    'class'               => '',
    'title'               => '',
    'id'                  => '',
    'sprite'              => '',
    'preserveAspectRatio' => ''
  ), $atts, 'svg');

  if (!$atts['sprite']) {
    return false;
  }

  $sprite = $atts['sprite'];

  unset($atts['sprite']);
  $atts = array_filter($atts);

  $atr_str = '';

  foreach ($atts as $key => $value) {
    $atr_str .= ' ' . $key . '="' . esc_attr($value) . '"';
  }

  return '<svg ' . $atr_str . '><use xlink:href="' . $theme_config['svg']['sprite_file'] . '#' . $sprite . '"></svg>';
}
add_shortcode('svg', 'svg');


/**
 * Copyright Shortcode
 *
 * @since 1.0.0
 * @return string HTML output copyright string.
 */
function copyright() {
  return '&copy; ' . date('Y') . ' <span class="copyright-site-name">' . get_bloginfo('name') . '</span>.';
}
add_shortcode('copyright', 'copyright');


/**
 * Optiontree option shortcode
 *
 * Get a theme option as a shortcode. Only for text based values.
 *
 * @since 1.0.0
 * @param mixed $atts option id as string or array with id and a default value.
 * @return string The option data value if found or the default value.
 */
function ot_shortcode($atts) {
  if (gettype($atts) === 'string') {
    $atts = array(
      'id' => $atts
    );
  }

  $atts = shortcode_atts(array(
    'id'      => '',
    'default' => ''
  ), $atts, 'option');

  return ot_get_option($atts['id'], $atts['default']);
}
add_shortcode('option', 'ot_shortcode');


/**
 * Social icons shortcode
 *
 * @since 1.0.0
 * @param array $atts class value to include on the UL element.
 * @return string HTML output of unordered list with social icons as SVGS with links.
 */
function social_icons($atts = array()) {
  $social_links = ot_get_option('social_links', array());
  $output = '';

  $atts = shortcode_atts(array(
    'class' => ''
  ), $atts, 'option');

  if (!empty($social_links)) {
    $output .= '<ul class="social-icons ' . $atts['class'] . '">';

    foreach ($social_links as $social_link) {
      $output .= '<li><a href="' . $social_link['href'] . '" title="' . $social_link['name'] . '">' . svg(sanitize_title($social_link['name'])) . '</a></li>';
    }

    $output .= '</ul>';
  }

  return $output;
}
add_shortcode('social_icons', 'social_icons');
