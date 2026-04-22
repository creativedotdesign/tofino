<?php

/**
 *
 * Helper functions
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Helpers;




/**
 * Gets a post ID from its slug.
 *
 * If WPML is active and no post is found in the current language,
 * falls back to the default language and returns the translated ID.
 *
 * @since 1.0.0
 *
 * @param string $slug      The post slug to search for.
 * @param string $post_type Optional. The post type to search. Default 'page'.
 * @return int|null The post ID or null if not found.
 */
function get_id_by_slug(string $slug, string $post_type = 'page'): ?int
{
  $page = get_page_by_path($slug, OBJECT, $post_type);

  if ($page) {
    return $page->ID;
  }
  // WPML: slug may only exist in the default language. Look it up there, then get the translation.
  if (function_exists('wpml_object_id_filter')) {
    do_action('wpml_switch_language', apply_filters('wpml_default_language', null));
    $page = get_page_by_path($slug, OBJECT, $post_type);
    do_action('wpml_switch_language', null); // Restore current language

    if ($page) {
      $translated_id = apply_filters('wpml_object_id', $page->ID, $post_type, true);
      return $translated_id ? (int) $translated_id : null;
    }
  }
  return null;
}


/**
 * Fixes text orphans by adding a non-breaking space before the last word.
 * Ignores text inside HTML tags.
 *
 * @since 3.2.0
 *
 * @param string $str            Optional. The input string, may contain HTML. Default ''.
 * @param int    $min_word_count Optional. Minimum word count required to apply the fix. Default 3.
 * @return string The processed string with &amp;nbsp; before the last word in each text node.
 */
function fix_text_orphan(string $str = '', int $min_word_count = 3): string
{
  $str = trim($str);

  $pattern = '/>([^<]+)</';

  $result = preg_replace_callback($pattern, function (array $matches) use ($min_word_count): string {
    $text = $matches[1];

    $space = strrpos($text, ' ');

    $word_count = str_word_count($text);

    if ($space !== false && $word_count > $min_word_count) {
      $text = substr($text, 0, $space) . '&nbsp;' . substr($text, $space + 1);
    }

    return '>' . $text . '<';
  }, $str);

  return $result ?? $str;
}


/**
 * Returns responsive image attribute values for use in an img tag.
 *
 * @since 3.2.1
 *
 * @param int|null $image_id Optional. The attachment ID. Defaults to the post featured image.
 * @param string   $size     Optional. The registered image size for the src attribute. Default 'full'.
 * @return array{srcset: string|false, sizes: string|false, src: string|null, alt: string} Image attribute values.
 */
function responsive_image_attribute_values(?int $image_id = null, string $size = 'full'): array
{
  if (!$image_id) {
    $image_id = (int) get_post_thumbnail_id();
  }

  $meta = wp_get_attachment_metadata($image_id);
  $url = wp_get_attachment_image_src($image_id, $size);
  $sizes = wp_calculate_image_sizes($size, $url, $meta, $image_id);
  $srcset = wp_get_attachment_image_srcset($image_id, $size);
  $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);

  return [
    'srcset' => $srcset,
    'sizes' => $sizes,
    'src' => ($url ? $url[0] : null),
    'alt' => esc_attr((string) $alt)
  ];
}


/**
 * Wraps the last word of a string in a span element with a given class.
 *
 * @since 4.0.0
 *
 * @param string $string The input string.
 * @param string $class  The CSS class to apply to the span.
 * @return string The string with the last word wrapped in a span.
 */
function wrap_last_word(string $string, string $class): string
{
  $pieces = explode(" ", $string);

  $pieces[count($pieces) - 1] = '<span class="' . esc_attr($class) . '">' . $pieces[count($pieces) - 1] . '</span>';

  return implode(" ", $pieces);
}


/**
 * Gets a sub field from a specific layout within an ACF flexible content field.
 *
 * @since 4.1.0
 *
 * @param string   $flex_field  The flexible content field name.
 * @param string   $flex_layout The layout name to match, or empty to return the first layout.
 * @param int|null $page_id     Optional. The post ID. Defaults to the current post.
 * @return array|null The matched layout field data, or null if not found.
 */
function get_flex_field_by_page_id(string $flex_field, string $flex_layout = '', ?int $page_id = null): ?array
{
  $page_id = $page_id ?: (int) get_the_ID();
  $fields = get_field($flex_field, $page_id);

  if (is_array($fields)) {
    foreach ($fields as $field) {
      if ($flex_layout === '' || $field['acf_fc_layout'] === $flex_layout) {
        return $field;
      }
    }
  }

  return null;
}


/**
 * Checks if a page contains only a single ACF flexible content module.
 *
 * @since 4.1.0
 *
 * @param string $module_name The ACF layout name to check for.
 * @param int    $page_id     The post ID to check.
 * @return bool True if the page contains only the specified module.
 */
function is_single_module_page(string $module_name, int $page_id): bool
{
  $modules = get_field('content_modules', $page_id);

  if (is_array($modules) && count($modules) === 1) {
    return $modules[0]['acf_fc_layout'] === $module_name;
  }

  return false;
}


/**
 * Normalizes an ACF link target value.
 *
 * Returns '_blank' if the target is '_blank', otherwise '_self'.
 *
 * @since 4.1.0
 *
 * @param string $target The target attribute value from an ACF link field.
 * @return string '_blank' or '_self'.
 */
function check_target(string $target): string
{
  return $target === '_blank' ? '_blank' : '_self';
}


/**
 * Escapes Markdown control characters in plain text.
 *
 * @since 5.0.0
 *
 * @param string $text Plain text to escape.
 * @return string Markdown-safe text.
 */
function escape_markdown_text(string $text): string
{
  return strtr($text, [
    '\\' => '\\\\',
    '`'  => '\`',
    '*'  => '\*',
    '_'  => '\_',
    '{'  => '\{',
    '}'  => '\}',
    '['  => '\[',
    ']'  => '\]',
    '('  => '\(',
    ')'  => '\)',
    '#'  => '\#',
    '+'  => '\+',
    '-'  => '\-',
    '.'  => '\.',
    '!'  => '\!',
    '|'  => '\|',
    '>'  => '\>',
  ]);
}


/**
 * Converts HTML content to Markdown.
 *
 * Uses league/html-to-markdown when available. Falls back to plain-text
 * extraction with basic line-break preservation.
 *
 * @since 5.0.0
 *
 * @param string $content HTML content.
 * @return string Markdown output.
 */
function convert_html_to_markdown(string $content): string
{
  $content = trim($content);

  if ($content === '') {
    return '';
  }

  $content = do_shortcode($content);

  $default_options = [
    'header_style' => 'atx',
    'strip_tags' => true,
    'remove_nodes' => 'script style',
    'hard_break' => true,
  ];
  $options = $default_options;

  if (class_exists('\League\HTMLToMarkdown\HtmlConverter')) {
    $converter = new \League\HTMLToMarkdown\HtmlConverter($options);
    $markdown = (string) $converter->convert($content);
  } else {
    $sanitized = wp_kses_post($content);
    $markdown = html_entity_decode(wp_strip_all_tags($sanitized), ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }

  while (str_contains($markdown, "\n\n\n")) {
    $markdown = str_replace("\n\n\n", "\n\n", $markdown);
  }

  return trim($markdown);
}


/**
 * Resolves a module-local markdown renderer file.
 *
 * @since 5.0.0
 *
 * @param string $layout The module folder slug.
 * @return string|null Absolute path to the markdown renderer file.
 */
function get_module_markdown_file(string $layout): ?string
{
  $paths = apply_filters('tofino_custom_module_paths', [get_template_directory() . '/modules/']);
  $manifest = \Tofino\Registry\FolderManifest::get('modules', $layout);
  $declared_markdown = $manifest['markdown'] ?? null;

  foreach ($paths as $path) {
    $path = trailingslashit((string) $path);
    $path_real = realpath($path);
    if (!$path_real) {
      continue;
    }

    $declared_file = $declared_markdown ? $path . $layout . '/' . $declared_markdown : null;
    $default_file  = $path . $layout . '/markdown.php';
    $file_path     =
      ($declared_file && file_exists($declared_file)) ? $declared_file
      : (file_exists($default_file) ? $default_file : null);

    if (!$file_path) {
      continue;
    }

    $file_real = realpath($file_path);
    if (!$file_real || !str_starts_with($file_real, $path_real . DIRECTORY_SEPARATOR)) {
      continue;
    }

    return $file_real;
  }

  return null;
}


/**
 * Renders a module's Markdown serializer and returns the output string.
 *
 * @since 5.0.0
 *
 * @param string $layout The module folder slug.
 * @return string Markdown output for the current module row.
 */
function render_module_markdown(string $layout): string
{
  $file_path = get_module_markdown_file($layout);

  if (!$file_path) {
    return '';
  }

  ob_start();
  include $file_path;
  $output = ob_get_clean();

  return is_string($output) ? trim($output) : '';
}


/**
 * Renders an ACF flexible content module template file.
 *
 * Searches through registered module paths (filterable via 'tofino_custom_module_paths')
 * and includes the first matching template file.
 *
 * @since 5.0.0
 *
 * @param string $layout The module layout filename (without .php extension).
 * @return void
 */
function render_module(string $layout): void
{
  $paths = apply_filters('tofino_custom_module_paths', [get_template_directory() . '/modules/']);
  $manifest = \Tofino\Registry\FolderManifest::get('modules', $layout);
  $declared_template = $manifest['template'] ?? null;

  foreach ($paths as $path) {
    $path_real = realpath($path);
    if (!$path_real) {
      continue;
    }

    $declared_folder_file = $declared_template ? $path . $layout . '/' . $declared_template : null;
    $folder_file = $path . $layout . '/template.php';
    $flat_file   = $path . $layout . '.php';
    $file_path   =
      ($declared_folder_file && file_exists($declared_folder_file)) ? $declared_folder_file
      : (file_exists($folder_file) ? $folder_file : (file_exists($flat_file) ? $flat_file : null));

    if (!$file_path) {
      continue;
    }

    // Ensure the resolved template lives inside the registered module root —
    // belt-and-braces against any bad path that slipped past FolderManifest.
    $file_real = realpath($file_path);
    if (!$file_real || !str_starts_with($file_real, $path_real . DIRECTORY_SEPARATOR)) {
      continue;
    }

    include $file_real;

    if (!str_contains($path, get_template_directory())) {
      echo "<!-- Module loaded from plugin -->";
    }

    return;
  }
}


/**
 * Gets the display label for an ACF flexible content module layout.
 *
 * @since 5.0.0
 *
 * @param string $row_layout The raw ACF layout name (for example: general_content).
 * @param string $field_name Optional. Flexible content field name. Default 'content_modules'.
 * @return string The layout label, or a humanized fallback when no label is found.
 */
function get_module_name(string $row_layout, string $field_name = 'content_modules'): string
{
  static $layout_labels_cache = [];

  if (!isset($layout_labels_cache[$field_name])) {
    $field_object = get_field_object($field_name);
    $layouts = is_array($field_object['layouts'] ?? null) ? $field_object['layouts'] : [];
    $layout_labels_cache[$field_name] = [];

    foreach ($layouts as $layout) {
      if (!empty($layout['name']) && !empty($layout['label'])) {
        $layout_labels_cache[$field_name][$layout['name']] = $layout['label'];
      }
    }
  }

  return $layout_labels_cache[$field_name][$row_layout] ?? ucwords(str_replace('_', ' ', $row_layout));
}
