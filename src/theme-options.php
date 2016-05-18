<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions;

/**
 * Google Analytics
 *
 * Adds Google Analytics JS code in to the footer.
 * Only if WP_DEBUG is false and UA code defined in theme options.
 *
 * @since 1.0.0
 * @return void
 */
function google_analytics() {
  if (!WP_DEBUG && get_theme_mod('google_analytics')) { ?>
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
      ga('create', '<?php echo get_theme_mod('google_analytics'); ?>', 'auto');
      ga('send', 'pageview');
    </script><?php
  }
}
add_action('wp_footer', __NAMESPACE__ . '\\google_analytics');


/**
 * Admin login logo
 *
 * Displays the logo uplaoded via theme options to the login screen.
 *
 * @since 1.0.0
 * @return void
 */
function admin_login_logo() {
  $admin_logo = get_theme_mod('admin_logo');
  if ($admin_logo) { ?>
    <style type="text/css">
      .login h1 a {
        background-image: url(<?php echo $admin_logo; ?>);
        padding-bottom: 30px;
      }
    </style>
    <?php
  }
}
add_action('login_head', __NAMESPACE__ . '\\admin_login_logo');


/**
 * Add theme options to body class
 *
 * Adds the menu-sticky and/or the footer sticky classes to the body.
 *
 * @since 1.0.0
 * @param array $classes Array of classes passed to the body tag by WP.
 * @return void
 */
function add_theme_options_body_class($classes) {
  //Menu Sticky
  if (get_theme_mod('menu_sticky') === 'enabled') {
    $classes[] = 'menu-fixed';
  }

  //Footer Sticky
  if (get_theme_mod('footer_sticky') === 'enabled') {
    $classes[] = 'footer-sticky';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_theme_options_body_class');


/**
 * Menu position
 *
 * Returns menu position classes based on theme option setting.
 *
 * @since 1.0.0
 * @return void
 */
function menu_position() {
  $position = get_theme_mod('menu_position');
  switch ($position) {
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
 * Menu
 *
 * Returns menu sticky class based on theme option setting.
 *
 * @since 1.0.0
 * @return void
 */
function menu_sticky() {
  if (get_theme_mod('menu_sticky') === 'enabled') {
    return 'navbar-sticky-top';
  }
}


/**
 * Notification
 *
 * Display notification Top/Bottom based on theme option setting.
 *
 * @since 1.0.0
 * @param string $position The position of the notification e.g. Top, Bottom
 * @return void
 */
function notification($position) {
  if ($position == get_theme_mod('notification_position')) {
    if (get_theme_mod('notification_text') && !isset($_COOKIE['tofino-notification-closed'])) : ?>
      <!-- Notifcation <?php echo $position; ?> -->
      <div class="alert alert-info notification <?php echo $position; ?>" id="tofino-notification">
        <div class="container">
          <div class="row">
            <div class="col-xs-12">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true"><?php echo svg('icon-close'); ?></span>
                <span class="sr-only"><?php _e('Close', 'tofino'); ?></span>
              </button>
              <p><?php echo nl2br(get_theme_mod('notification_text')); ?></p>
            </div>
          </div>
        </div>
      </div><?php
    endif;
  }
}


/**
 * Sanitize text
 *
 * @since 1.2.0
 * @param string $input The input.
 * @return string Sanitized string.
 */
function sanitize_text($input) {
  return wp_kses_post(force_balance_tags($input));
}


/**
 * Sanitize integer
 *
 * @since 1.2.0
 * @param string|integer $input The input.
 * @return integer validated integer.
 */
function sanitize_integer($input) {
  if (is_numeric($input)) {
    return intval($input);
  }
}


/**
 * Sanitizes choices (selects / radios)
 *
 * Checks that the input matches one of the available choices

 * @param string $input The input.
 * @param object $setting The setting to validate.
 * @since 1.2.0
 */
function sanitize_choices($input, $setting) {
  global $wp_customize;
  $control = $wp_customize->get_control($setting->id);
  if (array_key_exists($input, $control->choices)) {
    return $input;
  } else {
    return $setting->default;
  }
}


/**
 * Add theme options link
 *
 * Add the Customize link to the admin menu.
 *
 * @since 1.2.0
 * @return void
 */
function add_theme_options_link() {
  add_menu_page('Theme Options', 'Theme Options', 'edit_theme_options', 'customize.php');
}
add_action('admin_menu', __NAMESPACE__ . '\\add_theme_options_link');


/**
 * Create WP Customizer panel
 *
 * Create new panel in WP Customizer for Theme options
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function create_panel($wp_customize) {
  $wp_customize->add_panel('tofino_options', [
    'title' => __('Theme Options', 'tofino')
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\create_panel');


/**
 * Menu settings
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function menu_settings($wp_customize) {
  $wp_customize->add_section('tofino_menu_settings', [
    'title'    => __('Menu', 'tofino'),
    'panel'    => 'tofino_options',
    'priority' => 1
  ]);

  $wp_customize->add_setting('menu_sticky', [
    'default'           => 'disabled',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_choices',
  ]);

  $wp_customize->add_control('menu_sticky', [
    'label'       => __('Sticky Menu', 'tofino'),
    'description' => '',
    'section'     => 'tofino_menu_settings',
    'type'        => 'select',
    'choices'     => [
      'enabled'  => __('Enabled', 'tofino'),
      'disabled' => __('Disabled', 'tofino')
    ]
  ]);

  $wp_customize->add_setting('menu_position', [
    'default'           => 'center',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_choices',
  ]);

  $wp_customize->add_control('menu_position', [
    'label'       => __('Menu Position', 'tofino'),
    'description' => '',
    'section'     => 'tofino_menu_settings',
    'type'        => 'select',
    'choices'     => [
      'left'   => __('Left', 'tofino'),
      'center' => __('Center', 'tofino'),
      'right'  => __('Right', 'tofino')
    ]
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\menu_settings');


/**
 * Notification settings
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function notification_settings($wp_customize) {
  $wp_customize->add_section('tofino_notification_settings', [
    'title'    => __('Notification', 'tofino'),
    'panel'    => 'tofino_options',
    'priority' => 2
  ]);

  // Notification text
  $wp_customize->add_setting('notification_text', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('notification_text', [
    'label'       => __('Notification Text', 'tofino'),
    'description' => __('Notification is shown until dismissed (at which point a cookie is set).', 'tofino'),
    'section'     => 'tofino_notification_settings',
    'type'        => 'textarea'
  ]);

  // Notification expires
  $wp_customize->add_setting('notification_expires', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_integer',
  ]);

  $wp_customize->add_control('notification_expires', [
    'label'       => __('Notification Expires', 'tofino'),
    'description' => __('Number of days until the notification expires. Set via a cookie.', 'tofino'),
    'section'     => 'tofino_notification_settings',
    'type'        => 'text'
  ]);

  // Notification position
  $wp_customize->add_setting('notification_position', [
    'default'           => 'top',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_choices',
  ]);

  $wp_customize->add_control('notification_position', [
    'label'       => __('Notification Position', 'tofino'),
    'description' => __('Notification position. Bottom = Fixed over footer. Top = Fixed above top menu.', 'tofino'),
    'section'     => 'tofino_notification_settings',
    'type'        => 'select',
    'choices'     => [
      'top'    => 'Top',
      'bottom' => 'Bottom'
    ]
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\notification_settings');


/**
 * Client data settings
 *
 * Commonly used data. Tel number, company number, address etc.
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function client_data_settings($wp_customize) {
  $wp_customize->add_section('tofino_client_data_settings', [
    'title'    => __('Client Data', 'tofino'),
    'panel'    => 'tofino_options',
    'priority' => 3
  ]);

  // Telephone number
  $wp_customize->add_setting('telephone_number', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('telephone_number', [
    'label'   => __('Telephone Number', 'tofino'),
    'section' => 'tofino_client_data_settings',
    'type'    => 'text'
  ]);

  // Email address
  $wp_customize->add_setting('email_address', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('email_address', [
    'label'   => __('Email address', 'tofino'),
    'section' => 'tofino_client_data_settings',
    'type'    => 'text'
  ]);

  // Address
  $wp_customize->add_setting('address', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('address', [
    'label'   => __('Address', 'tofino'),
    'section' => 'tofino_client_data_settings',
    'type'    => 'textarea'
  ]);

  // Company number
  $wp_customize->add_setting('company_number', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('company_number', [
    'label'   => __('Company number', 'tofino'),
    'section' => 'tofino_client_data_settings',
    'type'    => 'text'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\client_data_settings');


/**
 * Google settings
 *
 * Anaylics, reCAPTCHA etc.
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function google_settings($wp_customize) {
  $wp_customize->add_section('tofino_google_settings', [
    'title'    => __('Google Analytics / reCAPTCHA', 'tofino'),
    'panel'    => 'tofino_options',
    'priority' => 4
  ]);

  // Google analytics
  $wp_customize->add_setting('google_analytics', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('google_analytics', [
    'label'       => __('Google Analytics UA Code', 'tofino'),
    'description' => __('Only runs GA Script when WP_DEBUG is false.', 'tofino'),
    'section'     => 'tofino_google_settings',
    'type'        => 'text'
  ]);

  // Captcha site key
  $wp_customize->add_setting('captcha_site_key', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('captcha_site_key', [
    'label'       => __('reCAPTCHA Site Key', 'tofino'),
    'section'     => 'tofino_google_settings',
    'type'        => 'text'
  ]);

  // Captcha secret
  $wp_customize->add_setting('captcha_secret', [
    'default'           => '',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('captcha_secret', [
    'label'       => __('reCAPTCHA Secret Key', 'tofino'),
    'section'     => 'tofino_google_settings',
    'type'        => 'text'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\google_settings');


/**
 * Social icons
 *
 * Facebook, twitters etc
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function social_settings($wp_customize) {
  $wp_customize->add_section('tofino_social_settings', [
    'title'       => __('Social Networks', 'tofino'),
    'description' => 'Links to various social networks. Remember to add the SVG icon for any new networks you add.',
    'panel'       => 'tofino_options',
    'priority'    => 5
  ]);

  $wp_customize->add_setting('social[facebook]', ['default' => '']);

  $wp_customize->add_control('social[facebook]', [
    'label'   => __('Facebook', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[twitter]', ['default' => '']);

  $wp_customize->add_control('social[twitter]', [
    'label'   => __('Twitter', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[instagram]', ['default' => '']);

  $wp_customize->add_control('social[instagram]', [
    'label'   => __('Instagram', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[googleplus]', ['default' => '']);

  $wp_customize->add_control('social[googleplus]', [
    'label'   => __('GooglePlus', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[linkedin]', ['default' => '']);

  $wp_customize->add_control('social[linkedin]', [
    'label'   => __('LinkedIn', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[pinterest]', ['default' => '']);

  $wp_customize->add_control('social[pinterest]', [
    'label'   => __('Pinterest', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[youtube]', ['default' => '']);

  $wp_customize->add_control('social[youtube]', [
    'label'   => __('YouTube', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);

  $wp_customize->add_setting('social[soundcloud]', ['default' => '']);

  $wp_customize->add_control('social[soundcloud]', [
    'label'   => __('Soundcloud', 'tofino'),
    'section' => 'tofino_social_settings',
    'type'    => 'url'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\social_settings');


/**
 * Footer settings
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function footer_settings($wp_customize) {
  $wp_customize->add_section('tofino_footer_settings', [
    'title'    => __('Footer', 'tofino'),
    'panel'    => 'tofino_options',
    'priority' => 6
  ]);

  $wp_customize->add_setting('footer_sticky', [
    'default'           => 'disabled',
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_choices',
  ]);

  $wp_customize->add_control('footer_sticky', [
    'label'       => __('Sticky Footer', 'tofino'),
    'description' => '',
    'section'     => 'tofino_footer_settings',
    'type'        => 'select',
    'choices'     => [
      'enabled'  => __('Enabled', 'tofino'),
      'disabled' => __('Disabled', 'tofino')
    ]
  ]);

  $wp_customize->add_setting('footer_text', [
    'default' => __('<a href="https://github.com/lambdacreatives/tofino">Tofino</a> theme by <a href="https://github.com/mrchimp">MrChimp</a> and <a href="https://github.com/danimalweb">Danimalweb</a>.', 'tofino'),
    'sanitize_callback' => '\Tofino\ThemeOptions\sanitize_text',
  ]);

  $wp_customize->add_control('footer_text', [
    'label'   => __('Footer Text', 'tofino'),
    'section' => 'tofino_footer_settings',
    'type'    => 'textarea'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\footer_settings');


/**
 * Advacned settings
 *
 * Inline critical css, move jQuery to footer etc.
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function advanced_settings($wp_customize) {
  $wp_customize->add_section('tofino_advanced_settings', [
    'title'    => __('Advanced', 'tofino'),
    'panel'    => 'tofino_options',
    'priority' => 7
  ]);

  $wp_customize->add_setting('no_fout', ['default' => '']);

  $wp_customize->add_control('no_fout', [
    'label'       => __('No FOUT', 'tofino'),
    'description' => __('Enable the body class to remove the FOUT (Flash of unstyled text).', 'tofino'),
    'section'     => 'tofino_advanced_settings',
    'type'        => 'checkbox'
  ]);

  $wp_customize->add_setting('critical_css', ['default' => '']);

  $wp_customize->add_control('critical_css', [
    'label'       => __('Enable Critical CSS', 'tofino'),
    'description' => __('Inject the critical.css file as inline styles in the head tag. Defer the main CSS file in to loadCSS in the footer. Remember to run the styles:critical gulp task.', 'tofino'),
    'section'     => 'tofino_advanced_settings',
    'type'        => 'checkbox'
  ]);

  $wp_customize->add_setting('jquery_footer', ['default' => '']);

  $wp_customize->add_control('jquery_footer', [
    'label'       => __('Move jQuery to Footer', 'tofino'),
    'description' => __('Move jQuery to the footer. Uncheck if you have compatability issues with plugins.', 'tofino'),
    'section'     => 'tofino_advanced_settings',
    'type'        => 'checkbox'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\advanced_settings');


/**
 * Maintenance mode settings
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function maintenance_settings($wp_customize) {
  $wp_customize->add_section('tofino_maintenance_settings', [
    'title'    => __('Maintenance Mode', 'tofino'),
    'panel'    => 'tofino_options',
    'priority' => 8
  ]);

  $wp_customize->add_setting('maintenance_mode', ['default' => '']);

  $wp_customize->add_control('maintenance_mode', [
    'label'       => __('Maintenance Mode', 'tofino'),
    'description' => __('Enabling maintenance mode shows a message on each page in the admin area.', 'tofino'),
    'section'     => 'tofino_maintenance_settings',
    'type'        => 'checkbox'
  ]);

  $wp_customize->add_setting('maintenance_mode_text', ['default' => __('This site is currently in maintenance mode. Any changes you make may be overwritten or removed.', 'tofino')]);

  $wp_customize->add_control('maintenance_mode_text', [
    'label'       => __('Maintenance Mode Text', 'tofino'),
    'description' => __('Notification is shown until dismissed (at which point a cookie is set).', 'tofino'),
    'section'     => 'tofino_maintenance_settings',
    'type'        => 'textarea'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\maintenance_settings');


/**
 * Admin settings
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function admin_settings($wp_customize) {
  $wp_customize->add_section('tofino_admin_settings', [
    'title'    => __('Admin', 'tofino'),
    'panel'    => 'tofino_options',
    'priority' => 9
  ]);

  $wp_customize->add_setting('admin_logo', ['default' => '']);

  $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, 'admin_logo', [
    'label'    => __('Admin Login Logo', 'tofino'),
    'section'  => 'tofino_admin_settings',
    'settings' => 'admin_logo'
  ]));
}
add_action('customize_register', __NAMESPACE__ . '\\admin_settings');
