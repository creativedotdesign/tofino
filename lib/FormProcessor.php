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

  private function nonceCheck()
  {
    if (!wp_verify_nonce($this->data['nextNonce'], 'next_nonce')) { // Compare Nonce from POST request to server generated nonce
      $this->response['message'] = __('Security check failed.', 'tofino');
      return false;
    } else {
      return true;
    }
  }

  private function captchaCheck()
  {
    // Captcha secret check
    if (ot_get_option('captcha_secret')) { //Get from theme options
      $secret = ot_get_option('captcha_secret');
    } else {
      $this->response['message'] = __('reCaptcha sitekey and/or secret not found. Set this up in the theme options.', 'tofino');
      return false;
    }

    // Captcha validation check
    $recaptcha = new \ReCaptcha\ReCaptcha($secret);
    $resp = $recaptcha->verify($this->data['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
    if (!$resp->isSuccess()) {
      $errors = $resp->getErrorCodes(); // Should we send some real error codes back to the user?
      $this->response['message'] = __('Captcha failed.', 'tofino');
      return false;
    } else {
      return true;
    }
  }

  public function validateEmailAddress()
  {
    if (array_key_exists('email', $this->form_data)) { // Found email field
      $emailArray = explode('@', $this->form_data['email']); // Split it on @
      $hostname   = $emailArray[1];
      if (filter_var($this->form_data['email'], FILTER_VALIDATE_EMAIL) === false || checkdnsrr($hostname, 'MX') === false || gethostbyname($hostname) === $hostname) { //DNS lookup of MX or A/CNAME record
        $this->response['message'] = __('Invalid email address.', 'tofino');
        return false;
      } else {
        return true;
      }
    }
  }

  /**
   * Get receipient from theme options
   */
  private function getSettings()
  {
    $prefix = $this->data['name'];
    $settings = [];

    $settings['recipient']    = getRecipient();
    $settings['subject']      = (ot_get_option($prefix . '_form_email_subject') ? ot_get_option($prefix . '_form_email_subject') : null);
    $settings['recipient_cc'] = (ot_get_option($prefix . '_form_cc_address') ? ot_get_option($prefix . '_form_cc_address') : null);
    $settings['send_from']    = (ot_get_option($prefix . '_form_from_address') ? ot_get_option($prefix . '_form_from_address') : null);

    if (ot_get_option($prefix . '_form_to_address')) { // Email address from contact form options
      $recipient = ot_get_option($prefix . '_form_to_address');
    } elseif (ot_get_option('email_address')) { // Email address from general options
      $recipient = ot_get_option('email_address');
    } else {
      $this->response['message'] = __('No recipient email address.', 'tofino');
      return false;
    }
    return $recipient;
  }

  /**
   * Genereate email body using html template
   */
  private function buildEmailBody($template_file = 'contact-form.html')
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

    $message = file_get_contents(get_template_directory() . '/templates/email/' . $template_file); // Get the template.

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
  public function sendMail($recipient, $subject, $recipient_cc = null, $from = null, $template = null)
  {
    $settings = getFormSettings();

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    if ($from) {
      $headers[] = 'From: ' . $from;
    }

    if ($recipient_cc) {
      $headers[] = 'Cc: ' . $recipient_cc;
    }

    $email_body = buildEmailBody($template);

    $mail = wp_mail($recipient, $subject, $email_body, $headers);

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
  public function process()
  {
    // nonce check
    if (!$this->nonceCheck()) {
      return $this->sendResponse($this->response);
    }

    // captcha check
    if (!$this->captchaCheck()) {
      return $this->sendResponse($this->response);
    }

    // email address check
    if (!$this->validateEmailAddress()) {
      return $this->sendResponse($this->response);
    }

    foreach ($this->callbacks as $callback) {
      $result = $callback();

      if (!$result) {
        return $this->response;
      }
    }

    if (!$this->getRecipient()) {
      return $this->sendResponse($this->response);
    }


    // send email?
    $this->response['success'] = true;

    return $this->response;
  }
}





/**
 * Save form data as post meta
 */
function save_form_data($post_id, $meta_key, $data = array()) {
  if ($post_id && is_array($data)) {
    $meta_id = add_post_meta($post_id, $meta_key, $data, false);
    if (0 < intval($meta_id)) {
      return $meta_id;
    } else {
      $response = array(
        'success' => false,
        'message' => __('Unable to save data.', 'tofino')
      );
      \Tofino\Helpers\send_json_response($response);
    }
  }
}
