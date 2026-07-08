<?php

/**
 * Module manifest registry.
 *
 * Modules are declared by module.json files and registered by providers. Tofino
 * registers its own theme modules; plugins register their exact manifests with
 * the `tofino/register_modules` filter.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Registry;

final class ModuleManifest
{
  /** @var array<string, array<string, string>>|null */
  private static ?array $cache = null;

  /** @return array<string, array<string, string>> */
  public static function all(): array
  {
    if (self::$cache !== null) {
      return self::$cache;
    }

    $manifest_paths = self::theme_manifest_paths();
    $manifest_paths = apply_filters('tofino/register_modules', $manifest_paths);
    $manifest_paths = is_array($manifest_paths) ? $manifest_paths : [];

    $modules = [];

    foreach ($manifest_paths as $manifest_path) {
      if (!is_string($manifest_path)) {
        continue;
      }

      $module = self::from_manifest($manifest_path);
      if (!$module) {
        continue;
      }

      $name = $module['name'];
      if (isset($modules[$name])) {
        continue;
      }

      $modules[$name] = $module;
    }

    ksort($modules);
    return self::$cache = $modules;
  }

  /** @return array<string, string>|null */
  public static function get(string $name): ?array
  {
    return self::all()[$name] ?? null;
  }

  /**
   * Resolve a module file declared by manifest key.
   *
   * @param array<string, string> $module
   */
  public static function file(array $module, string $key): ?string
  {
    $dir = $module['_dir'] ?? '';
    if ($dir === '') {
      return null;
    }

    $relative = $module[$key] ?? null;
    if (!$relative) {
      return null;
    }

    $dir_real = realpath($dir);
    $file_real = realpath(trailingslashit($dir) . $relative);

    if (!$dir_real || !$file_real || !str_starts_with($file_real, $dir_real . DIRECTORY_SEPARATOR)) {
      return null;
    }

    return is_file($file_real) ? $file_real : null;
  }

  /**
   * Build a normalized module definition from a module.json file.
   *
   * @return array<string, string>|null
   */
  private static function from_manifest(string $manifest_path): ?array
  {
    $manifest_real = realpath($manifest_path);
    if (!$manifest_real || !is_file($manifest_real)) {
      return null;
    }

    $dir = dirname($manifest_real);
    $content = file_get_contents($manifest_real);
    $manifest = $content ? json_decode($content, true) : null;

    if (!is_array($manifest)) {
      return null;
    }

    $name = $manifest['name'] ?? null;
    $title = $manifest['title'] ?? null;

    if (!is_string($name) || !preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
      return null;
    }

    if (!is_string($title) || $title === '') {
      return null;
    }

    $module = [
      'name' => $name,
      'title' => $title,
      '_dir' => $dir,
    ];

    foreach ($manifest as $key => $value) {
      if (!is_string($key) || !is_string($value) || $value === '') {
        continue;
      }

      if (in_array($key, ['name', 'title'], true)) {
        continue;
      }

      // Note: module manifests have no `scope` key — nothing ever gated on it
      // (a stray one is treated like any unknown key and dropped below).
      // Features' feature.json scope is separate (FolderManifest).

      $target = realpath(trailingslashit($dir) . $value);
      if ($target && str_starts_with($target, $dir . DIRECTORY_SEPARATOR)) {
        $module[$key] = $value;
      }
    }

    return $module;
  }

  /** @return array<int, string> */
  private static function theme_manifest_paths(): array
  {
    // Child theme first: all() is first-wins on module name, so a child theme
    // can override a parent module by shipping the same module name.
    $dirs = array_unique([get_stylesheet_directory(), get_template_directory()]);

    $paths = [];
    foreach ($dirs as $dir) {
      $paths = array_merge($paths, glob($dir . '/modules/*/module.json') ?: []);
    }

    return $paths;
  }
}
