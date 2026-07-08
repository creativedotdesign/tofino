<?php

/**
 * Load CSS and JS files
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\Assets;


/**
 * Prints the page-wide CSS cascade-layer order, inline, before any stylesheet.
 *
 * Layer order is fixed by the FIRST mention of each name in document order, so
 * this single statement is authoritative no matter what enqueues after it.
 * It is a PLATFORM guarantee: Tofino-family plugins scope their Tailwind
 * builds into the lowest `plugins` layer (per-part `layer(plugins)` imports)
 * and rely on that layer sitting below the theme's — on every Tofino site,
 * child-themed or not. Without this, plugin sheets that enqueue first would
 * establish their own order (empirically `plugins` can land ABOVE utilities,
 * letting plugin preflight/utilities beat theme CSS).
 *
 *   properties  Tailwind's @property fallback layer (leaks unscoped from every
 *               TW v4 build — tailwindcss#15005 — so it's pinned lowest)
 *   plugins     every plugin's generated Tailwind (theme/preflight/utilities)
 *   theme/base/components/utilities  the standard Tailwind layers
 *   brand       highest — per-brand overrides of plugin CSS (Compose)
 *
 * A child theme printing the same statement is a harmless duplicate (first
 * mention wins). Do NOT reorder without auditing every plugin's app.css.
 *
 * @since 5.0.0
 *
 * @return void
 */
function cascade_layer_order(): void
{
  echo '<style>@layer properties, plugins, theme, base, components, utilities, brand;</style>' . "\n";
}
add_action('wp_head', __NAMESPACE__ . '\\cascade_layer_order', 0);


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

  // Module slugs whose manifest resolves outside this theme (child theme or
  // plugin override) — the front-end loader skips this theme's script for
  // them so the overriding source's script isn't double-bound.
  $overridden = [];
  foreach (\Tofino\Registry\ModuleManifest::all() as $module) {
    if (!str_starts_with($module['_dir'], get_template_directory())) {
      $overridden[] = basename($module['_dir']);
    }
  }

  $data = [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nextNonce' => wp_create_nonce('next_nonce'),
    'themeUrl' => get_template_directory_uri(),
    'siteURL' => home_url(),
    'overriddenModules' => $overridden,
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
