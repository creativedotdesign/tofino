<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\Hotjar;

/**
 * Hotjar Tracking
 *
 * Adds Hotjar Tracking JS code in to the footer.
 * Only if WP_DEBUG is false and UA code defined in theme options.
 *
 * @since 1.8.0
 * @return void
 */
function hotjar_tracking() {
  if (!WP_DEBUG && get_theme_mod('hotjar_site_id')) { ?>
    <!-- Hotjar Tracking Code -->
    <script>
      (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:<?php echo get_theme_mod('hotjar_site_id'); ?>,hjsv:5};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
      })(window,document,'//static.hotjar.com/c/hotjar-','.js?sv=');
    </script><?php
  }
}
add_action('wp_footer', __NAMESPACE__ . '\\hotjar_tracking');


/**
 * Hotjar settings
 *
 * Heatmaps, Recordings etc.
 *
 * @since 1.8.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function hotjar_tracking_settings($wp_customize) {
  $wp_customize->add_section('tofino_hotjar_tracking_settings', [
    'title'    => __('Hotjar Tracking', 'tofino'),
    'priority' => 135
  ]);

  // Hotjar site ID
  $wp_customize->add_setting('hotjar_site_id', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('hotjar_site_id', [
    'label'       => __('Hotjar Tracking Site ID', 'tofino'),
    'description' => __('Only runs Hotjar Script when WP_DEBUG is false.', 'tofino'),
    'section'     => 'tofino_hotjar_tracking_settings',
    'type'        => 'text'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\hotjar_tracking_settings');
