<?php

/**
 * Load CSS and JS files
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Assets;


/**
 * Registers shared JavaScript module runtimes that Tofino-only plugins can consume.
 *
 * @since 5.0.0
 *
 * @return void
 */
function shared_script_modules(): void
{
  \Tofino\Integrations\Vite::register_shared_script_modules();
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\shared_script_modules', 1);
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\shared_script_modules', 1);


/**
 * Enqueues the main front-end Vite assets.
 *
 * @since 1.1.0
 *
 * @return void
 */
function main_script(): void
{
  if ($GLOBALS['pagenow'] === 'wp-login.php' || is_admin()) {
    return;
  }

  \Tofino\Integrations\Vite::use_vite();
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\main_script', 10);


/**
 * Passes PHP data to front-end JS via an inline script.
 *
 * @since 1.1.0
 *
 * @return void
 */
function localize_scripts(): void
{
  if ($GLOBALS['pagenow'] === 'wp-login.php' || is_admin()) {
    return;
  }

  $data = [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nextNonce' => wp_create_nonce('next_nonce'),
    'themeUrl' => get_template_directory_uri(),
    'siteURL' => home_url(),
  ];

  if (function_exists('wpml_object_id_filter')) {
    $data['language'] = apply_filters('wpml_current_language', null);
  }

  $data = apply_filters('tofino/localize_data', $data);

  wp_register_script('tofino-data', false);
  wp_enqueue_script('tofino-data');
  wp_add_inline_script(
    'tofino-data',
    'const tofinoJS = ' . wp_json_encode($data) . ';',
  );
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\localize_scripts', 10);


/**
 * Enqueues admin and login Vite assets.
 *
 * @since 1.0.0
 *
 * @return void
 */
function admin_scripts(): void
{
  \Tofino\Integrations\Vite::use_vite('js/admin.ts');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts');
add_action('login_head', __NAMESPACE__ . '\\admin_scripts');


/**
 * Tag theme/module scripts with `type="module"` and defer every other
 * front-end script. Admin and login scripts are left untouched.
 *
 * @param string $tag    Script HTML tag.
 * @param string $handle Script handle.
 * @return string Updated script tag.
 */
function add_defer_attribute(string $tag, string $handle): string
{
  if (str_starts_with($handle, 'tofino') || $handle === 'form-builder' || $handle === 'data-viz') {
    return str_replace('script src', 'script type="module" src', $tag);
  }

  if (!is_admin() && $GLOBALS['pagenow'] !== 'wp-login.php') {
    return str_replace(' src', ' defer src', $tag);
  }

  return $tag;
}
// add_filter('script_loader_tag', __NAMESPACE__ . '\\add_defer_attribute', 10, 2);


/**
 * Outputs the SVG spritemap inline in the footer.
 *
 * @since 3.2.0
 *
 * @return void
 */
function add_svg_sprite_to_footer(): void
{
  // Child theme's sprite wins when present (a child owning the site build
  // generates one complete sprite); falls back to the parent's.
  $svg_sprite = get_theme_file_path('dist/sprite.svg');

  if (file_exists($svg_sprite)) {
    echo file_get_contents($svg_sprite);
  }
}
add_action('wp_footer', __NAMESPACE__ . '\\add_svg_sprite_to_footer');
