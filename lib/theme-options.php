<?php

namespace Tofino\ThemeOptions;

//Remove layout option
add_filter('ot_show_new_layout', '__return_false');

//Remove default text
add_filter('ot_header_version_text', '__return_false');

//Remove logo
add_filter('ot_header_logo_link', '__return_false');

//Set 'ot_theme_mode' filter to true.
add_filter('ot_theme_mode', '__return_true');

//Hide Option Tree settings menu item
add_filter('ot_show_pages', '__return_false');

//Remove default social media types
add_filter('ot_type_social_links_load_defaults', '__return_false');

/**
 * Build the custom settings & update OptionTree.
 *
 * @return    void
 * @since     2.3.0
 */
function custom_theme_options() {

  /* OptionTree is not loaded yet */
  if (!function_exists('ot_settings_id')) {
    return false;
  }

  /**
   * Get a copy of the saved settings array.
   */
  $saved_settings = get_option(ot_settings_id(), array());

  /**
   * Custom settings array that will eventually be
   * passes to the OptionTree Settings API Class.
   */

  $custom_settings = array(
    'contextual_help' => array(
      'content'       => array(),
    ),
    'sections' => array(
      array(
        'id'    => 'general_settings',
        'title' => __('General', 'tofino')
      ),
      array(
        'id'    => 'menu_settings',
        'title' => __('Menu', 'tofino')
      ),
      array(
        'id'    => 'other_settings',
        'title' => __('Other', 'tofino')
      ),
      array(
        'id'    => 'contact_form',
        'title' => __('Contact Form', 'tofino')
      ),
    ),
    'settings' => array(
      array(
        'id'      => 'admin_login_logo_id',
        'label'   => __('Admin Login Logo', 'tofino'),
        'desc'    => '',
        'std'     => '',
        'type'    => 'upload',
        'section' => 'general_settings',
        'class'   => 'ot-upload-attachment-id',
      ),
      array(
        'id'      => 'google_analytics',
        'label'   => __('Google Analytics UA Code', 'tofino'),
        'desc'    => __('Only runs GA Script when WP_DEBUG is false.', 'tofino'),
        'std'     => '',
        'type'    => 'text',
        'section' => 'general_settings'
      ),
      array(
        'id'      => 'social_links',
        'label'   => __('Social Links', 'tofino'),
        'desc'    => '',
        'std'     => '',
        'type'    => 'social-links',
        'section' => 'general_settings',
      ),
      array(
        'id'          => 'telephone_number',
        'label'       => __('Telephone Number', 'tofino'),
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'general_settings',
      ),
      array(
        'id'          => 'email_address',
        'label'       => __('Email Address', 'tofino'),
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'general_settings',
      ),
      array(
        'id'      => 'address',
        'label'   => __('Address', 'tofino'),
        'desc'    => '',
        'std'     => '',
        'type'    => 'textarea-simple',
        'section' => 'general_settings',
        'rows'    => '4',
      ),
      array(
        'id'          => 'company_number',
        'label'       => __('Company Number', 'tofino'),
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'general_settings',
       ),
      array(
        'id'      => 'footer_text',
        'label'   => __('Footer Text', 'tofino'),
        'desc'    => '',
        'std'     => __('<a href="https://github.com/lambdacreatives/tofino">Tofino</a> theme by <a href="https://github.com/mrchimp">MrChimp</a> and <a href="https://github.com/danimalweb">Danimalweb</a>.', 'tofino'),
        'type'    => 'textarea-simple',
        'section' => 'general_settings',
        'rows'    => '3',
      ),
      array(
        'id'      => 'menu_fixed_checkbox',
        'label'   => __('Menu', 'tofino'),
        'desc'    => '',
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'menu_settings',
        'choices' => array(
          array(
            'value' => false,
            'label' => __('Disable Fixed Menu', 'tofino'),
            'src'   => ''
          ),
        )
      ),
      array(
        'id'      => 'menu_position_select',
        'label'   => __('Menu Position', 'tofino'),
        'desc'    => '',
        'std'     => '',
        'type'    => 'select',
        'section' => 'menu_settings',
        'choices' => array(
          array(
            'value' => 'left',
            'label' => __('Left', 'tofino'),
            'src'   => ''
          ),
          array(
            'value'  => 'center',
            'label'  => __('Center', 'tofino'),
            'src'    => ''
          ),
          array(
            'value' => 'right',
            'label' => __('Right', 'tofino'),
            'src'   => ''
          )
        )
      ),
      array(
        'id'      => 'footer_sticky_checkbox',
        'label'   => __('Sticky Footer', 'tofino'),
        'desc'    => __('Flexbox supported browsers only.', 'tofino'),
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'other_settings',
        'choices' => array(
          array(
            'value' => false,
            'label' => __('Enable Sticky Footer', 'tofino'),
            'src'   => ''
          ),
        )
      ),
      array(
        'id'      => 'notification_text',
        'label'   => __('Notification Text', 'tofino'),
        'desc'    => __('Notification is shown until dismissed (at which point a cookie is set).', 'tofino'),
        'std'     => '',
        'type'    => 'textarea-simple',
        'section' => 'other_settings',
        'rows'    => '3'
      ),
      array(
        'id'      => 'notification_position',
        'label'   => __('Notification Position', 'tofino'),
        'desc'    => __('Notification position. Bottom = Fixed over footer. Top = Fixed above top menu.', 'tofino'),
        'std'     => 'bottom',
        'type'    => 'select',
        'section' => 'other_settings',
        'choices' => array(
          array(
            'value' => 'top',
            'label' => __('Top', 'tofino'),
            'src'   => ''
          ),
          array(
            'value'  => 'bottom',
            'label'  => __('Bottom', 'tofino'),
            'src'    => ''
          ),
        )
      ),
      array(
        'id'          => 'contact_form_to_address',
        'label'       => __('TO email address', 'tofino'),
        'desc'        => __('Email address used in the TO field. Leave blank to use the email address defined in General Settings.', 'tofino'),
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'          => 'contact_form_cc_address',
        'label'       => __('CC email address', 'tofino'),
        'desc'        => __('Email address used in the CC field.', 'tofino'),
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'          => 'contact_form_from_address',
        'label'       => __('FROM email address', 'tofino'),
        'desc'        => __('Email address used in the FROM field. Leave blank for server default.', 'tofino'),
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'          => 'contact_form_email_subject',
        'label'       => __('Email Subject', 'tofino'),
        'desc'        => __('The subject field. Leave blank for "Form submission from SERVER_NAME".', 'tofino'),
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'      => 'contact_form_success_message',
        'label'   => __('Success Message', 'tofino'),
        'desc'    => __('Message displayed to use after form action is successful.', 'tofino'),
        'std'     => __("Thanks, we'll be in touch soon.", 'tofino'),
        'type'    => 'textarea-simple',
        'section' => 'contact_form',
        'rows'    => '3'
      ),
      array(
        'id'      => 'enable_captcha_checkbox',
        'label'   => __('Enable reCaptcha', 'tofino'),
        'desc'    => __('Enable Google reCaptcha "I am not a robot".', 'tofino'),
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'contact_form',
        'choices' => array(
          array(
            'value' => true,
            'label' => __('Enable reCaptcha', 'tofino'),
            'src'   => ''
          ),
        )
      ),
      array(
        'id'          => 'captcha_site_key',
        'label'       => __('reCaptcha Site Key', 'tofino'),
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'          => 'captcha_secret',
        'label'       => __('reCaptcha Secret Key', 'tofino'),
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
    )
  );

  /* allow settings to be filtered before saving */
  $custom_settings = apply_filters(ot_settings_id() . '_args', $custom_settings);

  /* settings are not the same update the DB */
  if ($saved_settings !== $custom_settings) {
    update_option(ot_settings_id(), $custom_settings);
  }

  /* Lets OptionTree know the UI Builder is being overridden */
  global $ot_has_custom_theme_options;
  $ot_has_custom_theme_options = true;

}

/**
 * Initialize the custom Theme Options.
 */
add_action('admin_init', __NAMESPACE__ . '\\custom_theme_options');

/**
 * Load Google Analyrics
 */
add_action('wp_footer', __NAMESPACE__ . '\\google_analytics');

function google_analytics() {
  if (!WP_DEBUG && ot_get_option('google_analytics')) { ?>
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
      ga('create', '<?php echo ot_get_option('google_analytics'); ?>', 'auto');
      ga('send', 'pageview');
    </script><?php
  }
}

/**
 * Change admin login screen logo
 */
add_action('login_enqueue_scripts', __NAMESPACE__ . '\\admin_login_logo');

function admin_login_logo() {
  if (ot_get_option('admin_login_logo_id')) {
    $src = wp_get_attachment_image_src(ot_get_option('admin_login_logo_id'), 'original'); ?>
    <style type="text/css">
      .login h1 a {
        background-image: url(<?php echo $src[0]; ?>);
        padding-bottom: 30px;
      }
    </style>
    <?php
  }
}

/**
 * Add menu-sticky and/or the footer sticky classes to the body.
 */
function add_theme_options_body_class($classes) {
  //Menu Sticky
  $menu_sticky_disabled = ot_get_option('menu_fixed_checkbox');
  if (!$menu_sticky_disabled) {
    $classes[] = 'menu-fixed';
  }

  //Footer Sticky
  $footer_sticky_enabled = ot_get_option('footer_sticky_checkbox');
  if ($footer_sticky_enabled) {
    $classes[] = 'footer-sticky';
  }

  return $classes;
}

add_filter('body_class', __NAMESPACE__ . '\\add_theme_options_body_class');

/**
 * Return menu position classes based on theme option.
 */
function menu_position() {
  $position = ot_get_option('menu_position_select');
  switch ($position) {
    case 'left':
      $class = '';
      break;
    case 'center':
      $class = 'menu-center';
      break;
    case 'right':
      $class = 'menu-right';
      break;
    default:
      $class = null;
  }
  return $class;
}

/**
 * Return menu fixed class based on theme option.
 */
function menu_fixed() {
  $is_disabled = ot_get_option('menu_fixed_checkbox');
  if (!$is_disabled) {
    $class = 'navbar-sticky-top';
  } else {
    $class = null;
  }
  return $class;
}

function notification($position) {
  if ($position == ot_get_option('notification_position')) {
    if (ot_get_option('notification_text') && !isset($_COOKIE['tofino-notification-closed'])) : ?>
      <!-- Notifcation <?php echo $position; ?> -->
      <div class="alert alert-info notification <?php echo $position; ?>" id="tofino-notification">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
          <span class="sr-only">Close</span>
        </button>
        <p><?php echo ot_get_option('notification_text') ?></p>
      </div><?php
    endif;
  }
}
