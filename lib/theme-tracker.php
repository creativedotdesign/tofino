<?php
/**
 * Theme tracker
 *
 * Options and function for tracking theme usage.
 *
 * @package Tofino
 * @since 1.0.0
 */

namespace Tofino\ThemeTracker;

/**
 * Theme tracker options
 *
 * Custom settings array that is merged with the main theme options array.
 *
 * @since 1.0.0
 * @return void
 */
function theme_tracker_options() {
  return array(
    'contextual_help' => array(
      'content'       => array(),
    ),
    'sections' => array(
      array(
        'id'    => 'tracker',
        'title' => __('Theme Tracker', 'tofino')
      ),
    ),
    'settings' => array(
      array(
        'id'      => 'theme_tracker_enabled',
        'label'   => __('Enable Theme Tracker', 'tofino'),
        'desc'    => __('Send theme name, theme version, site url, ip address and WP version to the tracker API every 7 days. This data is used to plan future updates.', 'tofino'),
        'std'     => 'enabled',
        'type'    => 'select',
        'section' => 'tracker',
        'choices' => array(
          array(
            'value' => 'enabled',
            'label' => __('Enabled', 'tofino'),
            'src'   => ''
          ),
          array(
            'value'  => 'disabled',
            'label'  => __('Disabled', 'tofino'),
            'src'    => ''
          ),
        )
      ),
      array(
        'id'        => 'theme_tracker_api_key',
        'label'     => __('Theme Tracker API Key', 'tofino'),
        'desc'      => __('API key required to connect to the tracker.', 'tofino'),
        'std'       => '',
        'type'      => 'text',
        'section'   => 'tracker',
      ),
      array(
        'id'        => 'theme_tracker_api_url',
        'label'     => __('Theme Tracker API Url', 'tofino'),
        'desc'      => __('The API endpoint to send the theme data.', 'tofino'),
        'std'       => 'http://tracker.lambdacreatives.com/api/v1/theme',
        'type'      => 'text',
        'section'   => 'tracker',
      ),
      array(
        'id'      => 'theme_tracker_debug',
        'label'   => __('Theme Tracker Debug Mode', 'tofino'),
        'desc'    => __('Send data continuously. Ignore transient time.', 'tofino'),
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'tracker',
        'choices' => array(
          array(
            'value' => true,
            'label' => __('Enable debug mode', 'tofino'),
            'src'   => ''
          ),
        )
      ),
    )
  );
}


/**
 * Missing API key notice
 *
 * Displays error notice at the top of the admin screen if theme tracking is enabled
 * and the API key is missing.
 *
 * @since 1.0.0
 * @return void
 */
function missing_apikey_notice() {
  if (ot_get_option('theme_tracker_enabled') !== 'disabled' && !ot_get_option('theme_tracker_api_key')) { ?>
    <div class="error notice">
      <p><?php _e('Theme tracking is enabled but is missing the API Key.', 'tofino'); ?></p>
    </div><?php
  }
}
add_action('admin_notices', __NAMESPACE__ . '\\missing_apikey_notice', 1);


/**
 * Track theme usage.
 *
 * Sends theme data via HTTP a request to the tracker API as JSON data.
 * Uses transient for low performance impact.
 * Hooked in to wp_footer action.
 *
 * @since 1.0.0
 * @return void
 */
function theme_tracker() {
  if (ot_get_option('theme_tracker_enabled') !== 'disabled') { // Only if enabled

    if (ot_get_option('theme_tracker_debug')) {
      delete_transient('theme_tracking'); // Used to clear the transient for testing
    }

    if (false === ($result = get_transient('theme_tracking'))) {

      // Check for uid, else generate one. Uid is per site and generated once.
      if (get_option('theme_tracking_uid')) {
        $uid = get_option('theme_tracking_uid');
      } else {
        $uid = uniqid();
        add_option('theme_tracking_uid', $uid);
      }

      $url           = ot_get_option('theme_tracker_api_url');
      $api_key       = ot_get_option('theme_tracker_api_key');
      $theme_data    = wp_get_theme();
      $theme_name    = $theme_data->get('Name');
      $theme_version = $theme_data->get('Version');
      $theme_author  = $theme_data->get('Author');
      $server_ip     = (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'Unknown');
      $environment   = (getenv('WP_ENV') ? getenv('WP_ENV') : 'Unknown'); // For Bedrock installs

      $data = array(
        'uid'               => $uid,
        'theme_name'        => $theme_name,
        'theme_version'     => $theme_version,
        'theme_author'      => $theme_author,
        'site_name'         => get_bloginfo('name'),
        'site_url'          => get_site_url(),
        'ip_address'        => $server_ip,
        'environment'       => $environment,
        'wordpress_version' => get_bloginfo('version')
      );

      // Send the API key as a http header
      $headers = array(
        'Content-Type'  => 'application/json',
        'Authorization' => $api_key
      );

      // Use wp_remote_post to make the http request
      $response = wp_remote_post(esc_url_raw($url), array(
        'headers' => $headers,
        'timeout' => 10,
        'body'    => json_encode($data)
      ));

      if (is_wp_error($response)) { // Request error occured.
        $error_message = $response->get_error_message();
        error_log('[' . __('Theme Tracker API Error', 'tofino') . '] ' . $error_message); // Log error in webservers errorlog
        $result = false;
        set_transient('theme_tracking', $result, 60*60*2); // Set the transient to try again in 2 hours
        exit;
      }

      if (json_decode($response['body'])) { // Response body is valid JSON
        $json_response = json_decode(wp_remote_retrieve_body($response));

        if ($json_response->error == false) {
          $result = true;
        } else { // Valid JSON, with error.
          error_log('[' . __('Theme Tracker API Error', 'tofino') . '] ' . $json_response->message); // Log error in webservers errorlog
          $result = false;
        }
      } else { // Invlid response received
        error_log('[' . __('Theme Tracker API Error', 'tofino') . '] ' . __('Invalid resposne (not JSON) received from the API endpoint.', 'tofino')); // Log error in webservers errorlog
        $result = false;
      }

      // Set the transient to send data again in 7 days
      set_transient('theme_tracking', $result, 60*60*168); //sec*min*hours

    }

  }
}
add_action('wp_footer', __NAMESPACE__ . '\\theme_tracker');
