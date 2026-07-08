<?php

/**
 * Machine Readable — markdown twins for singular content.
 *
 * Every supported post gets a markdown version at `{permalink}.md` (homepage:
 * `/index.md`), also reachable via `?format=markdown` or an
 * `Accept: text/markdown` request header. HTML responses advertise the twin
 * through a `Link: rel="alternate"` header and a <link> tag in <head>.
 * Markdown requests are logged as JSON lines (see log_request()).
 *
 * Page bodies serialize their flexible-content modules: a module opts in by
 * declaring `"markdown": "markdown.php"` in its module.json (see
 * docs/modules.md). Modules without a serializer are skipped — nothing is
 * emitted for them, so content a module withholds on the HTML side (e.g. a
 * gate) can never leak here by default. Other post types (and module-less
 * pages) convert `the_content` via html-to-markdown.
 *
 * Loaded by FeatureRegistry when the feature is enabled (General Options →
 * Features). Because feature render files load during theme setup —
 * after `plugins_loaded`, before `init` — the URL-suffix strip and the
 * page-cache opt-out run at include time rather than on those hooks.
 *
 * @package Tofino
 */

namespace Tofino\Features\MachineReadable;

use function Tofino\Helpers\convert_html_to_markdown;
use function Tofino\Helpers\escape_markdown_text;
use function Tofino\Helpers\render_module_markdown;

if (!defined('ABSPATH')) {
  exit;
}

class MarkdownExport
{
  const SUPPORTED_POST_TYPES = ['page', 'post'];

  /** Original REQUEST_URI captured before `.md` suffix stripping. */
  private ?string $original_request_uri = null;

  public function __construct()
  {
    // Order matters: the strip rewrites `/path.md` to `?format=markdown`
    // before the cache check reads the request.
    $this->maybe_strip_md_suffix();
    $this->disable_page_cache_for_markdown();

    add_action('template_redirect', [$this, 'maybe_output_markdown'], 1);
    add_action('template_redirect', [$this, 'advertise_markdown_alternate'], 2);
    add_action('wp_head', [$this, 'emit_link_alternate_tag'], 5);
  }


  /**
   * Post types with markdown twins. Child themes extend via the filter.
   *
   * @return string[]
   */
  private function supported_post_types(): array
  {
    return (array) apply_filters('tofino/machine_readable/post_types', self::SUPPORTED_POST_TYPES);
  }


  /**
   * Rewrites `/some/path.md` requests to `/some/path` and flags the request
   * as markdown. Runs at include time — before parse_request reads the URL.
   */
  private function maybe_strip_md_suffix(): void
  {
    if (empty($_SERVER['REQUEST_URI'])) {
      return;
    }

    // REQUEST_URI is used for parse_url + regex; only unslash, do not run
    // sanitize_text_field here as it would strip valid URL bytes.
    $uri = (string) wp_unslash($_SERVER['REQUEST_URI']);
    $parts = parse_url($uri);
    $path = isset($parts['path']) ? (string) $parts['path'] : '';

    if (!preg_match('#^(.+)\.md(/?)$#', $path, $matches)) {
      return;
    }

    $this->original_request_uri = $uri;

    $new_path = $matches[1] . $matches[2];

    // `/index.md` is the homepage twin (Nginx hosts commonly block `/.md`
    // via a dotfile deny rule). Map it back to `/` so WP resolves the front page.
    if ($new_path === '/index' || $new_path === '/index/') {
      $new_path = '/';
    }
    $query = isset($parts['query']) ? (string) $parts['query'] : '';

    // Ensure format=markdown is in the query string so canonical redirects
    // (e.g. /news → /news/) preserve it in their Location header.
    parse_str($query, $query_vars);
    $query_vars['format'] = 'markdown';
    $query = http_build_query($query_vars);

    $_SERVER['REQUEST_URI'] = $new_path . '?' . $query;
    $_GET['format'] = 'markdown';
    $_REQUEST['format'] = 'markdown';
  }


  private function disable_page_cache_for_markdown(): void
  {
    if ($this->is_markdown_requested() && !defined('DONOTCACHEPAGE')) {
      define('DONOTCACHEPAGE', true);
    }
  }


  public function maybe_output_markdown(): void
  {
    if (!$this->is_markdown_requested() || !$this->is_exportable_view()) {
      return;
    }

    $markdown = $this->build_post_markdown(get_queried_object());

    if (!is_string($markdown) || $markdown === '') {
      return;
    }

    $this->log_request(get_queried_object());

    nocache_headers();
    header('Content-Type: text/markdown; charset=' . get_option('blog_charset'));
    header('Vary: Accept', false);
    echo trim($markdown);
    exit;
  }


  /**
   * Emits an HTTP Link header on HTML responses pointing to the .md twin so
   * crawlers can discover it without parsing the body.
   */
  public function advertise_markdown_alternate(): void
  {
    if ($this->is_markdown_requested() || !$this->is_exportable_view() || headers_sent()) {
      return;
    }

    $url = $this->get_alternate_markdown_url();
    if ($url === '') {
      return;
    }

    header('Link: <' . $url . '>; rel="alternate"; type="text/markdown"', false);
    header('Vary: Accept', false);
  }


  /**
   * Emits a <link rel="alternate" type="text/markdown"> tag inside <head> on
   * supported singular pages.
   */
  public function emit_link_alternate_tag(): void
  {
    if ($this->is_markdown_requested() || !$this->is_exportable_view()) {
      return;
    }

    $url = $this->get_alternate_markdown_url();
    if ($url === '') {
      return;
    }

    printf(
      '<link rel="alternate" type="text/markdown" title="%s" href="%s" />' . "\n",
      esc_attr__('Markdown version', 'tofino'),
      esc_url($url)
    );
  }


  /**
   * Builds the .md twin URL for the current request. Assumes the caller has
   * already verified the view is markdown-exportable.
   */
  private function get_alternate_markdown_url(): string
  {
    $permalink = (string) get_permalink(get_queried_object());
    if ($permalink === '') {
      return '';
    }

    // Home uses `/index.md` rather than `/.md` — dotfile-denying Nginx configs
    // reject any path segment starting with a dot before PHP can rewrite it.
    $is_home = substr_count($permalink, '/') === 3 && substr($permalink, -1) === '/';
    $url = $is_home ? $permalink . 'index.md' : rtrim($permalink, '/') . '.md';

    return esc_url_raw($url);
  }


  /**
   * Whether the current view exposes a single supported WP_Post that we can
   * serialize to markdown. Covers is_singular() and the "Posts page" case
   * (Settings → Reading) where is_home() is true but the queried object is
   * still a Page.
   */
  private function is_exportable_view(): bool
  {
    if (is_404() || is_search()) {
      return false;
    }

    $post = get_queried_object();

    if (!($post instanceof \WP_Post) || post_password_required($post)) {
      return false;
    }

    return in_array($post->post_type, $this->supported_post_types(), true);
  }


  /**
   * Resolves the document H1. Prefers Yoast SEO title (curated for search
   * engines and LLMs), falls back to WP post title.
   */
  private function resolve_seo_title(\WP_Post $post): string
  {
    $title = $this->yoast_meta_for_post($post->ID, 'title');
    if ($title === '') {
      $title = (string) get_the_title($post);
    }
    // Titles arrive HTML-encoded (&amp; etc.) — markdown wants the plain text.
    $title = wp_specialchars_decode(wp_strip_all_tags($title), ENT_QUOTES);
    return trim(preg_replace('/\s+/', ' ', $title));
  }


  /**
   * Resolves the summary line. Prefers Yoast meta description, falls back to
   * a manually-set WP excerpt. Auto-generated excerpts are suppressed.
   */
  private function resolve_seo_description(\WP_Post $post): string
  {
    $desc = $this->yoast_meta_for_post($post->ID, 'description');
    if ($desc !== '') {
      return $desc;
    }
    return has_excerpt($post) ? (string) get_the_excerpt($post) : '';
  }


  /**
   * Builds the "Other languages: [Name](url), …" line from WPML's active
   * languages. Returns '' when WPML is inactive or no translations exist.
   */
  private function build_alternate_languages_line(): string
  {
    $languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 1]);
    if (!is_array($languages) || count($languages) < 2) {
      return '';
    }

    $current = (string) apply_filters('wpml_current_language', null);
    $links = [];

    foreach ($languages as $code => $lang) {
      if ($code === $current || empty($lang['url'])) {
        continue;
      }
      $name = !empty($lang['native_name']) ? $lang['native_name'] : ($lang['translated_name'] ?? $code);
      $links[] = '[' . escape_markdown_text((string) $name) . '](' . esc_url_raw((string) $lang['url']) . ')';
    }

    if (empty($links)) {
      return '';
    }

    return '**Other languages:** ' . implode(', ', $links);
  }


  /**
   * Appends one JSON-line per markdown request to the log file. Silently
   * no-ops if the path is unwritable or logging is disabled.
   *
   * Default path: wp-content/llm-access.log
   * Override by defining LLM_ACCESS_LOG in wp-config.php — set to an absolute
   * path to relocate, or to a falsy value to disable.
   */
  private function log_request(\WP_Post $post): void
  {
    $path = defined('LLM_ACCESS_LOG') ? constant('LLM_ACCESS_LOG') : WP_CONTENT_DIR . '/llm-access.log';

    if (!$path || !is_string($path)) {
      return;
    }

    $dir = dirname($path);
    if (!is_dir($dir) || !is_writable($dir)) {
      return;
    }

    $ua      = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash((string) $_SERVER['HTTP_USER_AGENT'])) : '';
    $referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash((string) $_SERVER['HTTP_REFERER'])) : '';
    $ip      = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash((string) $_SERVER['REMOTE_ADDR'])) : '';
    $uri     = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash((string) $_SERVER['REQUEST_URI'])) : '';

    $entry = [
      'ts'        => gmdate('c'),
      'url'       => $this->original_request_uri ?? $uri,
      'post_id'   => $post->ID,
      'post_type' => $post->post_type,
      'lang'      => (string) apply_filters('wpml_current_language', null),
      'ua'        => $ua,
      'vendor'    => self::classify_user_agent($ua),
      'referer'   => $referer,
      'ip'        => $ip,
      'trigger'   => $this->trigger_label(),
    ];

    @file_put_contents($path, wp_json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);
  }


  /**
   * Coarse vendor label for known AI crawlers and user-fetch agents.
   * Unrecognized agents (including humans) return 'other'.
   */
  private static function classify_user_agent(string $ua): string
  {
    $ua = strtolower($ua);

    $needles = [
      'gptbot'             => 'openai',
      'oai-searchbot'      => 'openai',
      'chatgpt-user'       => 'openai',
      'claudebot'          => 'anthropic',
      'claude-web'         => 'anthropic',
      'anthropic-ai'       => 'anthropic',
      'perplexitybot'      => 'perplexity',
      'perplexity-user'    => 'perplexity',
      'google-extended'    => 'google',
      'googlebot'          => 'google',
      'bingbot'            => 'microsoft',
      'applebot'           => 'apple',
      'meta-externalagent' => 'meta',
      'bytespider'         => 'bytedance',
      'ccbot'              => 'commoncrawl',
      'cohere'             => 'cohere',
      'mistralai'          => 'mistral',
      'diffbot'            => 'diffbot',
    ];

    foreach ($needles as $needle => $vendor) {
      if (strpos($ua, $needle) !== false) {
        return $vendor;
      }
    }

    return 'other';
  }


  /**
   * Which discovery path produced this markdown response.
   */
  private function trigger_label(): string
  {
    // If the strip handler ran, the original URI ended in .md.
    if ($this->original_request_uri !== null) {
      return 'md-url';
    }

    $format = isset($_GET['format']) ? sanitize_key(wp_unslash((string) $_GET['format'])) : '';
    if ($format === 'markdown') {
      return 'query-param';
    }

    return 'accept-header';
  }


  /**
   * Fetches a Yoast meta value (title or description) with template
   * placeholders resolved. Returns '' when Yoast is unavailable or empty.
   */
  private function yoast_meta_for_post(int $post_id, string $field): string
  {
    if (!function_exists('YoastSEO')) {
      return '';
    }

    try {
      // Called by name — Yoast is an optional plugin, so a static reference
      // would be an undefined symbol when it isn't installed.
      $meta = call_user_func('YoastSEO')->meta->for_post($post_id);
    } catch (\Throwable) {
      return '';
    }

    if (!is_object($meta) || empty($meta->{$field})) {
      return '';
    }

    return (string) $meta->{$field};
  }


  private function is_markdown_requested(): bool
  {
    $format = isset($_GET['format']) ? sanitize_key(wp_unslash((string) $_GET['format'])) : '';

    if ($format === 'markdown') {
      return true;
    }

    $accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower(sanitize_text_field(wp_unslash((string) $_SERVER['HTTP_ACCEPT']))) : '';

    return $accept !== '' && str_contains($accept, 'text/markdown');
  }


  private function build_post_markdown(\WP_Post $post): string
  {
    $title = $this->resolve_seo_title($post);
    $title = $title !== '' ? $title : __('Untitled', 'tofino');

    $sections = [
      '# ' . escape_markdown_text($title),
    ];

    $permalink = esc_url_raw((string) get_permalink($post));
    if ($permalink !== '') {
      $sections[] = '**URL:** [' . __('View', 'tofino') . '](' . $permalink . ')';
    }

    $modified = get_post_modified_time('c', true, $post);
    if ($modified) {
      $sections[] = '**Last Updated:** ' . sanitize_text_field((string) $modified);
    }

    $summary = $this->resolve_seo_description($post);
    if ($summary !== '') {
      $sections[] = '**Summary:** ' . convert_html_to_markdown($summary);
    }

    $alternates = $this->build_alternate_languages_line();
    if ($alternates !== '') {
      $sections[] = $alternates;
    }

    /**
     * Lets child themes append document-level metadata lines between the
     * standard header sections and the body (e.g. Published/Byline/Source
     * for a news post) without the parent knowing their fields.
     *
     * @param string[] $sections Markdown sections built so far.
     * @param \WP_Post $post     The post being serialized.
     */
    $sections = (array) apply_filters('tofino/machine_readable/sections', $sections, $post);

    $body = $this->build_body_markdown($post);
    if ($body !== '') {
      $sections[] = $body;
    }

    return implode("\n\n", array_filter($sections));
  }


  private function build_body_markdown(\WP_Post $post): string
  {
    if ($post->post_type === 'page') {
      $modules = $this->build_modules_markdown($post->ID);
      if ($modules !== '') {
        return $modules;
      }
    }

    return convert_html_to_markdown((string) apply_filters('the_content', $post->post_content));
  }


  private function build_modules_markdown(int $post_id): string
  {
    if (!have_rows('content_modules', $post_id)) {
      return '';
    }

    $sections = [];

    while (have_rows('content_modules', $post_id)) {
      the_row();

      $layout = (string) get_row_layout();
      if ($layout === '') {
        continue;
      }

      $module_markdown = render_module_markdown($layout);
      if ($module_markdown === '') {
        continue;
      }

      $sections[] = $module_markdown;
    }

    return trim(implode("\n\n", array_filter($sections)));
  }
}

new MarkdownExport();
