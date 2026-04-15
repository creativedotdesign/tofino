<?php

/**
 * Disable Post Type runtime.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino;

class DisablePostType
{
  private bool $is_disabled = false;

  /**
   * Constructor. Defers the ACF option check until ACF has fully initialised.
   */
  public function __construct()
  {
    add_action('acf/init', [$this, 'initialize']);
  }

  /**
   * Reads the ACF option and registers the relevant hooks when the post type
   * is disabled. Called on the `acf/init` action.
   */
  public function initialize(): void
  {
    $this->is_disabled = (bool) get_field('disable_post_type', 'option');

    if (!$this->is_disabled) {
      return;
    }

    add_action('admin_menu', [$this, 'remove_post_menu']);
    add_action('admin_bar_menu', [$this, 'remove_new_post_admin_bar'], 999);
    $this->disable_post_type_rewrite();
    add_action('template_redirect', [$this, 'redirect_single_post']);
  }

  /**
   * Removes the "Posts" menu item from the WordPress admin sidebar.
   */
  public function remove_post_menu(): void
  {
    remove_menu_page('edit.php');
  }

  /**
   * Removes the "New Post" link from the admin toolbar.
   *
   * @param \WP_Admin_Bar $wp_admin_bar The admin bar instance.
   */
  public function remove_new_post_admin_bar(\WP_Admin_Bar $wp_admin_bar): void
  {
    $wp_admin_bar->remove_node('new-post');
  }

  /**
   * Sets the post type properties that mark it as private and removes its
   * rewrite rules. Extracted to avoid duplication in the WPML language loop.
   */
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

  /**
   * Disables the built-in "post" post type on the front end and removes its
   * rewrite rules. When WPML is active, applies the settings for every active
   * language so WPML does not restore the rewrite rules on a language switch.
   */
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

  /**
   * Redirects any front-end request for a single post to the home page.
   */
  public function redirect_single_post(): void
  {
    if (is_single() && get_post_type() === 'post') {
      wp_safe_redirect(home_url(), 301);
      exit;
    }
  }
}

new DisablePostType();
