<?php

namespace Tofino;

/**
 * Form Processor
 *
 * @package Tofino
 * @since 1.2.0
 */
class FormProcessor
{

  public $callbacks = [];
  private $data     = [];
  private $formData = [];
  private $response = [
    'success' => false,
    'message' => ''
  ];

  public function __construct()
  {
    $this->data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); // XSS;
    parse_str($this->data['data'], $this->form_data);
    $this->form_data['date_time'] = time(); // Create timestamp for submission
  }

  /**
   * Nonce validation
   *
   * @since 1.2.0
   *
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
    if (ot_get_option('enable_captcha_checkbox')) {
      if (ot_get_option('captcha_secret') && ot_get_option('captcha_site_key')) {
        return true;
      } else {
        $this->response['message'] = __('reCaptcha sitekey and/or secret not found. Set this up in the theme options.', 'tofino');
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * Captcha validation
   *
   * @since 1.2.0
   *
   * @uses ReCaptcha to validate the aganist the Google ReCaptcha APU.
   * @see https://github.com/google/recaptcha
   * @param String $captcha_repsonse Capcha response string from front end form.
   * @return boolean If the Capcha was valid or not
   */
  private function isValidCaptcha($captcha_repsonse)
  {
    $secret    = ot_get_option('captcha_secret');
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
   *
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
   * Return error is JSON format if no email addresses found.
   *
   * @since 1.2.0
   * @param string $theme_option_field The option field to check for the email address.
   * @return string|json The email address if found, else JSON array output.
   */
  public function getRecipient($theme_option_field)
  {
    if (ot_get_option($theme_option_field)) { // Email address from contact form options
      $recipient = ot_get_option($theme_option_field);
    } elseif (ot_get_option('email_address')) { // Email address from general options
      $recipient = ot_get_option('email_address');
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
   * @return integer|boolean The saved meta id or false if save failed.
   */
  public function saveData($post_id, $meta_key, $data = array())
  {
    if ($post_id && is_array($data)) {
      $meta_id = add_post_meta($post_id, $meta_key, $data, false);
      if (0 < intval($meta_id)) {
        return $meta_id;
      } else {
        $this->response['message'] =  __('Unable to save data.', 'tofino');
        return false;
      }
    }
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
   * Send mail
   *
   * Sends an email using WordPress function wp_mail
   *
   * @since 1.2.0
   * @uses wp_mail()
   * @uses buildEmailBody()
   * @return boolean If the email was successfully sent.
   */
  private function sendMail($settings)
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
      $this->response['message'] = __('Unable to complete request due to a system error. Send mail failed.', 'tofino');
      return false;
    }
  }

  /**
   * Add a callback
   * @param function $callback The callback
   */
  public function addCallback($callback)
  {
    $this->callback[] = $callback;
  }

  /**
   * [process description]
   * @return boolean Success status
   */
  public function process($settings)
  {
    // nonce check
    if (!$this->isValidNonce($this->data['nextNonce'])) {
      wp_send_json($this->response);
    }

    // captcha check
    if (array_key_exists('g-recaptcha-response', $this->data)) {
      if ($this->isCaptchaEnabled()) {
        if (!$this->isValidCaptcha($this->data['g-recaptcha-response'])) {
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

    foreach ($this->callbacks as $callback) {
      $result = $callback();

      if (!$result) {
        return $this->response;
      }
    }

    if ($this->sendMail($settings)) {
      $this->response['message'] = $settings['success_msg'];
      $this->response['success'] = true;
    }

    wp_send_json($this->response);
  }
}
