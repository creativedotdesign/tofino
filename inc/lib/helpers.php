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
  $paths = apply_filters('tofino_custom_module_paths', [get_template_directory() . '/templates/modules/']);

  foreach ($paths as $path) {
    $file_path = $path . $layout . '.php';

    if (file_exists($file_path)) {
      include $file_path;

      $is_from_plugin = !str_contains($path, get_template_directory());

      if ($is_from_plugin) {
        echo "<!-- Module loaded from plugin -->";
      }
    }
  }
}
