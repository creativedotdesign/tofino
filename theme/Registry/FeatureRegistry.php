<?php

/**
 * Feature folder registry.
 *
 * Auto-discovers feature folders under features/{slug}/, loads render.php
 * and fields.php for enabled features, and provides an ACF toggle screen
 * to disable features entirely (no hooks, no options page, no field groups).
 *
 * ACF handles the UI and field persistence. On save, an acf/save_post hook
 * syncs the toggle state into a single wp_option (tofino_disabled_features)
 * which is read via get_option() at boot — before ACF initialises — so
 * there is no circular dependency.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Registry;

final class FeatureRegistry
{
  private const OPTION_KEY = 'tofino_disabled_features';

  /** @var array<string, array<string, string>> All discovered feature manifests keyed by slug */
  private array $features = [];

  /** @var string[] Slugs that are currently disabled */
  private array $disabled = [];

  public static function boot(): void
  {
    $instance = new self();
    $instance->discover();
    $instance->load_runtime();
    // Run after settings/admin.php registers the 'general-options' parent
    // page (acf/init priority 10). Otherwise sub-pages register while the
    // parent is still absent from $admin_page_hooks, producing bare-slug
    // menu hrefs instead of admin.php?page=… URLs (WP menu-header.php hook
    // name mismatches between registration and render time).
    add_action('acf/init', [$instance, 'load_fields'], 20);
    add_action('acf/init', [$instance, 'register_toggle_fields'], 20);
    add_action('acf/save_post', [$instance, 'sync_disabled_features'], 20);
    add_action('admin_menu', [$instance, 'remove_disabled_menus'], 999);
  }

  /**
   * Scan the features directory for all feature folders and load the
   * disabled list from the database.
   */
  private function discover(): void
  {
    $this->features = FolderManifest::all('features');
    $this->disabled = get_option(self::OPTION_KEY, []);
  }

  private function is_enabled(string $slug): bool
  {
    return !in_array($slug, $this->disabled, true);
  }

  /**
   * Require each feature's runtime file immediately so its functions, hooks,
   * and shortcodes are available during the request. Enabled features load
   * the `render` file; disabled features load the `disabled` file (if any),
   * which lets a feature ship "off-mode" behaviour such as removing a default
   * WordPress post type.
   */
  public function load_runtime(): void
  {
    foreach ($this->features as $slug => $manifest) {
      $key = $this->is_enabled($slug) ? 'render' : 'disabled';
      $file = $manifest[$key] ?? null;

      if ($file) {
        require_once $manifest['_dir'] . '/' . $file;
      }
    }
  }

  /**
   * Require every enabled feature's fields.php when ACF is ready to
   * register local field groups.
   */
  public function load_fields(): void
  {
    foreach ($this->features as $slug => $manifest) {
      if (!$this->is_enabled($slug)) {
        continue;
      }

      $acf = $manifest['acf'] ?? null;

      if ($acf) {
        require_once $manifest['_dir'] . '/' . $acf;
      }
    }
  }

  /**
   * Register the Features options sub-page and a field group with a
   * true/false toggle for each discovered feature.
   */
  public function register_toggle_fields(): void
  {
    if (!function_exists('acf_add_options_sub_page') || !function_exists('acf_add_local_field_group')) {
      return;
    }

    acf_add_options_sub_page([
      'page_title' => 'Features',
      'menu_title' => 'Features',
      'menu_slug' => 'tofino-features',
      'parent_slug' => 'general-options',
      'capability' => 'manage_options',
      'autoload' => false,
    ]);

    $fields = [];

    foreach ($this->features as $slug => $manifest) {
      $key = str_replace('-', '_', $slug);
      $label = $manifest['title'] ?? ucwords(str_replace('-', ' ', $slug));

      $fields[] = [
        'key' => 'field_feature_' . $key,
        'label' => $label,
        'name' => 'feature_' . $key . '_enabled',
        'type' => 'true_false',
        'default_value' => 1,
        'ui' => 1,
      ];
    }

    acf_add_local_field_group([
      'key' => 'group_tofino_features',
      'title' => 'Features',
      'fields' => $fields,
      'location' => [
        [['param' => 'options_page', 'operator' => '==', 'value' => 'tofino-features']],
      ],
      'style' => 'default',
      'label_placement' => 'left',
      'instruction_placement' => 'label',
      'active' => true,
    ]);
  }

  /**
   * Remove admin menu items for disabled features. Catches both PHP-registered
   * and DB-persisted (ACFE) options pages. Uses the feature's menu_slug which
   * should match the folder slug by convention.
   */
  public function remove_disabled_menus(): void
  {
    foreach ($this->disabled as $slug) {
      remove_submenu_page('general-options', $slug);
    }
  }

  /**
   * After any options page is saved, sync the ACF toggle values into a
   * single wp_option so is_enabled() can read it at boot time without
   * depending on ACF's internal storage format.
   *
   * Runs on every options save (not just the Features page) but the cost
   * is negligible — a few get_field calls and a conditional update_option.
   */
  public function sync_disabled_features($post_id): void
  {
    if ($post_id !== 'options') {
      return;
    }

    $disabled = [];

    foreach (array_keys($this->features) as $slug) {
      $key = 'feature_' . str_replace('-', '_', $slug) . '_enabled';
      $value = get_field($key, 'option');

      // null = never saved (new feature), treat as enabled.
      // false/0 = explicitly toggled off.
      if ($value !== null && !$value) {
        $disabled[] = $slug;
      }
    }

    update_option(self::OPTION_KEY, $disabled, false);
    // Persistent object caches (Redis/Memcached) won't see update_option
    // writes for the non-autoloaded options bucket, so bust it explicitly.
    wp_cache_delete(self::OPTION_KEY, 'options');
  }

}

FeatureRegistry::boot();
