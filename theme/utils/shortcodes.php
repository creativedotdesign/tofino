<?php

/**
 * Shortcodes
 *
 * @package Tofino
 * @since 1.0.0
 */


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
  $name = $atts['file'];

  if (!is_string($name) && !is_int($name)) {
    return '';
  }

  $name = (string) $name;
  $is_attachment = ctype_digit($name);

  if ($is_attachment) {
    $attachment_id = (int) $name;

    if (get_post_type($attachment_id) !== 'attachment' || get_post_mime_type($attachment_id) !== 'image/svg+xml') {
      return '';
    }
    $file = get_attached_file($attachment_id);
    $uploads = wp_get_upload_dir();
    $allowed_dirs = empty($uploads['error']) ? [$uploads['basedir']] : [];
  } else {
    // Built assets may use subdirectories, but never relative traversal or URLs.
    if (!preg_match('#^[a-zA-Z0-9_.-]+(?:/[a-zA-Z0-9_.-]+)*$#', $name)
      || array_intersect(explode('/', $name), ['.', '..'])
    ) {
      return '';
    }

    $file = get_theme_file_path('dist/svgs/' . $name . '.svg');
    $allowed_dirs = [get_stylesheet_directory() . '/dist/svgs', get_template_directory() . '/dist/svgs'];
  }

  $file = $file ? realpath($file) : false;

  if (!$file || !is_file($file) || !is_readable($file) || strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'svg') {
    return '';
  }

  $contained = false;
  foreach ($allowed_dirs as $dir) {
    $base = realpath($dir);

    if ($base && str_starts_with($file, $base . DIRECTORY_SEPARATOR)) {
      $contained = true;
      break;
    }
  }

  if (!$contained) {
    return '';
  }

  $contents = file_get_contents($file);

  if (!$contents) {
    return '';
  }

  if ($is_attachment) {
    if (!class_exists(\Tofino\Security\Svg::class)) {
      return '';
    }

    $contents = \Tofino\Security\Svg::sanitize($contents);
    if (is_wp_error($contents)) {
      return '';
    }
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

    $opening = str_replace('class="' . $matches[1] . '"', 'class="' . esc_attr(implode(' ', $classes)) . '"', $matches[0]);
    return str_replace($matches[0], $opening, $svg);
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

  // Accessibility: an icon with a title is an image named by it; an icon
  // without one is decorative and hidden from assistive technology (the
  // control around it carries the name). Callers may still pass their own
  // role / aria-hidden and keep control.
  $title_tag = '';

  if ($title) {
    $title_id  = 'svg-title-' . wp_unique_id();
    $title_tag = '<title id="' . esc_attr($title_id) . '">' . esc_html($title) . '</title>';
    $attr_str .= ' role="img" aria-labelledby="' . esc_attr($title_id) . '"';
  } elseif (!isset($atts['role']) && !isset($atts['aria-hidden'])) {
    $attr_str .= ' aria-hidden="true" focusable="false"';
  }

  return '<svg' . $attr_str . '>' . $title_tag . '<use href="#icon-' . esc_attr($sprite) . '" /></svg>';
}
