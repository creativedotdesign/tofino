<?php
// Check for dependencies
require_once "theme/core/dependencies.php";

$tofino_autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($tofino_autoload)) {
  require_once $tofino_autoload;
}
unset($tofino_autoload);

/**
 * Tofino includes
 *
 * The $tofino_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed.
 *
 * Missing files will produce a fatal error.
 *
 */
$tofino_includes = [
  "theme/Integrations/Vite.php",
  "theme/Integrations/CloudflareTunnel.php",
  "theme/Registry/FolderManifest.php",
  "theme/Registry/FeatureRegistry.php",
  "theme/core/init.php",
  "theme/core/assets.php",
  "theme/core/nav.php",
  "theme/utils/helpers.php",
  "theme/utils/shortcodes.php",
  "theme/Registry/ModuleRegistry.php",
  "theme/Integrations/MarkdownExport.php",
  // Options pages and field groups
  "settings/admin.php",
  "settings/footer.php",
  "settings/client-data.php",
  "theme/Integrations/GraphQL.php",
];

foreach ($tofino_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'tofino'), $file), E_USER_ERROR);
  }

  if (class_exists('acf')) {
    require_once $filepath;
  }
}
unset($file, $filepath);
