<?php

/**
 * Config array
 */
$svg_url       = get_template_directory_uri() . '/dist/svg/sprite.symbol.svg';
$svg_file_path = get_template_directory() . '/dist/svg/sprite.symbol.svg';

$theme_config = [
  'svg' => ['sprite_file' => $svg_url . '?v=' . filemtime($svg_file_path)]
];


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
  'src/data-tables/contact-form-data.php',
  "src/forms/contact-form.php",
  "src/lib/nav-walker.php",
  "src/lib/init.php",
  "src/lib/assets.php",
  "src/lib/helpers.php",
  "src/lib/pagination.php",
  "src/shortcodes/copyright.php",
  "src/shortcodes/social-icons.php",
  "src/shortcodes/svg.php",
  "src/shortcodes/theme-option.php",
  "src/theme-options/admin.php",
  "src/theme-options/advanced.php",
  "src/theme-options/client-data.php",
  "src/theme-options/footer.php",
  "src/theme-options/google-analytics.php",
  "src/theme-options/google-recaptcha.php",
  "src/theme-options/init.php",
  "src/theme-options/maintenance-mode.php",
  "src/theme-options/menu.php",
  "src/theme-options/notifications.php",
  "src/theme-options/social-networks.php",
  "src/theme-options/theme-tracker.php",
  "src/theme-options/dashboard-widget.php",
];

foreach ($tofino_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'tofino'), $file), E_USER_ERROR);
  }
  require_once $filepath;
}
unset($file, $filepath);


/**
 * Composer dependencies
 *
 * External dependencies are defined in the composer.json and autoloaded.
 * Use 'composer dump-autoload -o' after adding new files.
 *
 */
if (file_exists(get_template_directory() . '/vendor/autoload.php')) { // Check composer autoload file exists. Result is cached by PHP.
  require_once 'vendor/autoload.php';
} else {
  if (is_admin()) {
    add_action('admin_notices', 'composer_error_notice');
  } else {
    wp_die(composer_error_notice(), __('An error occured.', 'tofino'));
  }
}

// Check for missing dist directory. Result is cached by PHP.
if (!is_dir(get_template_directory() . '/dist')) {
  if (is_admin()) {
    add_action('admin_notices', 'missing_dist_error_notice');
  } else {
    wp_die(missing_dist_error_notice(), __('An error occured.', 'tofino'));
  }
}

// Admin notice for missing composer autoload.
function composer_error_notice() {
?><div class="error notice">
    <p><?php _e('Composer autoload file not found. Run composer install on the command line.', 'tofino'); ?></p>
  </div><?php
}

// Admin notice for missing dist directory.
function missing_dist_error_notice() {
?><div class="error notice">
    <p><?php _e('/dist directory not found. You probably want to run yarn install and npm run dev on the command line.', 'tofino'); ?></p>
  </div><?php
}
