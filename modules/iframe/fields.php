<?php

/**
 * iFrame module — ACF field registration + tofinoJS integration.
 *
 * Registers the post-level module fields, appends an iframe-resizer license
 * field onto the theme's general-options group, and injects the license
 * into tofinoJS so the module's front-end script can pick it up. Keeping
 * all of this in one file means removing the module folder is enough to
 * drop the feature cleanly.
 */

namespace Tofino\Modules\Iframe;

acf_add_local_field_group([
  'key' => 'group_module_iframe',
  'title' => 'iFrame',
  'fields' => [
    [
      'key' => 'field_67102392ffa8e',
      'label' => 'URL',
      'name' => 'iframe_url',
      'type' => 'url',
    ],
    [
      'key' => 'field_6710243cffa8f',
      'label' => 'Add CSS container class',
      'name' => 'iframe_container',
      'type' => 'true_false',
      'default_value' => 0,
      'ui' => 1,
    ],
  ],
  'location' => [],
  'active' => true,
]);


/**
 * Append the license field onto the theme's general-options group so the
 * option lives alongside the other theme settings rather than spawning a
 * separate card.
 */
function register_options_field(): void
{
  $group_key = find_options_group_key();

  if ($group_key === null) {
    return;
  }

  acf_add_local_field([
    'key' => 'field_module_iframe_tab',
    'label' => 'iFrame',
    'type' => 'tab',
    'parent' => $group_key,
    'menu_order' => 900,
  ]);

  acf_add_local_field([
    'key' => 'field_671c0c73e5f41',
    'label' => 'iFrame Resizer License Key',
    'name' => 'iframe_resizer_license_key',
    'type' => 'text',
    'instructions' => 'See https://iframe-resizer.com/ for more details.',
    'default_value' => 'GPLv3',
    'parent' => $group_key,
    'menu_order' => 901,
  ]);
}
add_action('acf/init', __NAMESPACE__ . '\\register_options_field');


/**
 * Find the first locally-registered field group targeting the theme's
 * general-options page.
 */
function find_options_group_key(): ?string
{
  foreach (acf_get_local_field_groups() as $group) {
    foreach ($group['location'] ?? [] as $rule_group) {
      foreach ($rule_group as $rule) {
        if (($rule['param'] ?? '') === 'options_page'
          && ($rule['operator'] ?? '') === '=='
          && ($rule['value'] ?? '') === 'general-options'
        ) {
          return $group['key'];
        }
      }
    }
  }

  return null;
}


/**
 * Inject the iframe-resizer license into the tofinoJS localize payload.
 */
function add_license_to_localize_data(array $data): array
{
  $license = get_field('iframe_resizer_license_key', 'option');

  if ($license) {
    $data['iframeResizerLicense'] = $license;
  }

  return $data;
}
add_filter('tofino/localize_data', __NAMESPACE__ . '\\add_license_to_localize_data');
