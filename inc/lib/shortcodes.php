<?php

/**
 * Social icons shortcode
 *
 * @since 1.0.0
 * @param array $atts class value to include on the UL element.
 * @return string HTML output of unordered list with social icons as SVGS with links.
 */
function social_icons($atts = [])
{
  $output = '';

  $atts = shortcode_atts([
    'class'     => '',
    'platforms' => ''
  ], $atts, 'social_icons');

  $social_links = get_field('social_media_links', 'general-options');

  if (!empty($social_links) && (array_filter($social_links))) {
    $output .= '<ul class="social-icons' . ($atts['class'] ? ' ' . $atts['class'] : null) . '">';

    // Filter the social networks based on platform param
    if (!empty($atts['platforms'])) {
      $platforms    = array_map('trim', explode(',', $atts['platforms']));
      $social_links = array_intersect_key($social_links, array_flip($platforms));
      $social_links = array_replace(array_flip($platforms), $social_links);
    }

    // Build the links and icons
    foreach ($social_links as $key => $value) {
      if (!empty($value)) {
        $output .= '<li><a href="' . esc_url($value) . '" target="_blank" rel="nofollow noreferrer"><span class="sr-only">' . $key . '</span>' . svg(sanitize_title($key)) . '</a></li>';
      }
    }

    $output .= '</ul>';
  } else {
    $output .= 'Social links not found.';
  }

  return $output;
}
add_shortcode('social_icons', 'social_icons');


/**
 * Copyright Shortcode
 *
 * @since 1.0.0
 * @return string HTML output copyright string.
 */
function copyright()
{
  return '&copy; ' . date('Y');
}
add_shortcode('copyright', 'copyright');


/**
 * SVG Shortcode
 *
 * @since 1.0.0
 * @param mixed $atts options attributes array or string with sprite reference
 * @return string HTML SVG sprite code populated with parameters.
 */
function svg($atts)
{
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
      $file = get_template_directory() . '/dist/svgs/' . $atts['file'] . '.svg';
    }

    if (file_exists($file)) {
      $file_contents = file_get_contents($file);

      if ($file_contents) {
        if ($atts['class']) {
          // Check if class already exists on the svg tag, if found merge the classes
          if (preg_match('/<svg[^>]*class="([^"]*)"/', $file_contents, $matches)) {
            $classes = array_filter(array_map('trim', explode(' ', $matches[1])));

            if (!in_array($atts['class'], $classes)) {
              $classes[] = $atts['class'];
            }

            // Keep any existing attributes on the svg tag
            $file_contents = str_replace($matches[0], '<svg class="' . implode(' ', $classes) . '"', $file_contents);

            // $file_contents = str_replace($matches[0], '<svg class="' . implode(' ', $classes) . '"', $file_contents);
          } else {
            // Class doesn't exist, add it to the svg tag after the existing attributes
            if (preg_match('/<svg([^>]*)>/', $file_contents, $matches)) {
              $file_contents = str_replace($matches[0], '<svg' . $matches[1] . ' class="' . $atts['class'] . '">', $file_contents);
            }
          }
        }

        if ($atts['title']) {
          // Check if a title tag already exists, if found replace the title
          if (preg_match('/<title[^>]*>([^<]*)<\/title>/', $file_contents, $matches)) {
            $file_contents = str_replace($matches[0], '<title>' . $atts['title'] . '</title>', $file_contents);
          } else {
            // Title doesn't exist, add it inside the svg tag after the existing attributes
            if (preg_match('/<svg([^>]*)>/', $file_contents, $matches)) {
              $file_contents = str_replace($matches[0], '<svg' . $matches[1] . '><title>' . $atts['title'] . '</title>', $file_contents);
            }
          }
        }

        return $file_contents;
      }
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

  return '<svg' . $atr_str . '>' . (!empty($title) ? '<title>' . $title . '</title>' : '') . '<use href="#icon-' . $sprite . '" /></svg>';
}
add_shortcode('svg', 'svg');
