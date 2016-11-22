<?php
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
    $atts = [
      'sprite' => $atts
    ];
  }

  $atts = shortcode_atts([
    'class'               => '',
    'title'               => '',
    'id'                  => '',
    'sprite'              => '',
    'preserveAspectRatio' => ''
  ], $atts, 'svg');

  if (!$atts['sprite']) {
    return false;
  }

  $sprite = $atts['sprite'];
  unset($atts['sprite']);

  $title = $atts['title'];
  unset($atts['title']);

  $atts = array_filter($atts);

  $atr_str = '';

  foreach ($atts as $key => $value) {
    $atr_str .= ' ' . $key . '="' . esc_attr($value) . '"';
  }

  return '<svg' . $atr_str . '>' . (!empty($title) ? '<title>' . $title . '</title>' : '') . '<use xlink:href="' . $theme_config['svg']['sprite_file'] . '#' . $sprite . '" /></svg>';
}
add_shortcode('svg', 'svg');
