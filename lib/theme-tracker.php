<?php

namespace Tofino\ThemeTracker;

function theme_tracker_options() {

  /**
   * Custom settings array that is merged with the main theme options array.
   */
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
        'label'   => __('Theme Tracker', 'tofino'),
        'desc'    => __('Send theme name, theme version, site url, ip address and WP version to the tracker API every 7 days. This data is used to plan future updates.', 'tofino'),
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'tracker',
        'choices' => array(
          array(
            'value' => true,
            'label' => __('Enable theme tracking', 'tofino'),
            'src'   => ''
          ),
        )
      ),
      array(
        'id'        => 'theme_tracker_api_key',
        'label'     => __('Theme Tracker API Key', 'tofino'),
        'desc'      => __('API key required to connect to the tracker API. If empty or invalid data will not be sent.', 'tofino'),
        'std'       => '',
        'type'      => 'text',
        'section'   => 'tracker',
      ),
      array(
        'id'        => 'theme_tracker_api_url',
        'label'     => __('Theme Tracker API URL', 'tofino'),
        'desc'      => __('The API endpoint to send the theme data.', 'tofino'),
        'std'       => 'http://tracker.dev/api/theme',
        'type'      => 'text',
        'section'   => 'tracker',
      ),
    )
  );

}

/**
 * Track theme usage.
 */
function theme_tracker() {
  if (ot_get_option('theme_tracker_enabled')) { // Only if enabled

    delete_transient('theme_tracking'); // Used to clear the transient for testing

    if (false === ($result = get_transient('theme_tracking'))) {

      $url           = ot_get_option('theme_tracker_api_url');
      $api_key       = ot_get_option('theme_tracker_api_key');
      $theme_data    = wp_get_theme();
      $theme_name    = $theme_data->get('Name');
      $theme_version = $theme_data->get('Version');
      $theme_author  = $theme_data->get('Author');
      $server_ip     = (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'Unknown');

      $data = array(
        'theme_name'        => $theme_name,
        'theme_version'     => $theme_version,
        'theme_author'      => $theme_author,
        'site_name'         => get_bloginfo('name'),
        'site_url'          => get_site_url(),
        'ip_address'        => $server_ip,
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
        'body' => json_encode($data)
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
