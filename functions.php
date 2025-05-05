<?php
// Check for dependencies
require_once "inc/lib/dependencies.php";

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
  "inc/lib/class/Vite.php",
  "inc/lib/init.php",
  "inc/lib/assets.php",
  "inc/lib/helpers.php",
  "inc/lib/clean.php",
  "inc/lib/shortcodes.php",
  "inc/lib/class/ACFAutosize.php",
  "inc/lib/layouts.php",
  "inc/lib/class/DisablePostType.php",
  "inc/lib/class/CustomLoginForm.php",
  "inc/lib/class/AuditLogger.php",
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

// Disable Gutenberg (only works on functions.php)
add_filter('use_block_editor_for_post', '__return_false');
