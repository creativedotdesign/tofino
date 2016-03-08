<?php

namespace Tofino\ContactForm;

/**
 * Contact form theme options
 */
function theme_options() {
  return array(
    'contextual_help' => array(
      'content'       => array(),
    ),
    'sections' => array(
      array(
        'id'    => 'contact_form',
        'title' => __('Contact Form', 'tofino')
      ),
    ),
    'settings' => array(
      array(
        'id'          => 'contact_form_to_address',
        'label'       => __('TO email address', 'tofino'),
        'desc'        => __('Email address used in the TO field. Leave blank to use the email address defined in General Settings.', 'tofino'),
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'          => 'contact_form_cc_address',
        'label'       => __('CC email address', 'tofino'),
        'desc'        => __('Email address used in the CC field.', 'tofino'),
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'          => 'contact_form_from_address',
        'label'       => __('FROM email address', 'tofino'),
        'desc'        => __('Email address used in the FROM field. Leave blank for server default.', 'tofino'),
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'          => 'contact_form_email_subject',
        'label'       => __('Email Subject', 'tofino'),
        'desc'        => __('The subject field. Leave blank for "Form submission from SERVER_NAME".', 'tofino'),
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'      => 'contact_form_success_message',
        'label'   => __('Success Message', 'tofino'),
        'desc'    => __('Message displayed to use after form action is successful.', 'tofino'),
        'std'     => __("Thanks, we'll be in touch soon.", 'tofino'),
        'type'    => 'textarea-simple',
        'section' => 'contact_form',
        'rows'    => '3'
      ),
      array(
        'id'      => 'enable_captcha_checkbox',
        'label'   => __('Enable reCaptcha', 'tofino'),
        'desc'    => __('Enable Google reCaptcha "I am not a robot".', 'tofino'),
        'std'     => '',
        'type'    => 'checkbox',
        'section' => 'contact_form',
        'choices' => array(
          array(
            'value' => true,
            'label' => __('Enable reCaptcha', 'tofino'),
            'src'   => ''
          ),
        )
      ),
      array(
        'id'          => 'captcha_site_key',
        'label'       => __('reCaptcha Site Key', 'tofino'),
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
      array(
        'id'          => 'captcha_secret',
        'label'       => __('reCaptcha Secret Key', 'tofino'),
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'contact_form',
      ),
    )
  );
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

  $post_id = url_to_postid($_SERVER['HTTP_REFERER']);
  //$post_id = get_id_from_slug('contact-page');

  $save_success = $form->saveData($post_id, 'contact_form'); // Optional Save the data as post_meta

  if (!$save_success) {
    $form->respond(false, __('Unable to save data.', 'tofino'));
  }

  $email_success = $form->sendEmail([ // Optional
    'to'      => $form->getRecipient('contact_form_to_address'),
    'subject' => ot_get_option('contact_form_email_subject'),
    'cc'      => ot_get_option('contact_form_cc_address'),
    'from'    => ot_get_option('contact_form_from_address')
  ]);

  if (!$email_success) {
    $form->respond(false, __('Unable to complete request due to a system error. Send mail failed.', 'tofino'));
  }

  $form->respond(true, ot_get_option('contact_form_success_message')); // Required
}
add_action('wp_ajax_contact-form', __NAMESPACE__ . '\\ajax_contact_form');
add_action('wp_ajax_nopriv_contact-form', __NAMESPACE__ . '\\ajax_contact_form');
