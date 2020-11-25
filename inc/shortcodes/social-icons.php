<?php
/**
 * Social icons shortcode
 *
 * @since 1.0.0
 * @param array $atts class value to include on the UL element.
 * @return string HTML output of unordered list with social icons as SVGS with links.
 */
function social_icons($atts = []) {
  $theme_mods = get_theme_mods();
  $output     = '';

  if (!empty($theme_mods['social'])) {
    $social_links = $theme_mods['social'];

    $atts = shortcode_atts([
      'class'     => '',
      'platforms' => ''
    ], $atts, 'social_icons');

    if (!empty($social_links) && (array_filter($social_links))) {
      $output .= '<ul class="social-icons ' . $atts['class'] . '">';

      // Filter the social networks based on platform param
      if (!empty($atts['platforms'])) {
        $platforms    = array_map('trim', explode(',', $atts['platforms']));
        $social_links = array_intersect_key($social_links, array_flip($platforms));
      }

      // Build the links and icons
      foreach ($social_links as $key => $value) {
        if (!empty($value)) {
          $output .= '<li><a href="' . esc_url($value) . '" target="_blank" rel="nofollow noreferrer"><span class="sr-only">' . $key . '</span>' . svg(sanitize_title($key)) . '</a></li>';
        }
      }

      $output .= '</ul>';
    }

    return $output;
  } else {
    $output .= 'Social links not found.';
    return $output;
  }
}
add_shortcode('social_icons', 'social_icons');
