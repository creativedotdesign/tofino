<?php

/**
 * Vite asset loader for WordPress.
 *
 * Handles enqueuing of Vite-built assets in both development and production,
 * including HMR support.
 *
 * Adapted from https://github.com/andrefelipe/vite-php-setup
 *
 * @package Tofino
 * @since 4.0.0
 */

namespace Tofino;

class Vite
{
  private static string $handle = 'tofino';

  /** @var array<string, mixed>|null Cached manifest to avoid repeated file reads. */
  private static ?array $manifest_cache = null;


  /**
   * Enqueues all assets for a given entry point.
   *
   * @since 4.0.0
   *
   * @param string $script The entry point path relative to the src directory.
   * @return void
   */
  public static function use_vite(string $script = 'js/app.ts'): void
  {
    self::enqueue_css($script);
    self::enqueue_script($script);
  }


  /**
   * Returns the dev server URL from the hot file, or null if not running.
   *
   * The hot file is written by a Vite plugin on dev server start
   * and removed on shutdown.
   *
   * @since 5.0.0
   *
   * @return string|null The dev server URL, or null in production.
   */
  private static function get_dev_server_url(): ?string
  {
    // Cache the result so the file check only happens once per request.
    static $dev_url = false;

    if ($dev_url === false) {
      $hot_path = get_theme_file_path('dist/hot');

      if (file_exists($hot_path)) {
        $dev_url = rtrim(file_get_contents($hot_path), " \t\n\r/");
      } else {
        $dev_url = null;
      }
    }

    return $dev_url;
  }


  /**
   * Returns the base URL for production assets.
   *
   * @since 4.0.0
   *
   * @return string The dist directory URL.
   */
  private static function dist_url(): string
  {
    return get_stylesheet_directory_uri() . '/dist/';
  }


  /**
   * Resolves the URL for a given entry point.
   *
   * In dev mode, points to the Vite dev server.
   * In production, resolves from the manifest.
   *
   * @since 5.0.0
   *
   * @param string $entry The entry point path.
   * @return string|null The resolved URL, or null if unavailable.
   */
  private static function resolve_entry_url(string $entry): ?string
  {
    $dev_url = self::get_dev_server_url();

    if ($dev_url) {
      return $dev_url . '/' . $entry;
    }

    return self::asset_url($entry);
  }


  /**
   * Enqueues the script module for the entry point.
   *
   * Uses wp_enqueue_script_module for native ES module support
   * with automatic import map generation.
   *
   * @since 5.0.0
   *
   * @param string $entry The entry point path.
   * @return void
   */
  private static function enqueue_script(string $entry): void
  {
    $url = self::resolve_entry_url($entry);

    if (!$url) {
      return;
    }

    $handle = self::$handle . '-' . sanitize_title($entry);

    wp_enqueue_script_module($handle, $url, [], null);
  }


  /**
   * Enqueues CSS files extracted from the manifest for the entry point.
   *
   * In dev mode, CSS is injected by Vite via HMR.
   *
   * @since 5.0.0
   *
   * @param string $entry The entry point path.
   * @return void
   */
  private static function enqueue_css(string $entry): void
  {
    if (self::get_dev_server_url() !== null) {
      return;
    }

    foreach (self::get_css_urls($entry) as $url) {
      wp_enqueue_style(self::$handle . '-' . sanitize_title($entry), $url);
    }
  }


  /**
   * Reads and caches the Vite manifest file.
   *
   * @since 4.0.0
   *
   * @return array<string, array<string, mixed>> The decoded manifest data.
   */
  private static function get_manifest(): array
  {
    if (self::$manifest_cache !== null) {
      return self::$manifest_cache;
    }

    $file = get_theme_file_path('dist/.vite/manifest.json');

    if (!file_exists($file)) {
      self::$manifest_cache = [];
      return self::$manifest_cache;
    }

    $content = file_get_contents($file);
    self::$manifest_cache = $content ? json_decode($content, true) : [];

    return self::$manifest_cache;
  }


  /**
   * Resolves a production asset URL from the manifest.
   *
   * @since 4.0.0
   *
   * @param string $entry The entry point path.
   * @return string The full asset URL.
   */
  private static function asset_url(string $entry): string
  {
    $manifest = self::get_manifest();

    return isset($manifest[$entry])
      ? self::dist_url() . $manifest[$entry]['file']
      : self::dist_url() . $entry;
  }


  /**
   * Gets CSS file URLs from the manifest for an entry point.
   *
   * @since 4.0.0
   *
   * @param string $entry The entry point path.
   * @return string[] Array of CSS URLs.
   */
  private static function get_css_urls(string $entry): array
  {
    $manifest = self::get_manifest();
    $urls = [];

    if (!empty($manifest[$entry]['css'])) {
      foreach ($manifest[$entry]['css'] as $file) {
        $urls[] = self::dist_url() . $file;
      }
    }

    return $urls;
  }
}
