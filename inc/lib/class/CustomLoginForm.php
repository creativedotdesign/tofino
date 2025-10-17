<?php

/**
 * Custom login form
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino;

class CustomLoginForm {
  public $options = [];

  public function __construct()
  {
    $enabled = get_field('custom_login_screen', 'option');

    if ($enabled) {
      $this->options = get_field('login_screen', 'option');

      add_action('login_head', [$this, 'custom_login_colors']);
      add_action('login_head', [$this, 'logo_max_height']);
      add_action('login_form', [$this, 'move_lost_password_link']);
      add_action('login_message', [$this, 'custom_content_before_form'], 12);
      add_action('login_message', [$this, 'admin_login_logo']);
      add_filter('login_head', [$this, 'add_custom_class_to_login_body'], 10, 1);
    }
  }

  // Add custom class to login body
  public function add_custom_class_to_login_body()
  { 
    echo '<script type="text/javascript"> document.addEventListener("DOMContentLoaded", () => { document.body.classList.add("custom-login-screen"); });</script>';
    echo '<style type="text/css">body.login { display: none; }</style>';
  }

  // Add custom login colors
  public function custom_login_colors()
  {
    $button_color = $this->options['button_color'];
  
    if ($button_color) {
      echo '<style type="text/css">:root { --button-color: ' . $button_color . ' }</style>';
    }
  }

  // Add custom logo max height
  public function logo_max_height()
  {
    $logo_max_height = $this->options['logo_max_height'];

    if ($logo_max_height) {
      echo '<style type="text/css">.login-logo { max-height: ' . $logo_max_height . 'px; }</style>';
    }
  }
  
  // Move 'Lost your password?' link into the login form box
  public function move_lost_password_link()
  {
    $lost_password_url = wp_lostpassword_url();
  
    echo '<p class="lost-password-link"><a href="' . esc_url($lost_password_url) . '">' . __('Lost password?', 'tofino') . '</a></p>';
  }

  // Add content at the top of the login form
  public function custom_content_before_form()
  {
    if (isset($_GET['action']) && $_GET['action'] === 'lostpassword') {
      echo '<h1 class="login-title">Password Reset</h1>';
    } else if ($this->options['text']) {
      echo '<h1 class="login-title">' . $this->options['text'] . '</h1>';
    }
  }

  /**
   * Admin login logo
   *
   * Displays the logo uplaoded via theme options to the login screen.
   *
   * @since 5.0.0
   * @return void
   */
  public function admin_login_logo()
  {
    $admin_logo = $this->options['logo'];

    if ($admin_logo) {
      // Get the file path of the logo
      $admin_logo_path = get_attached_file($admin_logo);

      // Check file type of the logo
      $filetype = wp_check_filetype($admin_logo_path);

      if ($filetype['ext'] === 'svg') {
        echo svg(['file' => $admin_logo, 'class' => 'login-logo']);
      } else if ($filetype['ext'] === 'png' || $filetype['ext'] === 'jpg' || $filetype['ext'] === 'jpeg') {
        echo wp_get_attachment_image($admin_logo, 'full', false, ['class' => 'login-logo']);
      }
    }
  }
}

add_filter('acf/init', function() {
  new CustomLoginForm(); 
});
