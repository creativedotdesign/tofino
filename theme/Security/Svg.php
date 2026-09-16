<?php

namespace Tofino\Security;

use enshrined\svgSanitize\Sanitizer;

if (!defined('ABSPATH')) {
  exit;
}

/** Shared SVG policy for inline rendering, uploads and Brand imports. */
final class Svg
{
  /**
   * Return sanitized SVG markup, or an error. Callers own paths and file writes.
   */
  public static function sanitize(string $contents): string|\WP_Error
  {
    $error = new \WP_Error('invalid_svg', __('This SVG could not be sanitized and was rejected.', 'tofino'), ['status' => 422]);

    if (trim($contents) === '' || !class_exists(Sanitizer::class)) {
      return $error;
    }

    try {
      $sanitizer = new Sanitizer();
      // Use the library's remote-reference filtering; this is not a ban on all URLs.
      $sanitizer->removeRemoteReferences(true);
      $clean = $sanitizer->sanitize($contents);

      if (!is_string($clean) || trim($clean) === '') {
        return $error;
      }

      $document = new \DOMDocument();
      if (!@$document->loadXML($clean, LIBXML_NONET) || $document->documentElement?->localName !== 'svg') {
        return $error;
      }

      return $clean;
    } catch (\Throwable) {
      return $error;
    }
  }
}
