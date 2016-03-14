<?php

namespace Tofino\ContactForm;

/**
 * Contact form theme options
 */
}


/**
 * AJAX Contact Form
 */
function ajax_contact_form() {
  $form = new \Tofino\AjaxForm(); // Required

  // Optional
  $form->addValidator(function () {
    return true;
  });

  $form->validate(); // Required. Call validate

  //$data = $form->getData(); // Optional  Do what you want with the sanitized form data

  $post_id = url_to_postid($_SERVER['HTTP_REFERER']); // Get the post_id from the referring page

  $save_success = $form->saveData($post_id, 'contact_form'); // Optional Save the data as post_meta

  if (!$save_success) {
    $form->respond(false, __('Unable to save data.', 'tofino'));
  }

  $email_success = $form->sendEmail([ // Optional
    'to'      => $form->getRecipient('contact_form_to_address'),
    'subject' => get_theme_mod('contact_form_email_subject'),
    'cc'      => get_theme_mod('contact_form_cc_address'),
    'from'    => get_theme_mod('contact_form_from_address')
  ]);

  if (!$email_success) {
    $form->respond(false, __('Unable to complete request due to a system error. Send mail failed.', 'tofino'));
  }

  $form->respond(true, get_theme_mod('contact_form_success_message')); // Required
}
add_action('wp_ajax_contact-form', __NAMESPACE__ . '\\ajax_contact_form');
add_action('wp_ajax_nopriv_contact-form', __NAMESPACE__ . '\\ajax_contact_form');
