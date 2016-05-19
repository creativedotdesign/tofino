<?php
/**
 * Contact form
 *
 * Theme options and form processing for the contact form.
 *
 * @package Tofino
 * @since 1.2.0
 */

namespace Tofino\ContactForm;

/**
 * Contact form theme options
 *
 * @since 1.2.0
 * @param object $wp_customize Instance of WP_Customize_Manager class.
 * @return void
 */
function contact_form_settings($wp_customize) {
  $wp_customize->add_section('tofino_contact_form_settings', [
    'title' => __('Contact Form', 'tofino'),
    'panel' => 'tofino_options'
  ]);

  $wp_customize->add_setting('contact_form_to_address', ['default' => '']);

  $wp_customize->add_control('contact_form_to_address', [
    'label'       => __('To', 'tofino'),
    'description' => __('Email address used in the TO field. Leave blank to use the email address defined in Client Data settings.', 'tofino'),
    'section'     => 'tofino_contact_form_settings',
    'type'        => 'text'
  ]);

  $wp_customize->add_setting('contact_form_cc_address', ['default' => '']);

  $wp_customize->add_control('contact_form_cc_address', [
    'label'       => __('CC', 'tofino'),
    'description' => __('Add CC email address, seperate with a comma and a space (", ") to use multiple addresses.', 'tofino'),
    'section'     => 'tofino_contact_form_settings',
    'type'        => 'text'
  ]);

  $wp_customize->add_setting('contact_form_from_address', ['default' => '']);

  $wp_customize->add_control('contact_form_from_address', [
    'label'       => __('From', 'tofino'),
    'description' => __('Email address used in the FROM field. Leave blank for server default.', 'tofino'),
    'section'     => 'tofino_contact_form_settings',
    'type'        => 'text'
  ]);

  $wp_customize->add_setting('contact_form_subject', ['default' => '']);

  $wp_customize->add_control('contact_form_subject', [
    'label'       => __('Subject', 'tofino'),
    'description' => __('The subject field. Leave blank for "Form submission from SERVER_NAME".', 'tofino'),
    'section'     => 'tofino_contact_form_settings',
    'type'        => 'text'
  ]);

  $wp_customize->add_setting('contact_form_success_message', [
    'default' => __("Thanks, we'll be in touch soon.", 'tofino')
  ]);

  $wp_customize->add_control('contact_form_success_message', [
    'label'       => __('Success Message', 'tofino'),
    'description' => __('Message displayed to use after form action is successful.', 'tofino'),
    'section'     => 'tofino_contact_form_settings',
    'type'        => 'text'
  ]);

  $wp_customize->add_setting('contact_form_captcha', ['default' => '']);

  $wp_customize->add_control('contact_form_captcha', [
    'label'       => __('Enable reCAPTCHA', 'tofino'),
    'description' => __('Enable Google reCAPTCHA "I am not a robot".', 'tofino'),
    'section'     => 'tofino_contact_form_settings',
    'type'        => 'checkbox'
  ]);
}
add_action('customize_register', __NAMESPACE__ . '\\contact_form_settings');


/**
 * Ajax Contact Form
 *
 * Process the ajax request.
 * Called via JavaScript.
 *
 * @since 1.2.0
 * @return void
 */
function ajax_contact_form() {
  $form = new \Tofino\AjaxForm(); // Required

  // Defined expected fields. Keys should match the input field names
  $fields = [
    'name'    => ['required' => true],
    'email'   => ['required' => true],
    'message' => ['required' => true]
  ];

  // Optional
  $form->addValidator(function () {
    return true;
  });

  $form->validate($fields); // Required  Call validate

  $data = $form->getData(); // Optional  Do what you want with the sanitized form data

  $post_id = url_to_postid($_SERVER['HTTP_REFERER']); // Get the post_id from the referring page

  $save_success = $form->saveData($post_id, 'contact_form'); // Optional  Save the data as post_meta

  if (!$save_success) {
    $form->respond(false, __('Unable to save data.', 'tofino'));
  }

  $admin_email_success = $form->sendEmail([ // Optional
    'to'                 => $form->getRecipient('contact_form_to_address'),
    'subject'            => get_theme_mod('contact_form_email_subject'),
    'cc'                 => get_theme_mod('contact_form_cc_address'),
    'from'               => get_theme_mod('contact_form_from_address'), // If not defined or blank the server default email address will be used
    'remove_submit_data' => false,
    'user_email'         => false,
    'template'           => 'default-form.html'
  ]);

  $user_email_address = $data['email'];

  $user_email_success = $form->sendEmail([ // Optional
    'to'                 => $user_email_address,
    'subject'            => __('Thanks for contacting us!', 'tofino'),
    'message'            => __('We will be in touch with you in the next 48hrs.', 'tofino'),
    'remove_submit_data' => true,
    'user_email'         => true,
    'template'           => 'default-form.html'
  ]);

  if (!$admin_email_success || !$user_email_success) {
    $form->respond(false, __('Unable to complete request due to a system error. Send mail failed.', 'rocketpowerskating'));
  }

  $form->respond(true, get_theme_mod('contact_form_success_message', __("Thanks, we'll be in touch soon.", 'tofino'))); // Required
}
add_action('wp_ajax_contact-form', __NAMESPACE__ . '\\ajax_contact_form');
add_action('wp_ajax_nopriv_contact-form', __NAMESPACE__ . '\\ajax_contact_form');
