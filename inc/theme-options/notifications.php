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
    'default' => ''
  ]);

  $wp_customize->add_control(new \Text_Editor_Custom_Control($wp_customize, 'notification_text', [
    'label'       => __('Notification Text', 'tofino'),
    'description' => __('Notification is shown until dismissed (at which point a cookie is set).', 'tofino'),
    'settings'    => 'notification_text',
    'section'     => 'tofino_notification_settings',
    'priority'    => 10
  ]));

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
      <div class="flex items-center bg-blue-400 text-white text-sm font-bold px-4 py-3 alert notification <?php echo $position; ?>" role="alert"  id="tofino-notification">
        <div class="container flex justify-between">
          <span><?php echo nl2br(get_theme_mod('notification_text')); ?></span>

          <button type="button" class="w-5 h-5 js-close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true" class="text-white"><?php echo svg(['sprite' => 'icon-close', 'class' => 'w-full current-color h-full']); ?></span>
            <span class="sr-only"><?php _e('Close', 'tofino'); ?></span>
          </button>
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
