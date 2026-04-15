<?php

/**
 * Folder manifest discovery for features and modules.
 *
 * Reads feature.json / module.json from each folder. The JSON is the single
 * source of truth — no filename guessing or fallback conventions.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino;

final class FolderManifest
{
  /** @var array<string, array<string, array<string, string>>> */
  private static array $cache = [];

  private const TYPES = [
    'features' => 'feature.json',
    'modules' => 'module.json',
  ];

  /** @return array<string, array<string, string>> */
  public static function all(string $type): array
  {
    return self::discover($type, self::TYPES[$type]);
  }

  /** @return array<string, string>|null */
  public static function get(string $type, string $slug): ?array
  {
    return self::all($type)[$slug] ?? null;
  }

  /**
   * Discovers folders, reads manifest JSON, and validates referenced files exist.
   *
   * @return array<string, array<string, string>>
   */
  private static function discover(string $type, string $manifest_file): array
  {
    if (isset(self::$cache[$type])) {
      return self::$cache[$type];
    }

    $items = [];
    $base = get_template_directory() . '/' . $type;

    foreach (glob($base . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
      $slug = basename($dir);
      $manifest = self::read_json($dir . '/' . $manifest_file);

      if (!$manifest || empty($manifest['title']) || !is_string($manifest['title'])) {
        continue;
      }

      $resolved = ['title' => $manifest['title']];
      $dir_real = realpath($dir);

      foreach ($manifest as $key => $value) {
        if ($key === 'title' || !is_string($value) || $value === '') {
          continue;
        }

        if ($key === 'scope') {
          $resolved['scope'] = in_array($value, ['frontend', 'admin', 'both'], true) ? $value : 'frontend';
          continue;
        }

        // Only accept paths that resolve inside the module/feature folder —
        // blocks "../../wp-config.php" style escapes from a hostile manifest.
        $target = realpath($dir . '/' . $value);
        if ($target && $dir_real && str_starts_with($target, $dir_real . DIRECTORY_SEPARATOR)) {
          $resolved[$key] = $value;
        }
      }

      $items[$slug] = $resolved;
    }

    ksort($items);
    self::$cache[$type] = $items;

    return self::$cache[$type];
  }

  /**
   * Reads and decodes a JSON file into an associative array.
   *
   * @param string $path Absolute manifest path.
   * @return array<string, mixed>
   */
  private static function read_json(string $path): array
  {
    if (!file_exists($path)) {
      return [];
    }

    $content = file_get_contents($path);
    if (!$content) {
      return [];
    }

    $decoded = json_decode($content, true);

    return is_array($decoded) ? $decoded : [];
  }
}
