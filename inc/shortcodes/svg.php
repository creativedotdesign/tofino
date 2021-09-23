<?php

/**
 * SVG Shortcode
 *
 * @since 1.0.0
 * @param mixed $atts options attributes array or string with sprite reference
 * @return string HTML SVG sprite code populated with parameters.
 */
function svg($atts) {
  // SVG Sprite URL
  $svg_sprite_url = mix('dist/svg/sprite.svg', './');

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
    'preserveAspectRatio' => '',
    'file'                => ''
  ], $atts, 'svg');

  if ($atts['file']) {
    if (is_int($atts['file'])) {
      $file = get_attached_file($atts['file']);
    } else {
      $file = get_template_directory() . '/dist/svg/' . $atts['file'] . '.svg';
    }

    if (file_exists($file)) {
      return file_get_contents($file);
    }
  }

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

  return '<svg' . $atr_str . '>' . (!empty($title) ? '<title>' . $title . '</title>' : '') . '<use xlink:href="' . $svg_sprite_url . '#' . $sprite . '" /></svg>';
}
add_shortcode('svg', 'svg');