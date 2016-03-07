<?php

namespace Tofino;

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

  private function isValidNonce($nonce)
  {
    if (!wp_verify_nonce($nonce, 'next_nonce')) { // Compare Nonce from POST request to server generated nonce
      $this->response['message'] = __('Security check failed.', 'tofino');
      return false;
    } else {
      return true;
    }
  }

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

  private function isValidCaptcha($captcha_repsonse)
  {
    // Captcha validation check
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
   * Get recipient from theme options
   */
  public function getRecipient($theme_option_field)
  {
    if (ot_get_option($theme_option_field)) { // Email address from contact form options
      $recipient = ot_get_option($theme_option_field);
    } elseif (ot_get_option('email_address')) { // Email address from general options
      $recipient = ot_get_option('email_address');
    } else {
      $this->response['message'] = __('No recipient email address.', 'tofino');
      return $this->sendResponse($this->response);
    }
    return $recipient;
  }

  /**
   * Save form data as post meta
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
   * Genereate email body using html template
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
   * Send mail. Uses PHPMailer.
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
   * Send JSON and only JSON, then exit.
   * @param $response
   */
  private function sendResponse($response)
  {
    header('Content-type: application/json');
    $response = json_encode($response);
    echo $response;
    exit;
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
      return $this->sendResponse($this->response);
    }

    // captcha check
    if (array_key_exists('g-recaptcha-response', $this->data)) {
      if ($this->isCaptchaEnabled()) {
        if (!$this->isValidCaptcha($this->data['g-recaptcha-response'])) {
          return $this->sendResponse($this->response);
        }
      }
    }

    // email address check
    if (array_key_exists('email', $this->form_data)) { // Found email field
      if (!$this->isValidEmail($this->form_data['email'])) {
        return $this->sendResponse($this->response);
      }
    }

    foreach ($this->callbacks as $callback) {
      $result = $callback();

      if (!$result) {
        return $this->response;
      }
    }

    if (!$this->sendMail($settings)) {
      return $this->sendResponse($this->response);
    } else {
      $this->response['message'] = $settings['success_msg'];
      $this->response['success'] = true;
    }

    return $this->sendResponse($this->response);
  }
}
