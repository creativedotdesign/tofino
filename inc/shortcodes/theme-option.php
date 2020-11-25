<?php
/**
 * Theme option shortcode
 *
 * Get a theme option as a shortcode. Only for text based values.
 *
 * @since 1.0.0
 * @param mixed $atts option id as string or array with id and a default value.
 * @return string The option data value if found or the default value.
 */
function ot_shortcode($atts) {
  if (gettype($atts) === 'string') {
    $atts = [
      'id' => $atts
    ];
  }

  $atts = shortcode_atts([
    'id'      => '',
    'default' => ''
  ], $atts, 'option');

  return get_theme_mod($atts['id'], $atts['default']);
}
add_shortcode('option', 'ot_shortcode');
