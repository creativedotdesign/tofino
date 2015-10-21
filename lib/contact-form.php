<?php

namespace Tofino\ContactForm;

/**
 * AJAX Contact Form
 */
add_action('wp_ajax_contact-form', __NAMESPACE__ . '\\ajax_contact_form');
add_action('wp_ajax_nopriv_contact-form', __NAMESPACE__ . '\\ajax_contact_form');

function ajax_contact_form() {
  $nonce = $_POST['nextNonce']; // Nonce from POST request
  if (!wp_verify_nonce($nonce, 'next_nonce')) { // Compare to server generated nonce
    wp_die('Security check failed.', 'An error occured.');
  }

  $reCaptcha_enabled = ot_get_option('enable_captcha_checkbox');

  $form_data = array();
  parse_str($_POST['data'], $form_data); // Give it the POST data

  // Basic sanitization
  foreach ($form_data as $key => $value) {
    $form_data[$key] = filter_var($value, FILTER_SANITIZE_STRING);

    /*if (strpos($form_item,'email') !== false) { // Hunting for email
      $valid_email = filter_input($form_item, FILTER_VALIDATE_EMAIL);
    }*/
  }

  // reCaptcha enabled
  if ($reCaptcha_enabled) {
    // Captcha secret check
    if (ot_get_option('captcha_secret')) { //Get email address from theme options
      $secret = ot_get_option('captcha_secret');
    } else {
      $resposne = array(
        'success' => false,
        'message' => __('reCaptcha sitekey and/or secret not found. Set this up in the theme options.', 'tofino')
      );
      send_json_repsonse($resposne);
    }

    // Captcha validation check
    $recaptcha = new \ReCaptcha\ReCaptcha($secret);
    $resp = $recaptcha->verify($form_data['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
    if (!$resp->isSuccess()) {
      $errors = $resp->getErrorCodes(); // Should we send some real error codes back to the user?
      $resposne = array(
        'success' => false,
        'message' => __('Captcha failed.', 'tofino')
      );
      send_json_repsonse($resposne);
    }
  }

  if (ot_get_option('email_address')) { //Get email address from theme options
    $recipient = ot_get_option('email_address');
  } else {
    $resposne = array(
      'success' => false,
      'message' => __('No recipient email address.', 'tofino')
    );
    send_json_repsonse($resposne);
  }

  $subject    = __('Form submission from ', 'tofino') . $_SERVER['SERVER_NAME'];
  $email_body = build_email_body($form_data);
  $send_mail  = send_mail($recipient, $subject, $email_body, $from = null);

  if (true === $send_mail) {
    $response = array(
      'success' => true,
      'message' => ot_get_option('form_success_message')
    );
  } else {
    $response = array(
      'success' => false,
      'message' => __('Unable to complete request due to a system error. Send mail failed.', 'tofino')
    );
  }

  send_json_repsonse($response);
}

function build_email_body($form_data) {
  $msg = '<html><body>
          <table border="0"><tr><td>
          <h3>New form submission:</h3>';

  if (array_key_exists('g-recaptcha-response', $form_data)) {
    unset($form_data['g-recaptcha-response']);
  }

  foreach ($form_data as $key => $value) {
    $msg .= $key . ': ' . $value . '<br>';
  }

  $msg .= '</td></tr></table>
           </body></html>';

  return $msg;
}

function send_json_repsonse($response) {
  header('Content-type: application/json');
  $response = json_encode($response);
  echo $response;
  exit;
}

function send_mail($recipient, $subject, $email_body, $from = null) {
  $headers  = "MIME-Version: 1.0\r\n";
  $headers .= "Cc: daniel@lambdacreatives.com\r\n";
  $headers .= "Content-type: text/html; charset=utf-8\r\n";
  $headers .= "From: server@lambdacreatives.com\r\n";

  $result = mail($recipient, $subject, $email_body, $headers);

  return $result;
}
