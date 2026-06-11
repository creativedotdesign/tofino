<?php

/**
 * Module registry.
 *
 * Auto-loads ACF files from registered module manifests, tracks
 * which ACF field groups belong to which module, and injects matching
 * flexible-content layouts into the content_modules field.
 *
 * Manifest-based PHP layouts take precedence over any DB/GUI layout of the same
 * name; overridden layouts are reported via an admin notice.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Registry;

final class ModuleRegistry
{
  /** @var array<string, array{group_key: string, manifest: array<string, string>}> */
  private array $modules = [];

  /** @var string[] layout names overridden by manifest-based PHP layouts */
  private array $overridden = [];

  public static function boot(): void
  {
    $instance = new self();
    add_action('after_setup_theme', [$instance, 'load_module_cpts']);
    add_action('acf/include_fields', [$instance, 'load_module_fields']);
    add_action('admin_notices',      [$instance, 'render_override_notice']);
  }

  /**
   * Require every module's optional `cpt` file. A module that owns a custom
   * post type declares it in module.json ("cpt": "cpt.php") and the file
   * registers it the usual way (register_post_type on init, field groups on
   * acf/init). Runs on after_setup_theme so those hooks are registered in
   * time regardless of whether the module ships in a theme or a plugin.
   */
  public function load_module_cpts(): void
  {
    foreach (ModuleManifest::all() as $manifest) {
      if (!empty($manifest['cpt'])) {
        $file = ModuleManifest::file($manifest, 'cpt');

        if ($file) {
          require_once $file;
        }
      }
    }
  }

  /**
   * Require every module's ACF file, record which group key each
   * module registered, then register the parent __Page Modules group that
   * holds the content_modules flexible-content field. Runs on
   * acf/include_fields so every local group is available before
   * inject_layouts fires on the flex field.
   */
  public function load_module_fields(): void
  {
    foreach (ModuleManifest::all() as $name => $manifest) {
      $acf = $manifest['acf'] ?? null;
      $dir = $manifest['_dir'] ?? null;

      if (!$acf || !$dir) {
        continue;
      }

      require_once $dir . '/' . $acf;
      $this->modules[$name] = [
        'group_key' => 'group_module_' . $name,
        'manifest' => $manifest,
      ];
    }

    $this->register_page_modules_group();

    add_filter('acf/load_field/name=content_modules', [$this, 'inject_layouts'], 5);
  }

  /**
   * Registers the parent __Page Modules field group containing the
   * content_modules flexible-content field. Layouts are populated at
   * render time by inject_layouts().
   */
  private function register_page_modules_group(): void
  {
    if (!function_exists('acf_add_local_field_group')) {
      return;
    }

    acf_add_local_field_group([
      'key' => 'group_62583ddaa0897',
      'title' => '__Page Modules',
      'fields' => [
        [
          'key' => 'field_62586c9af1a1a',
          'label' => 'Content Modules',
          'name' => 'content_modules',
          'type' => 'flexible_content',
          'layouts' => [],
          'button_label' => 'Add Row',
          'show_in_graphql' => 1,
          'graphql_field_name' => 'contentModules',
        ],
      ],
      'location' => [
        [
          ['param' => 'post_type', 'operator' => '==', 'value' => 'page'],
        ],
      ],
      'position' => 'normal',
      'style' => 'seamless',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => [
        'block_editor',
        'the_content',
        'discussion',
        'comments',
        'revisions',
        'slug',
        'author',
        'format',
        'categories',
        'tags',
        'send-trackbacks',
      ],
      'active' => true,
      'show_in_rest' => false,
    ]);
  }

  /**
   * Merge manifest-sourced layouts into content_modules, replacing any existing
   * layout whose name collides.
   *
   * @param array<string, mixed> $field
   * @return array<string, mixed>
   */
  public function inject_layouts(array $field): array
  {
    $layouts = $this->build_auto_layouts();

    if (!$layouts) {
      return $field;
    }

    $existing = is_array($field['layouts'] ?? null) ? $field['layouts'] : [];
    $names    = array_column($layouts, 'name');

    foreach ($existing as $key => $layout) {
      if (in_array($layout['name'] ?? '', $names, true)) {
        $this->overridden[] = $layout['name'];
        unset($existing[$key]);
      }
    }

    $field['layouts'] = array_merge($existing, $layouts);

    return $field;
  }

  /**
   * Build flexible-content layouts from the field groups registered by each
   * module manifest.
   *
   * @return array<string, array<string, mixed>>
   */
  private function build_auto_layouts(): array
  {
    if (!function_exists('acf_get_field_group') || !function_exists('acf_get_fields')) {
      return [];
    }

    $out = [];

    foreach ($this->modules as $name => $module) {
      $group_key = $module['group_key'];
      $manifest = $module['manifest'];
      $group = acf_get_field_group($group_key);
      $fields = acf_get_fields($group_key);

      if (!$group || !is_array($fields) || !$fields) {
        continue;
      }

      $out["layout_$name"] = [
        'key'        => "layout_$name",
        'name'       => $name,
        'label'      => (string) ($manifest['title'] ?? $group['title']),
        'display'    => 'block',
        'sub_fields' => $fields,
        'min'        => '',
        'max'        => '',
      ];
    }

    return $out;
  }

  public function render_override_notice(): void
  {
    if (!$this->overridden || !function_exists('get_current_screen')) {
      return;
    }

    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, ['post', 'toplevel_page_acf-options'], true)) {
      return;
    }

    $list = implode(', ', array_map('esc_html', array_unique($this->overridden)));

    printf(
      '<div class="notice notice-info"><p>%s <strong>%s</strong></p></div>',
      esc_html__('Module PHP override active. These GUI/DB layouts are currently ignored because matching module folder definitions exist:', 'tofino'),
      $list
    );
  }
}

ModuleRegistry::boot();
