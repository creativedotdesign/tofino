<?php

namespace Tofino;

class Vite
{
  protected string $hostname = 'http://localhost';
  protected int $port = 3000;
 //  protected string $entry = 'wp-content/themes/tofino/dist/app.js';
  protected string $entry = 'wp-content/themes/tofino/src/js/app.ts';
  protected string $out_dir = 'dist';


  public function __toString(): string
  {

    // var_dump($this->isDev());

    return $this->preloadAssets('woff2')
      . $this->jsTag()
      . $this->jsPreloadImports()
      . $this->cssTag();
  }


  public function entry(string $entry): self
  {
    $this->entry = $entry;
    return $this;
  }

  public function hostname(string $hostname): self
  {
    $this->hostname = $hostname;
    return $this;
  }

  public function port(int $port): self
  {
    $this->port = $port;
    return $this;
  }

  public function outDir(string $dir): self
  {
    $this->out_dir = $dir;
    return $this;
  }

  public function jsUrl(): string
  {
    return $this->assetUrl($this->entry);
  }

  public function cssUrls(): array
  {
    return $this->assetsUrls($this->entry, 'css');
  }

  public function assetUrl(string $entry): string
  {
    $manifest = $this->manifest();

    // var_dump($manifest);

    if (!isset($manifest[$entry])) {
      return '';
    }

    return get_template_directory_uri()
      . '/' . $this->out_dir
      . '/' . ($manifest[$entry]['file']);
  }

  public function assetsUrls(string $entry, string $path = 'dist'): array
  {
    $urls = [];
    $manifest = $this->manifest();

    if (!empty($manifest[$entry][$path])) {
      foreach ($manifest[$entry][$path] as $file) {
        $urls[] = get_template_directory_uri()
          . '/' . $this->out_dir
          . '/' . $file;
      }
    }

    return $urls;
  }

  public function importsUrls(string $entry): array
  {
    $urls = [];
    $manifest = $this->manifest();

    if (!empty($manifest[$entry]['imports'])) {
      foreach ($manifest[$entry]['imports'] as $imports) {
        $urls[] = get_template_directory_uri()
          . '/' . $this->out_dir
          . '/' . $manifest[$imports]['file'];
      }
    }

    return $urls;
  }



  // Helper to output the script tag
  protected function jsTag()
  {
    $url = $this->isDev()
      ? $this->host() . '/' . $this->entry
      : $this->jsUrl();

    if (!$url) {
      return '';
    }

    // return '<script type="module" crossorigin src="'
    //   . $url
    //   . '"></script>';

    wp_register_script('tofino', $url, [], null, true);
    wp_enqueue_script('tofino');
  }

  protected function jsPreloadImports(): string
  {
    if ($this->isDev()) {
      return '';
    }

    $res = '';

    foreach ($this->importsUrls($this->entry) as $url) {
      $res .= '<link rel="modulepreload" href="' . $url . '">';
    }
    return $res;
  }

  // Helper to output style tag
  protected function cssTag()
  {
    // not needed on dev, it's inject by Vite
    if ($this->isDev()) {
      return '';
    }

    // $tags = '';
    foreach ($this->cssUrls() as $url) {
      // $tags .= '<link rel="stylesheet" href="' . $url . '">';

      wp_register_style('tofino', $url);
      wp_enqueue_style('tofino');
    }

    // return $tags;
  }

  protected function preloadAssets(string $type): string
  {
    if ($this->isDev()) {
      return '';
    }

    $res = '';

    foreach ($this->assetsUrls($this->entry) as $url) {
      if (!str_ends_with($url, '.' . $type)) {
        continue;
      }

      if ($type === 'woff2') {
        $res .= '<link rel="preload" href="' . $url .
          '" as="font" type="font/woff2" crossorigin="anonymous">';
      }
    }

    return $res;
  }



  protected function isDev(): bool
  {
    return $this->entryExists();
  }

  protected function host(): string
  {
    return $this->hostname . ':' . $this->port;
  }

  protected function manifest(): array
  {
    $content = file_get_contents(
      get_template_directory()
        . '/' . $this->out_dir
        . '/manifest.json'
    );

    return $content
      ? json_decode($content, true)
      : [];
  }

  // This method is very useful for the local server
  // if we try to access it, and by any means, didn't started Vite yet
  // it will fallback to load the production files from manifest
  // so you still navigate your site as you intended
  protected function entryExists(): bool
  {
    static $exists = null;

    if ($exists !== null) {
      return $exists;
    }

    $handle = curl_init($this->host() . '/' . $this->entry);

    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_NOBODY, true);
    curl_exec($handle);

    $error = curl_errno($handle);

    curl_close($handle);

    return $exists = !$error;
  }
}
