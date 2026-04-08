<?php

/**
 * Shortcodes
 *
 * @package Tofino
 * @since 1.0.0
 */


/**
 * Renders a list of social media icon links from ACF options.
 *
 * Usage: [social_icons class="my-class" platforms="facebook,twitter"]
 *
 * @since 1.0.0
 *
 * @param array|string $atts {
 *   Optional. Shortcode attributes.
 *   @type string $class     CSS class to add to the UL element.
 *   @type string $platforms Comma-separated list of platforms to display.
 * }
 * @return string HTML output of unordered list with social icons.
 */
function social_icons(array|string $atts = []): string
{
  $atts = shortcode_atts([
    'class'     => '',
    'platforms' => '',
  ], $atts, 'social_icons');

  $social_links = get_field('social_media_links', 'option');

  if (empty($social_links) || !array_filter($social_links)) {
    return 'Social links not found.';
  }

  $class = 'social-icons' . ($atts['class'] ? ' ' . esc_attr($atts['class']) : '');

  // Filter the social networks based on platform param
  if (!empty($atts['platforms'])) {
    $platforms    = array_map('trim', explode(',', $atts['platforms']));
    $social_links = array_intersect_key($social_links, array_flip($platforms));
    $social_links = array_replace(array_flip($platforms), $social_links);
  }

  $output = '<ul class="' . $class . '">';

  foreach ($social_links as $key => $value) {
    if (!empty($value)) {
      $output .= '<li><a href="' . esc_url($value) . '" target="_blank"><span class="sr-only">' . esc_html($key) . '</span>' . svg(sanitize_title($key)) . '</a></li>';
    }
  }

  $output .= '</ul>';

  return $output;
}
add_shortcode('social_icons', 'social_icons');


/**
 * Renders a copyright symbol with the current year.
 *
 * Usage: [copyright]
 *
 * @since 1.0.0
 *
 * @return string HTML copyright string.
 */
function copyright(): string
{
  return '&copy; ' . wp_date('Y');
}
add_shortcode('copyright', 'copyright');


/**
 * Renders an SVG, either inline from a file or as a sprite reference.
 *
 * Can be called as a shortcode [svg sprite="icon-name"] or directly as a
 * function with a string argument: svg('icon-name').
 *
 * Usage: [svg sprite="arrow" class="icon" title="Arrow icon"]
 *        [svg file="logo"]
 *        [svg file="123"] (attachment ID)
 *
 * @since 1.0.0
 *
 * @param array|string $atts {
 *   Shortcode attributes or a sprite name string.
 *   @type string     $class               CSS class for the SVG element.
 *   @type string     $title               Accessible title for the SVG.
 *   @type string     $id                  ID attribute for the SVG.
 *   @type string     $sprite              Sprite name from the spritemap.
 *   @type string     $preserveAspectRatio SVG preserveAspectRatio attribute.
 *   @type string|int $file                SVG filename (without extension) or attachment ID.
 * }
 * @return string SVG markup, or empty string if not found.
 */
function svg(array|string $atts): string
{
  if (is_string($atts)) {
    $atts = ['sprite' => $atts];
  }

  $atts = shortcode_atts([
    'class'               => '',
    'title'               => '',
    'id'                  => '',
    'sprite'              => '',
    'preserveAspectRatio' => '',
    'file'                => '',
  ], $atts, 'svg');

  if ($atts['file']) {
    return render_svg_file($atts);
  }

  if (!$atts['sprite']) {
    return '';
  }

  return render_svg_sprite($atts);
}
add_shortcode('svg', 'svg');


/**
 * Renders an inline SVG from a file path or attachment ID.
 *
 * @since 4.0.0
 *
 * @param array $atts The parsed shortcode attributes.
 * @return string The SVG file contents with attributes applied, or empty string.
 */
function render_svg_file(array $atts): string
{
  if (is_numeric($atts['file'])) {
    $file = get_attached_file((int) $atts['file']);
  } else {
    $file = get_template_directory() . '/dist/svgs/' . $atts['file'] . '.svg';
  }

  if (!$file || !file_exists($file)) {
    return '';
  }

  $contents = file_get_contents($file);

  if (!$contents) {
    return '';
  }

  if ($atts['class']) {
    $contents = apply_svg_class($contents, $atts['class']);
  }

  if ($atts['title']) {
    $contents = apply_svg_title($contents, $atts['title']);
  }

  return $contents;
}


/**
 * Adds or merges a CSS class onto an SVG element.
 *
 * @since 4.0.0
 *
 * @param string $svg   The SVG markup.
 * @param string $class The CSS class to add.
 * @return string The modified SVG markup.
 */
function apply_svg_class(string $svg, string $class): string
{
  // Check if class already exists on the svg tag, if found merge the classes
  if (preg_match('/<svg[^>]*class="([^"]*)"/', $svg, $matches)) {
    $classes = array_filter(array_map('trim', explode(' ', $matches[1])));

    if (!in_array($class, $classes, true)) {
      $classes[] = $class;
    }

    return str_replace($matches[0], '<svg class="' . esc_attr(implode(' ', $classes)) . '"', $svg);
  }

  // Class doesn't exist, add it to the svg tag
  if (preg_match('/<svg([^>]*)>/', $svg, $matches)) {
    return str_replace($matches[0], '<svg' . $matches[1] . ' class="' . esc_attr($class) . '">', $svg);
  }

  return $svg;
}


/**
 * Adds or replaces a title element inside an SVG.
 *
 * @since 4.0.0
 *
 * @param string $svg   The SVG markup.
 * @param string $title The title text.
 * @return string The modified SVG markup.
 */
function apply_svg_title(string $svg, string $title): string
{
  $title_tag = '<title>' . esc_html($title) . '</title>';

  // Check if a title tag already exists, if found replace it
  if (preg_match('/<title[^>]*>[^<]*<\/title>/', $svg, $matches)) {
    return str_replace($matches[0], $title_tag, $svg);
  }

  // Title doesn't exist, add it inside the svg tag
  if (preg_match('/<svg([^>]*)>/', $svg, $matches)) {
    return str_replace($matches[0], '<svg' . $matches[1] . '>' . $title_tag, $svg);
  }

  return $svg;
}


/**
 * Renders an SVG element using a sprite reference.
 *
 * @since 4.0.0
 *
 * @param array $atts The parsed shortcode attributes.
 * @return string SVG markup with a use element referencing the sprite.
 */
function render_svg_sprite(array $atts): string
{
  $sprite = $atts['sprite'];
  $title  = $atts['title'];

  unset($atts['sprite'], $atts['title'], $atts['file']);

  $atts    = array_filter($atts);
  $attr_str = '';

  foreach ($atts as $key => $value) {
    $attr_str .= ' ' . $key . '="' . esc_attr($value) . '"';
  }

  $title_tag = $title ? '<title>' . esc_html($title) . '</title>' : '';

  return '<svg' . $attr_str . '>' . $title_tag . '<use href="#icon-' . esc_attr($sprite) . '" /></svg>';
}
