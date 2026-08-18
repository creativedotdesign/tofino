<?php

/**
 * Custom Login runtime.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino;

class CustomLoginForm
{
  /** @var array<string, mixed> ACF options for the login screen. */
  public array $options = [];

  /**
   * Constructor. Checks whether the custom login screen is enabled via ACF
   * options and registers the relevant action hooks.
   */
  public function __construct()
  {
    $enabled = get_field('custom_login_screen', 'option');

    if (!$enabled) {
      return;
    }

    $this->options = get_field('login_screen', 'option') ?: [];

    add_filter('login_body_class', [$this, 'login_body_classes']);
    add_action('login_head', [$this, 'custom_login_colors']);
    add_action('login_head', [$this, 'logo_max_height']);
    add_action('login_form', [$this, 'move_lost_password_link']);
    add_action('login_message', [$this, 'custom_content_before_form'], 12);
    add_action('login_message', [$this, 'admin_login_logo']);
  }

  /**
   * Add server-rendered state classes so the correct logo is present on first
   * paint. The native logo remains visible when no custom upload is configured,
   * allowing a child theme to provide a branded fallback.
   *
   * @param string[] $classes Existing login body classes.
   * @return string[]
   */
  public function login_body_classes(array $classes): array
  {
    $classes[] = 'custom-login-screen';

    if (!empty($this->options['logo'])) {
      $classes[] = 'custom-login-screen-has-logo';
    }

    return $classes;
  }

  /**
   * Outputs an inline CSS custom property for the login button colour.
   */
  public function custom_login_colors(): void
  {
    $button_color = sanitize_hex_color($this->options['button_color'] ?? '');

    if ($button_color) {
      echo '<style>:root{--button-color:' . esc_attr($button_color) . '}</style>';
    }
  }

  /**
   * Outputs an inline CSS rule to constrain the login logo height.
   */
  public function logo_max_height(): void
  {
    $logo_max_height = (int) ($this->options['logo_max_height'] ?? 0);

    if ($logo_max_height > 0) {
      echo '<style>.login-logo{max-height:' . $logo_max_height . 'px}</style>';
    }
  }

  /**
   * Outputs a "Lost password?" link inside the login form box.
   */
  public function move_lost_password_link(): void
  {
    echo '<p class="lost-password-link"><a href="' . esc_url(wp_lostpassword_url()) . '">' . esc_html__('Lost password?', 'tofino') . '</a></p>';
  }

  /**
   * Outputs a heading above the login form. Shows a password-reset heading
   * on the lost password screen, or the custom text from theme options otherwise.
   */
  public function custom_content_before_form(): void
  {
    $action = sanitize_text_field(wp_unslash($_GET['action'] ?? ''));

    if ($action === 'lostpassword') {
      echo '<h1 class="login-title">' . esc_html__('Password Reset', 'tofino') . '</h1>';
    } elseif (!empty($this->options['text'])) {
      echo '<h1 class="login-title">' . esc_html($this->options['text']) . '</h1>';
    }
  }

  /**
   * Outputs the logo uploaded via theme options on the login screen.
   * Renders inline SVG for SVG files, or a standard img tag for raster images.
   */
  public function admin_login_logo(): void
  {
    $admin_logo = $this->options['logo'] ?? null;

    if (!$admin_logo) {
      return;
    }

    $admin_logo_path = get_attached_file($admin_logo);
    $filetype = wp_check_filetype($admin_logo_path);

    echo match ($filetype['ext']) {
      'svg' => svg(['file' => $admin_logo, 'class' => 'login-logo']),
      'png', 'jpg', 'jpeg' => wp_get_attachment_image($admin_logo, 'full', false, ['class' => 'login-logo']),
      default => '',
    };
  }
}

// The custom login screen only ever applies to wp-login.php, and `acf/init`
// does NOT fire there — bootstrap on `login_init`, which does. ACF's get_field
// resolves the saved options fine at this point.
add_action('login_init', static function () {
  new CustomLoginForm();
});
