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

use \Respect\Validation\Validator as v;

/**
 * Ajax Contact
 *
 * Process the ajax request.
 * Called via JavaScript.
 *
 * @since 1.2.0
 * @return void
 */
function ajax_contact() {
  // check_ajax_referer('next_nonce', 'security');

  $form = new \Tofino\AjaxForm(); // Required

  // Defined expected fields. Keys should match the input field names.
  // Add validation rules. See: https://respect-validation.readthedocs.io/en/latest/
  // setName is used for the return error messages
  $fields = [
    'firstName' => v::notEmpty()->setName('First Name'),
    'lastName' => v::notEmpty()->setName('Last Name'),
    'email' => v::email()->setName('Email'),
    'phone' => v::alwaysValid()->setName('Phone'),
    'message' => v::notEmpty()->setName('Message')
  ];

  $form->validate($fields); // Required  Call validate

  $data = $form->getData(); // Do what you want with the sanitized form data

  $post = [
    'ID'           => null,
    'post_title'   => $data['firstName'] . ' ' . $data['lastName'] . ' - ' . $data['email'],
    'post_excerpt' => '',
    'post_content' => null,
    'post_type'    => 'contact_submission',
    'post_status'  => 'publish',
  ];

  $post_id = wp_insert_post($post);

  $user_email_address = $data['email'];

  $post_meta = [
    'contact_form_firstname' => $data['firstName'],
    'contact_form_lastname' => $data['lastName'],
    'contact_form_email'   => $data['email'],
    'contact_form_phone'   => $data['phone'],
    'contact_form_message' => $data['message'],
  ];

  // Update the post meta
  foreach ($post_meta as $key => $value) {
    update_field($key, $value, $post_id);
  }
  
  $settings = get_field('contact_form', 'general-options');

  $admin_email_success = $form->sendEmail([ // Send out an email
    'to'                 => $form->getRecipient('contact_form'),
    'reply-to'           => $data['firstName'] . ' ' . $data['lastName'] . '<' . $user_email_address . '>', // Name <email@domain.com>
    'subject'            => $settings['subject'],
    'cc'                 => $settings['cc_address'],
    'from'               => $settings['from_address'], // If not defined or blank the server default email address will be used
    'remove_submit_data' => false,
    'user_email'         => false,
    'message'            => null,
    'template'           => 'email-body.html',
    'remove_keys'        => ['ip_address', 'referrer']
  ]);

  if (!$admin_email_success) {
    $form->respond(
      false,
      __('Unable to complete request due to a system error. Send mail failed.', 'tofino')
    );
  }

  $form->respond(
    true,
    $settings['success_message']
  ); // Required
}
add_action('wp_ajax_ajax-contact', __NAMESPACE__ . '\\ajax_contact');
add_action('wp_ajax_nopriv_ajax-contact', __NAMESPACE__ . '\\ajax_contact');


function create_post_type() {
  register_post_type('contact_submission', [
    'label' => 'Contact Form',
    'description' => '',
    'hierarchical' => false,
    'supports' => [
      0 => 'title',
    ],
    'taxonomies' => [],
    'public' => true,
    'exclude_from_search' => true,
    'publicly_queryable' => false,
    'can_export' => true,
    'delete_with_user' => 'null',
    'labels' => [],
    'menu_icon' => 'dashicons-email-alt',
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_nav_menus' => false,
    'show_in_admin_bar' => true,
    'rewrite' => false,
    'has_archive' => false,
    'show_in_rest' => false,
    'rest_base' => '',
    'rest_controller_class' => 'WP_REST_Posts_Controller',
    'acfe_archive_template' => '',
    'acfe_archive_ppp' => 0,
    'acfe_archive_orderby' => NULL,
    'acfe_archive_order' => NULL,
    'acfe_single_template' => '',
    'acfe_admin_archive' => false,
    'acfe_admin_ppp' => 10,
    'acfe_admin_orderby' => 'date',
    'acfe_admin_order' => 'DESC',
    'capability_type' => 'post',
    'capabilities' => [
      'create_posts' => false
    ],
    'map_meta_cap' => false,
  ]);
}
add_action( 'init', __NAMESPACE__ . '\\create_post_type' );
