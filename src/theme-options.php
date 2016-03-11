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
        'id'      => 'critical_css_checkbox',
        'label'   => __('Critical CSS', 'tofino'),
        'desc'    => __('Inject the critical.css file as inline styles in the head tag. Defer the main CSS file in to loadCSS in the footer. Remember to run the styles:critical gulp task.', 'tofino'),
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'other_settings',
        'choices' => array(
          array(
            'value' => false,
            'label' => __('Enable Critical CSS', 'tofino'),
            'src'   => ''
          ),
        )
      ),
      array(
        'id'      => 'jquery_in_footer',
        'label'   => __('jQuery in Footer', 'tofino'),
        'desc'    => __('Move jQuery to the footer. Uncheck if you have compatability issues with plugins.', 'tofino'),
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'other_settings',
        'choices' => array(
          array(
            'value' => false,
            'label' => __('Move jQuery to footer.', 'tofino'),
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
        'id'      => 'cookie_expires',
        'label'   => __('Cookie Expires', 'tofino'),
        'desc'    => __('Number of days until the cookie expires', 'tofino'),
        'std'     => '',
        'type'    => 'text',
        'section' => 'other_settings',
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
        'id'          => 'maintenance_mode_enabled',
        'label'       => __('Maintenance Mode', 'tofino'),
        'desc'        => __('Enabling maintenance mode shows a message on each page in the admin area.', 'tofino'),
        'std'         => '',
        'type'        => 'checkbox',
        'section'     => 'other_settings',
        'choices' => array(
          array(
            'value' => true,
            'label' => __('Enable maintenance mode', 'tofino'),
            'src'   => ''
          ),
        )
      ),
      array(
        'id'      => 'maintenance_mode_text',
        'label'   => __('Maintenance Mode Text', 'tofino'),
        'desc'    => '',
        'std'     => __('This site is currently in maintenance mode. Any changes you make may be overwritten or removed.', 'tofino'),
        'type'    => 'textarea-simple',
        'section' => 'other_settings',
        'rows'    => '4',
      ),
    )
  );

  // Get theme tracker options array
  $tracker_theme_options = \Tofino\ThemeTracker\theme_tracker_options();

  // Get contact form options array
  $contact_form_options = \Tofino\ContactForm\theme_options();

  // Merge arrays
  $custom_settings = array_merge_recursive($custom_settings, $tracker_theme_options);
  $custom_settings = array_merge_recursive($custom_settings, $contact_form_options);

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

add_action('wp_footer', __NAMESPACE__ . '\\google_analytics');

/**
 * Change admin login screen logo
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
add_action('login_enqueue_scripts', __NAMESPACE__ . '\\admin_login_logo');

/**
 * Add menu-sticky and/or the footer sticky classes to the body.
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
 * Return menu position classes based on theme option.
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
 * Return menu sticky class based on theme option.
 */
function menu_sticky() {
  if (get_theme_mod('menu_sticky') === 'enabled') {
    return 'navbar-sticky-top';
  }
}

/**
 * Display notification Top/Bottom based on theme option.
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
 * Adds the individual sections, settings, and controls to the theme customizer
 */

function remove_default_sections($wp_customize) {
  $wp_customize->remove_section('title_tagline');
  $wp_customize->remove_section('static_front_page');
}
add_action('customize_register', __NAMESPACE__ . '\\remove_default_sections');

/*
function remove_default_panels($wp_customize) {
  $wp_customize->remove_panel('nav_menus');
}
add_action('customize_register', __NAMESPACE__ . '\\remove_default_panels', 20);
*/

function create_panel($wp_customize) {
  $wp_customize->add_panel('tofino_options', [
    'title'       => __('Theme Options', 'tofino'),
    'description' => '',
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\create_panel');

function menu_settings($wp_customize) {
  $wp_customize->add_section('tofino_menu_settings', [
    'title' => __('Menu', 'tofino'),
    'panel' => 'tofino_options'
  ]);

  $wp_customize->add_setting('menu_sticky', ['default' => 'disabled']);

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

  $wp_customize->add_setting('menu_position', ['default' => 'center']);

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




function footer_settings($wp_customize) {
  $wp_customize->add_section('tofino_footer_settings', [
    'title' => __('Footer', 'tofino'),
    'panel' => 'tofino_options'
  ]);

  $wp_customize->add_setting('footer_sticky', ['default' => 'disabled']);

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

  $wp_customize->add_setting('footer_text', ['default' => __('<a href="https://github.com/lambdacreatives/tofino">Tofino</a> theme by <a href="https://github.com/mrchimp">MrChimp</a> and <a href="https://github.com/danimalweb">Danimalweb</a>.', 'tofino')]);

  $wp_customize->add_control('footer_text', [
    'label'   => __('Footer Text', 'tofino'),
    'section' => 'tofino_footer_settings',
    'type'    => 'textarea'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\footer_settings');

function advanced_settings($wp_customize) {
  $wp_customize->add_section('tofino_advanced_settings', [
    'title' => __('Advanced', 'tofino'),
    'panel' => 'tofino_options'
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




function notification_settings($wp_customize) {
  $wp_customize->add_section('tofino_notification_settings', [
    'title' => __('Notification', 'tofino'),
    'panel' => 'tofino_options'
  ]);

  $wp_customize->add_setting('notification_text', ['default' => '']);

  $wp_customize->add_control('notification_text', [
    'label'       => __('Notification Text', 'tofino'),
    'description' => __('Notification is shown until dismissed (at which point a cookie is set).', 'tofino'),
    'section'     => 'tofino_notification_settings',
    'type'        => 'textarea'
  ]);

  $wp_customize->add_setting('notification_expires', ['default' => '']);

  $wp_customize->add_control('notification_expires', [
    'label'       => __('Notification Expires', 'tofino'),
    'description' => __('Number of days until the notification expires. Set via a cookie.', 'tofino'),
    'section'     => 'tofino_notification_settings',
    'type'        => 'text'
  ]);

  $wp_customize->add_setting('notification_position', ['default' => 'center']);

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



function maintenance_settings($wp_customize) {
  $wp_customize->add_section('tofino_maintenance_settings', [
    'title' => __('Maintenance Mode', 'tofino'),
    'panel' => 'tofino_options'
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

function admin_settings($wp_customize) {
  $wp_customize->add_section('tofino_admin_settings', [
    'title' => __('Admin', 'tofino'),
    'panel' => 'tofino_options'
  ]);

  $wp_customize->add_setting('admin_logo', ['default' => '']);

  $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, 'admin_logo', [
    'label'    => __('Admin Login Logo', 'tofino'),
    'section'  => 'tofino_admin_settings',
    'settings' => 'admin_logo'
  ]));
}
add_action('customize_register', __NAMESPACE__ . '\\admin_settings');
