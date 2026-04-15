<?php

namespace Tofino;

/**
 * Fragment Cache
 *
 * Caches arbitrary output buffers using WordPress transients. When a persistent
 * object cache (e.g. Redis, Memcached) is configured, the transient functions
 * delegate to it automatically.
 *
 * @package Tofino
 * @since 1.7.0
 *
 * @link https://codex.wordpress.org/Class_Reference/WP_Object_Cache
 * @link https://gist.github.com/markjaquith/2653957
 */
class FragmentCache
{
  private string $key;
  private int $ttl;

  /**
   * @param string $key The transient key used to store and retrieve the cached output.
   * @param int    $ttl Time-to-live in seconds. 0 means the transient never expires.
   */
  public function __construct(string $key, int $ttl)
  {
    $this->key = $key;
    $this->ttl = $ttl;
  }

  /**
   * Attempts to output the cached fragment.
   *
   * If a cached value exists it is echoed immediately and the method returns
   * true. Otherwise an output buffer is started so the caller can generate the
   * content and pass it to {@see store()}.
   *
   * @return bool True if the fragment was served from cache, false if output buffering has begun.
   */
  public function output(): bool
  {
    $output = get_transient($this->key);

    if (!empty($output)) {
      echo $output;
      return true;
    }

    ob_start();
    return false;
  }

  /**
   * Flushes the current output buffer and stores its contents in the transient.
   *
   * Should only be called after {@see output()} returned false.
   *
   * @return void
   */
  public function store(): void
  {
    $output = ob_get_clean();

    if ($output !== false) {
      set_transient($this->key, $output, $this->ttl);
    }
  }
}
