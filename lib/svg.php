<?php

function svg($atts) {

  global $theme_config;

  if (gettype($atts) === 'string') {
    $atts = array(
      'sprite' => $atts
    );
  }

  $atts = shortcode_atts(array(
    'class'  => '',
    'title'  => '',
    'id'     => '',
    'sprite' => ''
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
