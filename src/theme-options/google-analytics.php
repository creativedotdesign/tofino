<?php
/**
 * Theme Options
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeOptions\GoogleAnalytics;

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
 * Google settings
 *
 * Anaylics, reCAPTCHA etc.
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function google_analytics_settings($wp_customize) {
  $wp_customize->add_section('tofino_google_analytics_settings', [
    'title'    => __('Google Analytics', 'tofino'),
    'priority' => 130
  ]);

  // Google analytics
  $wp_customize->add_setting('google_analytics', [
    'default'           => '',
    'sanitize_callback' => 'sanitize_text_field',
  ]);

  $wp_customize->add_control('google_analytics', [
    'label'       => __('Google Analytics UA Code', 'tofino'),
    'description' => __('Only runs GA Script when WP_DEBUG is false.', 'tofino'),
    'section'     => 'tofino_google_analytics_settings',
    'type'        => 'text'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\google_analytics_settings');
