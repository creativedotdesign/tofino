<?php
// Adapted from https://github.com/andrefelipe/vite-php-setup/blob/master/public/helpers.php
namespace Tofino;

class Vite
{

  public static $serverUrl = 'http://localhost:3000';

  public static function isDevServerRunning()
  {
    $file = @fopen(self::$serverUrl . "/@vite/client", "r");

    if (!$file) {
      error_clear_last();

      return false;
    }

    fclose($file);

    return true;
  }

  public static function base_path()
  {
    return get_stylesheet_directory_uri() . '/dist/';
  }

  public static function useVite(string $script = 'js/app.ts')
  {
    self::jsPreloadImports($script);
    self::cssTag($script);
    self::register($script);
  }

  public static function register($entry)
  {
    $url = self::isDevServerRunning()
      ? 'http://localhost:3000/' . $entry
      : self::assetUrl($entry);

    if (!$url) {
      return '';
    }

    wp_register_script("tofino", $url, false, true, true);
    wp_enqueue_script("tofino");
  }

  private static function jsPreloadImports($entry)
  {
    if (self::isDevServerRunning()) {
      return;
    }

    $res = '';
    foreach (self::importsUrls($entry) as $url) {
      $res .= '<link rel="modulepreload" href="' . $url . '">';
    }

    add_action('wp_head', function () use (&$res) {
      echo $res;
    });
  }

  private static function cssTag(string $entry): string
  {
    // not needed on dev, it's inject by Vite
    if (self::isDevServerRunning()) {
      return '';
    }

    $tags = '';
    foreach (self::cssUrls($entry) as $url) {
      wp_register_style("tofino/$entry", $url);
      wp_enqueue_style("tofino/$entry", $url);
    }
    return $tags;
  }


  // Helpers to locate files

  private static function getManifest(): array
  {
    $content = file_get_contents(get_stylesheet_directory() . '/dist/manifest.json');

    return json_decode($content, true);
  }

  private static function assetUrl(string $entry): string
  {
    $manifest = self::getManifest();

    return isset($manifest[$entry])
      ? self::base_path() . $manifest[$entry]['file']
      : self::base_path() . $entry;
  }

  private static function getPublicURLBase()
  {
    return self::isDevServerRunning() ? '/dist/' : self::base_path();
  }

  private static function importsUrls(string $entry): array
  {
    $urls = [];
    $manifest = self::getManifest();

    if (!empty($manifest[$entry]['imports'])) {
      foreach ($manifest[$entry]['imports'] as $imports) {
        $urls[] = self::getPublicURLBase() . $manifest[$imports]['file'];
      }
    }
    return $urls;
  }

  private static function cssUrls(string $entry): array
  {
    $urls = [];
    $manifest = self::getManifest();

    if (!empty($manifest[$entry]['css'])) {
      foreach ($manifest[$entry]['css'] as $file) {
        $urls[] = self::getPublicURLBase() . $file;
      }
    }
    return $urls;
  }
}