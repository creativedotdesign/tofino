<?php
/**
 * AjaxForm
 *
 * @package Tofino
 * @since 1.2.0
 */

namespace Tofino;

// Alias Respect Validation classes
// use \Respect\Validation\Validator as v;
// use \Respect\Validation\Exceptions\NestedValidationExceptionInterface;

/**
 * Ajax Form
 *
 * Class of functions for easier processing WP Ajax requests.
 *
 * @package Tofino
 * @since 1.2.0
 */
class AjaxForm
{

  public $validators = [];
  private $post      = [];
  private $form_data = [];
  private $response  = [
    'success' => false,
    'message' => '',
    'type'    => '',
    'extra'   => []
  ];


  /**
   * Construct
   *
   * Filter POST input.
   * Parse form data field to PHP array.
   * Add data_time key value.
   *
   * @since 1.2.0
   * @return void
   */
  public function __construct()
  {
    $this->post = $_POST;

    // Decode the JSON Array
    $this->form_data = json_decode(stripslashes($this->post['data']), true);

    // Sanaize the form data
    $this->form_data = $this->sanitize_form_data($this->form_data);
  }


  private function sanitize_form_data($data)
  {
    $sanitized_data = [];

    foreach ($data as $key => $value) {
      $sanitized_data[$key] = sanitize_text_field($value);
    }

    return $sanitized_data;
  }

  /**
   * Nonce validation
   *
   * @since 1.2.0
   * @uses wp_verify_nonce()
   * @param string $nonce Token from frontend form
   * @return boolean If the token was valid or not.
   */
  private function isValidNonce($nonce)
  {
    if (!wp_verify_nonce($nonce, 'next_nonce')) { // Compare Nonce from POST request to server generated nonce
      $this->response['message'] = __('Security check failed.', 'tofino');
      return false;
    } else {
      return true;
    }
  }


  /**
   * Get recipient
   *
   * Get recipient email address from the form specific Theme Options.
   * Fallback to general email address if not defined.
   * Return error in JSON format if no email addresses found.
   *
   * @since 1.2.0
   * @param string $theme_option_field The option field to check for the email address.
   * @return string|json The email address if found, else JSON array output.
   */
  public function getRecipient($theme_option_field)
  {
    if ($theme_option_field) {
      $contact_form_settings = get_field($theme_option_field, 'general-options');

      if ($contact_form_settings) {
        $recipient = $contact_form_settings['to_address']; // Email address from contact form options

        if ($recipient) {
          return $recipient;
        } else {
          $this->response['message'] = __('No email address found.', 'tofino');
          return json_encode($this->response);
        }
      } else {
        $this->response['message'] = __('No contact form settings found.', 'tofino');
        
        return wp_send_json($this->response);
      }
    } else {
      $this->response['message'] = __('Theme Option not passed to getRecipient function.', 'tofino');
      
      return wp_send_json($this->response);
    }
  }


  /**
   * Save data
   *
   * Save form data as post meta.
   *
   * @since 1.2.0
   * @uses add_post_meta()
   *
   * @param integer $post_id The id of the post to attach the meta data.
   * @param string $meta_key The key to use.
   * @param array $data The data
   *
   * @return integer|boolean The saved meta id or false if save failed.
   */
  public function saveData($post_id, $meta_key)
  {
    if ($post_id && $meta_key) {
      $meta_id = add_post_meta($post_id, $meta_key, $this->form_data, false);
      if (0 < intval($meta_id)) {
        return $meta_id;
      } else {
        return false;
      }
    }
  }


  /**
   * Get the form data
   *
   * @since 1.2.0
   * @return array The form data array
   */
  public function getData()
  {
    return $this->form_data;
  }


  /**
   * Build email body
   *
   * Genereate email body using html template.
   *
   * @since 1.2.0
   * @param string $template The filename of HTML the template to use.
   * @return string HTML output ready to be sent via email.
   */
  private function buildEmailBody(array $settings)
  {
    if (is_array($this->form_data)) {
      $form_content = null;
      if ($settings['remove_submit_data'] == false) {
        $form_content = '<table>';
        
        foreach ($this->form_data as $key => $value) { // Loop through each array item ouput the key value as a string
          if (!in_array($key, $settings['remove_keys'])) { // Check key is not in remove keys array
            if ($key == 'date_time') { // Convert unix timestamp to human readable date
              $value = current_time('d-M-Y H:i:s');
            }

            if (is_array($value)) {
              $value = implode("-", $value);
            }

            $key_name = str_replace('_', ' ', $key);
            $key_name = ucfirst($key_name);

            $form_content .= '<tr>';
            $form_content .= '<td><strong>' . $key_name . '</strong></td>';
            $form_content .= '<td style="padding-left: 1.25rem;">' . ($value ? $value : 'Empty') . '</td>';
            $form_content .= '</tr>';
          }
        }

        $form_content .= '</table>';
      }
    }

    $message = file_get_contents(get_template_directory() . '/templates/email/' . $settings['template']); // Get the template.

    // Replace custom named variables inside of the email template
    if (array_key_exists('replace_variables', $settings) && is_array($settings['replace_variables'])) {
      foreach ($settings['replace_variables'] as $key => $value) {
        $message = str_replace('%' . $key . '%', $value, $message);
      }
    }

    $message = str_replace('%datetime%', current_time('d-M-Y H:i:s'), $message);
    $message = str_replace('%form_content%', $form_content, $message);
    $message = str_replace('%message%', $settings['message'], $message);
    $message = str_replace('%ip_address%', (!$settings['user_email'] ? __('Client IP Address: ', 'tofino') . $_SERVER['REMOTE_ADDR'] : ''), $message);
    $message = str_replace('%referrer%', (!$settings['user_email'] ? __('Referrer: ', 'tofino') . $_SERVER['HTTP_REFERER'] : ''), $message);

    return $message;
  }


  /**
   * Send Email
   *
   * Sends an email using WordPress function wp_mail
   *
   * @since 1.2.0
   * @uses wp_mail()
   * @uses buildEmailBody()
   * @param array $settings The required parameters for wp_mail
   * @return boolean If the email was successfully sent.
   */
  public function sendEmail($settings)
  {
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    if (array_key_exists('from', $settings)) {
      $headers[] = 'From: ' . $settings['from'];
    }

    if (array_key_exists('reply-to', $settings)) {
      $headers[] = 'Reply-To: ' . $settings['reply-to'];
    }

    if (array_key_exists('cc', $settings)) {
      if (!empty($settings['cc'])) {
        $settings['cc'] = explode(',', $settings['cc']); // Split string on comma

        foreach ($settings['cc'] as $cc_email_address) {
          $headers[] = 'Cc: ' . trim($cc_email_address);
        }
      }
    }

    $email_body = $this->buildEmailBody($settings);

    if (empty($settings['subject'])) {
      $settings['subject'] = __('Form submission from ', 'tofino') . $_SERVER['SERVER_NAME'];
    }

    $mail = wp_mail($settings['to'], $settings['subject'], $email_body, $headers);

    // error_log(print_r($headers, true));

    if ($mail) {
      return true;
    } else {
      return false;
    }
  }


  /**
   * Add a Validator
   *
   * @since 1.2.0
   * @param function $validator The validator function
   * @return void
   */
  public function addValidator($validator)
  {
    $this->validators[] = $validator;
  }


  /**
   * Validate form data
   *
   * @since 1.2.0
   * @return void
   */
  public function validate($fields)
  {
    // Filter fields. Check submitted fields against expected fields.
    // Remove any fields not defined in the array.
    foreach ($this->form_data as $key => $value) {
      if (!array_key_exists($key, $fields)) {
        unset($this->form_data[$key]);
      }
    }

    // Create timestamp for submission
    $this->form_data['date_time'] = time();

    $errors = []; // To store any errors.

    // Loop through each field checking it's value against the assigned validation
    // rules. Add error message to array if validation fails.
    foreach ($fields as $key => $value) {
      try {
        $value->check($this->form_data[$key]);
      } catch (\InvalidArgumentException $ex) {
        $errors[$key] = $ex->getMessage();
      }
    }

    if ($errors) {
      $this->response['message'] = __('Validation failed.', 'tofino');
      $this->response['type']    = 'validation';
      $this->response['extra']   = $errors;
      wp_send_json($this->response);
    }

    foreach ($this->validators as $validator) {
      $result = $validator();
      if (!$result) {
        wp_send_json($this->response);
      }
    }
  }


  /**
   * Respond
   *
   * Sends the JSON response back to the JavaScript request.
   *
   * @uses wp_send_json
   * @param boolean $success True / false
   * @param string $message The message returned to the user
   * @return void
   */
  public function respond($success, $message, $redirect = null)
  {
    $this->response['success']  = $success;
    $this->response['message']  = $message;
    $this->response['redirect'] = $redirect;
    wp_send_json($this->response);
  }
}