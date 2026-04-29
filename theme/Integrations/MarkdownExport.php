<?php

/**
 * Markdown export endpoint for singular content.
 *
 * Serves Markdown when requests include `Accept: text/markdown` or
 * `?format=markdown`.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Integrations;

use function Tofino\Helpers\convert_html_to_markdown;
use function Tofino\Helpers\escape_markdown_text;
use function Tofino\Helpers\render_module_markdown;

class MarkdownExport
{
  public function __construct()
  {
    add_action('plugins_loaded', [$this, 'disable_page_cache_for_markdown']);
    add_action('template_redirect', [$this, 'maybe_output_markdown'], 1);
  }


  /**
   * Disables page-level caching for Markdown requests.
   *
   * @since 5.0.0
   * @return void
   */
  public function disable_page_cache_for_markdown(): void
  {
    if ($this->is_markdown_requested() && !defined('DONOTCACHEPAGE')) {
      define('DONOTCACHEPAGE', true);
    }
  }


  /**
   * Serves Markdown output for supported singular requests.
   *
   * @since 5.0.0
   * @return void
   */
  public function maybe_output_markdown(): void
  {
    if (!$this->is_markdown_requested() || !is_singular()) {
      return;
    }

    $post = get_queried_object();

    if (!($post instanceof \WP_Post) || post_password_required($post)) {
      return;
    }

    if (!in_array($post->post_type, ['page'], true)) {
      return;
    }

    $markdown = $this->build_post_markdown($post);

    if (!is_string($markdown) || $markdown === '') {
      return;
    }

    nocache_headers();
    header('Content-Type: text/markdown; charset=' . get_option('blog_charset'));
    echo trim($markdown);
    exit;
  }


  /**
   * Determines whether the current request explicitly asks for Markdown.
   *
   * @since 5.0.0
   * @return bool
   */
  private function is_markdown_requested(): bool
  {
    $format = isset($_GET['format']) ? sanitize_key(wp_unslash((string) $_GET['format'])) : '';

    if ($format === 'markdown') {
      return true;
    }

    $accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower((string) wp_unslash($_SERVER['HTTP_ACCEPT'])) : '';

    return $accept !== '' && str_contains($accept, 'text/markdown');
  }


  /**
   * Builds Markdown output for a post.
   *
   * @since 5.0.0
   *
   * @param \WP_Post $post The post object.
   * @return string Markdown.
   */
  private function build_post_markdown(\WP_Post $post): string
  {
    $title = sanitize_text_field((string) get_the_title($post));
    $title = $title !== '' ? $title : __('Untitled', 'tofino');
    $sections = [
      '# ' . escape_markdown_text($title),
    ];

    $permalink = esc_url_raw((string) get_permalink($post));

    if ($permalink !== '') {
      $sections[] = '**URL:** [View page](' . $permalink . ')';
    }

    $modified = get_post_modified_time('c', true, $post);
    if ($modified) {
      $sections[] = '**Last Updated:** ' . sanitize_text_field((string) $modified);
    }

    $excerpt = (string) get_the_excerpt($post);
    $excerpt_markdown = convert_html_to_markdown($excerpt);
    if ($excerpt_markdown !== '') {
      $sections[] = '**Summary:** ' . $excerpt_markdown;
    }

    $module_markdown = $this->build_modules_markdown($post->ID);

    if ($module_markdown !== '') {
      $sections[] = $module_markdown;
    } else {
      $post_content_markdown = convert_html_to_markdown((string) $post->post_content);
      if ($post_content_markdown !== '') {
        $sections[] = $post_content_markdown;
      }
    }

    return implode("\n\n", array_filter($sections));
  }


  /**
   * Builds markdown by iterating ACF flexible content module rows.
   *
   * @since 5.0.0
   *
   * @param int $post_id The post ID.
   * @return string Module markdown output.
   */
  private function build_modules_markdown(int $post_id): string
  {
    if (!have_rows('content_modules', $post_id)) {
      return '';
    }

    $sections = [];

    while (have_rows('content_modules', $post_id)) {
      the_row();

      $layout_raw = (string) get_row_layout();
      if ($layout_raw === '') {
        continue;
      }

      $module_markdown = render_module_markdown($layout_raw);

      if ($module_markdown === '') {
        continue;
      }

      $sections[] = $module_markdown;
    }

    return trim(implode("\n\n", array_filter($sections)));
  }
}

new MarkdownExport();
