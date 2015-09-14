<?php
/**
 * Tofino includes
 *
 * The $tofino_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 */
$tofino_includes = [
  'vendor/autoload.php',  // Composer Autoload classes
  'lib/assets.php',
  'lib/init.php'
];
foreach ($tofino_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'tofino'), $file), E_USER_ERROR);
  }
  require_once $filepath;
}
unset($file, $filepath);
