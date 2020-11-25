<?php
namespace Tofino;

/**
 * Fragement Cache
 *
 * Uses Transients. If persistent caching is configured, then the transients
 * functions will use the wp_cache.
 *
 * @package Tofino
 * @since 1.7.0
 *
 * @url https://codex.wordpress.org/Class_Reference/WP_Object_Cache
 * @url https://gist.github.com/markjaquith/2653957
 */
class FragmentCache {
  private $key = null;
  private $ttl = null;

  public function __construct($key, $ttl) {
    $this->key = $key;
    $this->ttl = $ttl;
  }

  public function output() {
    $output = get_transient($this->key);
    if (!empty($output)) { // It was in the cache
      echo $output;
      return true;
    } else {
      ob_start();
      return false;
    }
  }

  public function store() {
    $output = ob_get_flush(); // Flushes the buffers
    set_transient($this->key, $output, $this->ttl);
  }
}
