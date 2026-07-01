<?php

/**
 * Cloudflare Tunnel URL rewriting for local development.
 *
 * When a request arrives through Cloudflare Tunnel, WordPress still generates
 * absolute URLs using the local vhost (e.g. tofino.test). This class rewrites
 * those URLs to the public tunnel hostname so assets, links, and enqueued
 * scripts/styles resolve correctly.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Integrations;

class CloudflareTunnel
{
  /** @var string|null Cached public base URL for the current request. */
  private static ?string $public_base_cache = null;

  /** @var bool Whether the public base URL has been resolved for this request. */
  private static bool $cache_resolved = false;

  /**
   * Registers all tunnel URL-override filters.
   *
   * Call once from the theme bootstrap. Hooks into option_home, option_siteurl,
   * script/style loaders, and various URL output filters.
   *
   * @since 5.0.0
   *
   * @return void
   */
  public static function register(): void
  {
    add_filter('option_home', [self::class, 'override_base_url'], 1);
    add_filter('option_siteurl', [self::class, 'override_base_url'], 1);
    add_filter('redirect_canonical', [self::class, 'prevent_canonical_redirect'], 10, 2);
    add_filter('script_loader_src', [self::class, 'rewrite_url'], 20);
    add_filter('style_loader_src', [self::class, 'rewrite_url'], 20);
    add_filter('plugins_url', [self::class, 'rewrite_url'], 20);
    add_filter('content_url', [self::class, 'rewrite_url'], 20);
    add_filter('includes_url', [self::class, 'rewrite_url'], 20);
    add_filter('upload_dir', [self::class, 'rewrite_upload_dir'], 20);
  }

  /**
   * Filter callback for upload_dir.
   *
   * wp_upload_dir() builds its URLs from the WP_CONTENT_URL constant, which is
   * defined before theme filters load, so attachment URLs bypass the
   * option_siteurl and content_url filters and need rewriting here.
   *
   * @since 5.0.0
   *
   * @param array $uploads The upload directory data array.
   * @return array The array with url and baseurl rewritten to the tunnel host.
   */
  public static function rewrite_upload_dir(array $uploads): array
  {
    $uploads['url'] = self::rewrite_url((string) $uploads['url']);
    $uploads['baseurl'] = self::rewrite_url((string) $uploads['baseurl']);
    return $uploads;
  }

  /**
   * Filter callback for option_home / option_siteurl.
   *
   * Replaces the stored home or site URL with the tunnel's public base URL
   * when the request originates from Cloudflare.
   *
   * @since 5.0.0
   *
   * @param string $url The original home or site URL.
   * @return string The tunnel public URL, or the original URL if not applicable.
   */
  public static function override_base_url(string $url): string
  {
    return self::get_public_base_url() ?: $url;
  }

  /**
   * Cancels WordPress canonical redirects for tunnel requests.
   *
   * The Cloudflare ingress rewrites the Host header to the local vhost
   * (e.g. tofino.test) so Local's router can resolve the site, while this
   * integration overrides home/siteurl to the public tunnel host. WordPress
   * then sees the request host (tofino.test) differ from the canonical home
   * host (tofino.lambda.host) and 301-redirects every request to the tunnel
   * host, which Cloudflare forwards straight back — an infinite loop
   * (ERR_TOO_MANY_REDIRECTS). Skipping the canonical redirect for tunnel
   * requests breaks it; the override already emits correct absolute URLs.
   *
   * @since 5.0.0
   *
   * @param string $redirect_url  The canonical URL WordPress wants to redirect to.
   * @param string $requested_url The originally requested URL.
   * @return string|false The original redirect URL, or false to cancel the redirect.
   */
  public static function prevent_canonical_redirect(string $redirect_url, string $requested_url)
  {
    if (self::get_public_base_url()) {
      return false;
    }

    return $redirect_url;
  }

  /**
   * Filter callback that rewrites any local-vhost URL to the tunnel host.
   *
   * Replaces the scheme and host portion of an absolute URL when it matches
   * the current request's HTTP_HOST.
   *
   * @since 5.0.0
   *
   * @param string $url The original absolute URL (e.g. script src, style src, plugin URL).
   * @return string The rewritten URL with the tunnel hostname, or the original URL unchanged.
   */
  public static function rewrite_url(string $url): string
  {
    $public_base = self::get_public_base_url();
    if (!$public_base || $url === '') {
      return $url;
    }

    $request_host = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'] ?? ''));
    if ($request_host === '') {
      return $url;
    }

    return (string) preg_replace(
      '#^https?://' . preg_quote($request_host, '#') . '(?=/|$)#i',
      $public_base,
      $url,
    );
  }

  /**
   * Resolves and caches the public tunnel base URL for the current request.
   *
   * Reads the Vite hot file to determine the tunnel hostname. The result is
   * cached per-request so the hot file is only read once.
   *
   * @since 5.0.0
   *
   * @return string|null The public base URL (e.g. 'https://tofino.lambda.host'), or null
   *                     when not a Cloudflare request, not a local/development environment,
   *                     or the hot file is absent/invalid.
   */
  private static function get_public_base_url(): ?string
  {
    if (self::$cache_resolved) {
      return self::$public_base_cache;
    }
    self::$cache_resolved = true;

    if (!in_array(wp_get_environment_type(), ['local', 'development'], true)) {
      return null;
    }

    if (!isset($_SERVER['HTTP_CF_RAY'])) {
      return null;
    }

    $hot_path = get_template_directory() . '/dist/hot';
    if (!file_exists($hot_path)) {
      return null;
    }

    $hot_url = trim((string) file_get_contents($hot_path));
    $hot_url = rtrim($hot_url, '/');
    if ($hot_url === '') {
      return null;
    }

    $hot_host = wp_parse_url($hot_url, PHP_URL_HOST);
    if (!is_string($hot_host) || $hot_host === '' || $hot_host === 'localhost' || $hot_host === '127.0.0.1') {
      return null;
    }

    $hot_scheme = wp_parse_url($hot_url, PHP_URL_SCHEME);
    $protocol = $hot_scheme === 'http' ? 'http' : 'https';

    self::$public_base_cache = $protocol . '://' . $hot_host;
    return self::$public_base_cache;
  }
}

CloudflareTunnel::register();
