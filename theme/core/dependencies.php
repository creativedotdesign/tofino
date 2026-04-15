<?php

/**
 * Theme dependencies functions
 *
 * @package Tofino
 * @since 4.3.0
 */

namespace Tofino\Dependencies;


/**
 * Returns an admin notice HTML string.
 *
 * @param string $title   The bold label shown before the message.
 * @param string $message The notice body text.
 * @return string The formatted HTML notice markup.
 */
function render_notice(string $title, string $message): string
{
  return '<div class="error notice"><p><strong>' . esc_html($title) . '</strong> - ' . esc_html($message) . '</p></div>';
}


/**
 * Returns the admin notice for a missing Composer autoload file.
 *
 * @return string
 */
function composer_error_notice(): string
{
  return render_notice(
    __('Theme Error', 'tofino'),
    __('Composer autoload file not found. Run composer install on the command line.', 'tofino')
  );
}


/**
 * Returns the admin notice for a missing dist directory.
 *
 * @return string
 */
function missing_dist_error_notice(): string
{
  return render_notice(
    __('Theme Error', 'tofino'),
    __('/dist directory not found. You probably want to run npm install and npm run build on the command line.', 'tofino')
  );
}


/**
 * Returns the admin notice for a missing ACF Pro plugin.
 *
 * @return string
 */
function missing_acf_plugin_notice(): string
{
  return render_notice(
    __('Missing Plugin', 'tofino'),
    __('Advanced Custom Fields Pro plugin not found. Please install it.', 'tofino')
  );
}


/**
 * Returns the admin notice for a missing ACF Extended plugin.
 *
 * @return string
 */
function missing_acf_extended_plugin_notice(): string
{
  return render_notice(
    __('Missing Plugin', 'tofino'),
    __('Advanced Custom Fields: Extended plugin not found. Please install it.', 'tofino')
  );
}


/**
 * Checks for the ACF Extended plugin and registers an admin notice if missing.
 *
 * @return void
 */
function check_acf_extended_plugin(): void
{
  if (!is_plugin_active('acf-extended/acf-extended.php')) {
    add_action('admin_notices', function () {
      echo missing_acf_extended_plugin_notice();
    });
  }
}


// Load Composer autoload or die with a clear error.
if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
  require_once get_template_directory() . '/vendor/autoload.php';
} else {
  if (is_admin()) {
    add_action('admin_notices', function () {
      echo composer_error_notice();
    });
  } else {
    wp_die(composer_error_notice(), __('An error occurred.', 'tofino'));
  }
}


// Check for a built dist directory.
if (!is_dir(get_template_directory() . '/dist')) {
  if (is_admin()) {
    add_action('admin_notices', function () {
      echo missing_dist_error_notice();
    });
  } else {
    wp_die(missing_dist_error_notice(), __('An error occurred.', 'tofino'));
  }
}


// Check for ACF Pro plugin.
if (!class_exists('acf')) {
  if (is_admin()) {
    add_action('admin_notices', function () {
      echo missing_acf_plugin_notice();
    });
  } elseif (!str_contains($_SERVER['PHP_SELF'] ?? '', 'wp-login.php')) {
    wp_die(missing_acf_plugin_notice(), __('An error occurred.', 'tofino'));
  }
}


// Check for ACF Extended plugin.
add_action('admin_init', __NAMESPACE__ . '\\check_acf_extended_plugin');
