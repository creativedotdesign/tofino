<?php

namespace Tofino;

/**
 * Form Processor
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
    'message' => ''
  ];

  public function __construct()
  {
    $this->post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); // XSS;
    parse_str($this->post['data'], $this->form_data);
    $this->form_data['date_time'] = time(); // Create timestamp for submission
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
   * Captcha enabled
   *
   * @since 1.2.0
   * @return boolean If capcha is enabled and API complete in the Theme Options.
   */
  private function isCaptchaEnabled()
  {
    //if (ot_get_option('enable_captcha_checkbox')) {
    if (get_theme_mod('captcha_secret') && get_theme_mod('captcha_site_key')) {
      return true;
    } else {
      $this->response['message'] = __('reCaptcha sitekey and/or secret not found. Set this up in the theme options.', 'tofino');
      return false;
    }
    //} else {
    //  return false;
    //}
  }


  /**
   * Captcha validation
   *
   * @since 1.2.0
   * @uses ReCaptcha to validate the aganist the Google ReCaptcha APU.
   * @see https://github.com/google/recaptcha
   * @param String $captcha_repsonse Capcha response string from front end form.
   * @return boolean If the Capcha was valid or not
   */
  private function isValidCaptcha($captcha_repsonse)
  {
    $secret    = get_theme_mod('captcha_secret');
    $recaptcha = new \ReCaptcha\ReCaptcha($secret);
    $resp      = $recaptcha->verify($captcha_repsonse, $_SERVER['REMOTE_ADDR']);
    if (!$resp->isSuccess()) {
      $errors = $resp->getErrorCodes(); // Should we send some real error codes back to the user?
      $this->response['message'] = __('Captcha failed.', 'tofino');
      return false;
    } else {
      return true;
    }
  }


  /**
   * Email address validation
   *
   * Checks syntax, DNS MX record exists and hostname resloves to an IPV4 address.
   *
   * @since 1.2.0
   * @see http://php.net/manual/en/filter.filters.validate.php
   * @see http://php.net/manual/en/function.checkdnsrr.php
   * @see http://php.net/manual/en/function.gethostbyname.php
   * @param string $email_address The email address to validate.
   * @return boolean If the email addresses is valid or not.
   */
  private function isValidEmail($email_address)
  {
    $emailArray = explode('@', $email_address); // Split it on @
    $hostname   = $emailArray[1];
    if (filter_var($email_address, FILTER_VALIDATE_EMAIL) === false || checkdnsrr($hostname, 'MX') === false || gethostbyname($hostname) === $hostname) { //DNS lookup of MX or A/CNAME record
      $this->response['message'] = __('Invalid email address.', 'tofino');
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
    if (get_theme_mod($theme_option_field)) { // Email address from contact form options
      $recipient = get_theme_mod($theme_option_field);
    } elseif (get_theme_mod('email_address')) { // Email address from general options
      $recipient = get_theme_mod('email_address');
    } else {
      $this->response['message'] = __('No recipient email address.', 'tofino');
      return wp_send_json($this->response);
    }
    return $recipient;
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
  private function buildEmailBody($template = 'default-form.html')
  {
    if (is_array($this->form_data)) {
      if (array_key_exists('g-recaptcha-response', $this->form_data)) { // Remove reCaptcha from message content
        unset($this->form_data['g-recaptcha-response']);
      }

      $form_content = null;
      foreach ($this->form_data as $key => $value) { // Loop through each array item ouput the key value as a string
        $key_name      = str_replace('_', ' ', $key);
        $key_name      = ucfirst($key_name);
        $form_content .= $key_name . ': ' . ($value ? $value : 'Empty') . '<br>';
      }
    }

    $message = file_get_contents(get_template_directory() . '/templates/email/' . $template); // Get the template.

    if (ot_get_option('admin_login_logo_id')) {
      $src     = wp_get_attachment_image_src(ot_get_option('admin_login_logo_id'), 'original');
      $message = str_replace('%email_logo%', $src[0], $message);
    } else {
      $message = str_replace('%email_logo%', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', $message);
    }

    $message = str_replace('%form_content%', $form_content, $message);
    $message = str_replace('%ip_address%', __('Client IP Address: ', 'tofino') . $_SERVER['REMOTE_ADDR'], $message);
    $message = str_replace('%referrer%', __('Referrer: ', 'tofino') . $_SERVER['HTTP_REFERER'], $message);

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

    if (array_key_exists('cc', $settings)) {
      $headers[] = 'Cc: ' . $settings['cc'];
    }

    $template   = (array_key_exists('template', $settings) ? $settings['template'] : null); // @todo: Fix!
    $email_body = $this->buildEmailBody();
    $mail       = wp_mail($settings['to'], $settings['subject'], $email_body, $headers);

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
  public function validate()
  {
    // nonce check
    if (!$this->isValidNonce($this->post['nextNonce'])) {
      wp_send_json($this->response);
    }

    // captcha check
    if (array_key_exists('g-recaptcha-response', $this->post)) {
      if ($this->isCaptchaEnabled()) {
        if (!$this->isValidCaptcha($this->post['g-recaptcha-response'])) {
          wp_send_json($this->response);
        }
      }
    }

    // email address check
    if (array_key_exists('email', $this->form_data)) { // Found email field
      if (!$this->isValidEmail($this->form_data['email'])) {
        wp_send_json($this->response);
      }
    }

    foreach ($this->validators as $validator) {
      $result = $validator();
      if (!$result) {
        wp_send_json($this->response);
      }
    }
  }

  public function respond($success, $message)
  {
    $this->response['success'] = $success;
    $this->response['message'] = $message;
    wp_send_json($this->response);
  }
}
