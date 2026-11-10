<?php

/**
 * Disables the built-in "post" post type.
 *
 * Loaded by FeatureRegistry only when the "Posts" feature is toggled off in
 * the Features admin screen. Hides the Posts admin UI, strips the rewrite
 * rules, and redirects any front-end single-post request to the home page.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino;

/** Disable the Posts feature while preserving its settings and stored content. */
class PostsDisabler
{

  /** Register admin and frontend behavior for the disabled Posts feature. */
  public function __construct()
  {
    // Priority 11 runs after WP's create_initial_post_types() (init priority 0)
    // so $wp_post_types['post'] is populated.
    add_action('init', [$this, 'disable_post_type_rewrite'], 11);
    add_action('admin_menu', [$this, 'remove_post_menu']);
    add_action('admin_bar_menu', [$this, 'remove_new_post_admin_bar'], 999);
    add_action('template_redirect', [$this, 'redirect_single_post']);
  }

  /** Hide the Posts screen from the admin menu. */
  public function remove_post_menu(): void
  {
    remove_menu_page('edit.php');
  }

  /** Remove the shortcut for creating a new post. */
  public function remove_new_post_admin_bar(\WP_Admin_Bar $wp_admin_bar): void
  {
    $wp_admin_bar->remove_node('new-post');
  }

  /** Make the built-in post type unavailable for public queries and rewriting. */
  private function disable_post_type_props(): void
  {
    global $wp_post_types;

    if (!isset($wp_post_types['post'])) {
      return;
    }

    $wp_post_types['post']->public = false;
    $wp_post_types['post']->publicly_queryable = false;
    $wp_post_types['post']->query_var = false;
    $wp_post_types['post']->rewrite = false;
  }

  /** Disable post rewriting in every active WPML language. */
  public function disable_post_type_rewrite(): void
  {
    $this->disable_post_type_props();

    if (!defined('ICL_SITEPRESS_VERSION')) {
      return;
    }

    global $sitepress;

    $default_language = $sitepress->get_default_language();

    foreach ($sitepress->get_active_languages() as $lang) {
      $sitepress->switch_lang($lang['code']);
      $this->disable_post_type_props();
    }

    $sitepress->switch_lang($default_language);
  }

  /** Redirect single-post requests when the Posts feature is disabled. */
  public function redirect_single_post(): void
  {
    if (is_single() && get_post_type() === 'post') {
      wp_safe_redirect(home_url(), 301);
      exit;
    }
  }
}

new PostsDisabler();
