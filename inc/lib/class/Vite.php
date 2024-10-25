<?php
// Adapted from https://github.com/andrefelipe/vite-php-setup/blob/master/public/helpers.php
namespace Tofino;

class Vite
{
  public static $serverUrl = 'http://localhost:3000';

  public static function getBrowserSyncUrl($port = 3001)
  {
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

    socket_connect($sock, "8.8.8.8", 53);
    socket_getsockname($sock, $addr); // $name passed by reference
    socket_shutdown($sock);
    socket_close($sock);

    return 'http://' . $addr . ':' . $port;
  }

  public static function isBrowserSyncRunning() {
    // Get the header
    if (isset($_SERVER['HTTP_BROWSER_SYNC'])) {
      return true;
    }
  }

  public static function isDevServerRunning()
  {
    if (in_array(wp_get_environment_type(), ['local', 'development']) && is_array(wp_remote_get(self::$serverUrl))) {
      return true;
    } else {
      return false;
    }
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
    if (self::isDevServerRunning()) {
      $browserSyncUrl = self::getBrowserSyncUrl();
      $browserSyncUrl = str_replace(['http://', 'https://'], '', $browserSyncUrl);
      $browserSyncUrl = explode(':', $browserSyncUrl)[0];

      // The header Sec-Fetch-User only exists when browser-sync is running via localhost.
      $is_browser_sync_on_localhost = isset($_SERVER['HTTP_SEC_FETCH_USER']);

      if (self::isBrowserSyncRunning() && !$is_browser_sync_on_localhost) {
        $url = self::getBrowserSyncUrl(3000) . '/' . $entry;
      } else {
        $url = self::$serverUrl . '/' . $entry;
      }
    } else {
      $url = self::assetUrl($entry);
    }

    if (!$url) {
      return '';
    }

    wp_register_script("tofino", $url, false, true, true);
    wp_enqueue_script("tofino");
  }

  private static function jsPreloadImports($entry)
  {
    if (self::isDevServerRunning() || self::isBrowserSyncRunning()) {
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
    if (self::isDevServerRunning() || self::isBrowserSyncRunning()) {
      return '';
    }

    $tags = '';

    foreach (self::cssUrls($entry) as $url) {
      wp_register_style("tofino-" . sanitize_title($entry), $url);
      wp_enqueue_style("tofino-" . sanitize_title($entry), $url);
    }

    return $tags;
  }

  // Helpers to locate files
  private static function getManifest(): array
  {
    $file = get_stylesheet_directory() . '/dist/.vite/manifest.json';

    if (!file_exists($file)) {
      return [];
    }

    $content = @file_get_contents(get_stylesheet_directory() . '/dist/.vite/manifest.json');

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
