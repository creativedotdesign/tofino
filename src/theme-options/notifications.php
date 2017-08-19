<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\Notifications;

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
    'priority' => 120
  ]);

  // Notification text
  $wp_customize->add_setting('notification_text', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('notification_text', [
    'label'       => __('Notification Text', 'tofino'),
    'description' => __('Notification is shown until dismissed (at which point a cookie is set).', 'tofino'),
    'section'     => 'tofino_notification_settings',
    'type'        => 'textarea'
  ]);

  // Notification expires
  $wp_customize->add_setting('notification_expires', [
    'default'           => 999,
    'sanitize_callback' => 'absint',
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
    'sanitize_callback' => '\Tofino\Helpers\sanitize_choices',
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

  $wp_customize->add_setting('notification_use_js', ['default' => '']);

  $wp_customize->add_control('notification_use_js', [
    'label'       => __('Display Notifications using Javascript', 'tofino'),
    'description' => __('Work around for when the website has a static cache.', 'tofino'),
    'section'     => 'tofino_notification_settings',
    'type'        => 'checkbox'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\notification_settings');


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
  if ($position == get_theme_mod('notification_position', 'top')) {
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
 * Adds the menu-sticky classes to the body.
 *
 * @since 1.9.0
 * @param array $classes Array of classes passed to the body tag by WP.
 * @return void
 */
function add_notification_class($classes) {
  if (get_theme_mod('notification_use_js') === true) {
    $classes[] = 'notification-use-js';
  }
  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_notification_class');
