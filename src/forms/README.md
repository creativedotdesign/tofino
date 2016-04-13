# Forms

Handle validation and processing of HTML forms via Ajax.

## Features
- Uses Ajax
- Save submitted form data as post meta
- Email form data to website administrator and to the user submitting the form
- Google reCaptcha to prevent Spam
- Custom HTML email templates
- Custom email subjects
- Custom success / failure messages
- CC form data to another email address
- Client side validation using HTML5 require attributes
- Server side validation
- Add custom validation or additional form processing (e.g. Stripe checkout or send form data to an external API) via ``addValidator`` callback function.
- Multilingual ready

## Files
- `src/forms/contact-form.php` - Example form processing usage. Define expected fields, WP Customizer options, PHP function called on submit (via Ajax request).
- `templates/content-page-contact.php` - Example HTML contact form
- `templates/email/default-template.html` Default HTML email template
- `src/lib/AjaxForm.php` - The PHP class used to process the form submission
- 'assets/scripts/ajax-form.js' - The JS code used for the Ajax request

## Quick start

To use the including contact form you need to:-

- Update the fields in the Contact Form theme options
- Review / update the ``ajax_contact_form()`` function in ``src/forms/contact-form.php``
- Test your form

## Additional info

If the To email address field has not been defined the email address defined in the Client Data theme options screen will be used.

If the From email address field has not been defined the default server email address will be used.

The form is processed via Ajax when the class ``form-processor`` is added to the form element, e.g. ``<form class="form-processor">``.

The Ajax response message is added to the HTML element ``js-form-result``. If successful an additional classes ``alert alert-success`` are added. If failed classes ``alert alert-danger`` are added.

The form is hidden on success.

During the Ajax request all form fields are disabled.

The PHP function called by the Ajax request is defined by the WordPress ``add_action`` function and linked to the form elements id.

Example:

``<form class="form-processor" id="contact-form">``

```
function ajax_contact_form() {
  // Do processing
}
add_action('wp_ajax_contact-form', ajax_contact_form');
add_action('wp_ajax_nopriv_contact-form', ajax_contact_form');
```
