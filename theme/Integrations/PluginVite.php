<?php

/**
 * Vite asset loader for Tofino-aligned plugins.
 *
 * Lets a plugin participate in the theme's Vite dev server (and HMR) without
 * running its own Vite process. In production the plugin's own built bundle
 * is enqueued from its `dist/.vite/manifest.json`.
 *
 * Usage from a plugin (canonical Tofino layout — entry `app.ts` at plugin root):
 *
 *   if (class_exists(\Tofino\Integrations\PluginVite::class)) {
 *     \Tofino\Integrations\PluginVite::use_vite([
 *       'handle'     => 'my-plugin',
 *       'plugin_dir' => plugin_dir_path(__FILE__),
 *       'plugin_url' => plugin_dir_url(__FILE__),
 *       // 'src'          => 'app.ts',  // optional; defaults to 'app.ts'
 *       // 'manifest_key' => 'app.ts',  // optional; defaults to basename($src)
 *       // 'deps'         => [['id' => 'vue', 'import' => 'dynamic']],
 *     ]);
 *   }
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Integrations;

final class PluginVite
{
  /** @var array<string, array<string, mixed>> Per-plugin manifest cache. */
  private static array $manifest_cache = [];

  /**
   * Enqueue a plugin's Vite entry — through the theme dev server in dev,
   * or from the plugin's own production manifest otherwise.
   *
   * @param array{
   *   handle: string,
   *   plugin_dir: string,
   *   plugin_url: string,
   *   src: string,
   *   manifest_key?: string,
   *   deps?: array<int, array{id: string, import?: string}|string>,
   * } $args
   */
  public static function use_vite(array $args): void
  {
    foreach (['handle', 'plugin_dir', 'plugin_url'] as $key) {
      if (empty($args[$key])) {
        return;
      }
    }

    $args['src'] = $args['src'] ?? 'app.ts';

    self::enqueue_script($args);
    self::enqueue_css($args);
  }

  private static function enqueue_script(array $args): void
  {
    $url = self::resolve_entry_url($args);

    if (!$url) {
      return;
    }

    wp_enqueue_script_module(
      $args['handle'],
      $url,
      $args['deps'] ?? [],
      null,
    );
  }

  private static function enqueue_css(array $args): void
  {
    // In dev, Vite injects CSS over HMR — skip the production stylesheet.
    if (Vite::get_dev_server_url() !== null) {
      return;
    }

    foreach (self::get_css_urls($args) as $url) {
      wp_enqueue_style($args['handle'] . '-' . md5($url), $url, [], null);
    }
  }

  private static function resolve_entry_url(array $args): ?string
  {
    $dev_url = Vite::get_dev_server_url();

    if ($dev_url) {
      $abs = rtrim($args['plugin_dir'], '/\\') . '/' . ltrim($args['src'], '/\\');
      return $dev_url . '/@fs' . $abs;
    }

    $manifest = self::get_manifest($args['plugin_dir']);
    $entry = $manifest[self::manifest_key($args)] ?? null;

    if (!is_array($entry) || empty($entry['file'])) {
      return null;
    }

    return self::dist_url($args['plugin_url']) . $entry['file'];
  }

  /**
   * @return array<int, string>
   */
  private static function get_css_urls(array $args): array
  {
    $manifest = self::get_manifest($args['plugin_dir']);
    $entry = $manifest[self::manifest_key($args)] ?? null;

    if (!is_array($entry) || empty($entry['css'])) {
      return [];
    }

    $base = self::dist_url($args['plugin_url']);
    $urls = [];

    foreach ((array) $entry['css'] as $file) {
      $urls[] = $base . (string) $file;
    }

    return $urls;
  }

  private static function manifest_key(array $args): string
  {
    return (string) ($args['manifest_key'] ?? basename($args['src']));
  }

  private static function dist_url(string $plugin_url): string
  {
    return rtrim($plugin_url, '/') . '/dist/';
  }

  /**
   * @return array<string, array<string, mixed>>
   */
  private static function get_manifest(string $plugin_dir): array
  {
    $key = rtrim($plugin_dir, '/\\');

    if (isset(self::$manifest_cache[$key])) {
      return self::$manifest_cache[$key];
    }

    $file = $key . '/dist/.vite/manifest.json';

    if (!file_exists($file)) {
      return self::$manifest_cache[$key] = [];
    }

    $decoded = json_decode((string) file_get_contents($file), true);

    return self::$manifest_cache[$key] = is_array($decoded) ? $decoded : [];
  }
}
