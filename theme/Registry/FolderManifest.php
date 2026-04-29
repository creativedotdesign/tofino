<?php

/**
 * Folder manifest discovery for features.
 *
 * Reads feature.json from each feature folder. Modules are handled by
 * ModuleManifest.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Registry;

final class FolderManifest
{
  /** @var array<string, array<string, array<string, string>>> */
  private static array $cache = [];

  private const TYPES = [
    'features' => 'feature.json',
  ];

  /** @return array<string, array<string, string>> */
  public static function all(string $type): array
  {
    if (!isset(self::TYPES[$type])) {
      return [];
    }

    return self::discover($type, self::TYPES[$type]);
  }

  /** @return array<string, string>|null */
  public static function get(string $type, string $slug): ?array
  {
    $items = self::all($type);

    return $items[$slug] ?? null;
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

    foreach (glob(get_template_directory() . '/' . $type . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
      if (!file_exists($dir . '/' . $manifest_file)) {
        continue;
      }

      $manifest = self::read_json($dir . '/' . $manifest_file);

      if (!$manifest || empty($manifest['title']) || !is_string($manifest['title'])) {
        continue;
      }

      $dir_real = realpath($dir);
      if (!$dir_real) {
        continue;
      }

      $slug = basename($dir);

      $resolved = [
        'title' => $manifest['title'],
        '_dir'  => $dir_real,
      ];

      foreach ($manifest as $manifest_key => $value) {
        if ($manifest_key === 'title' || !is_string($value) || $value === '') {
          continue;
        }

        if ($manifest_key === 'scope') {
          $resolved['scope'] = in_array($value, ['frontend', 'admin', 'both'], true) ? $value : 'frontend';
          continue;
        }

        // Only accept paths that resolve inside the module/feature folder —
        // blocks "../../wp-config.php" style escapes from a hostile manifest.
        $target = realpath($dir . '/' . $value);
        if ($target && str_starts_with($target, $dir_real . DIRECTORY_SEPARATOR)) {
          $resolved[$manifest_key] = $value;
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
