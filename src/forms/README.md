# Forms

Handle validation and processing of HTML forms via Ajax.

What is AjaxForm?

AjaxForm is a PHP Class and jQuery plugin to help with the heavy lifting of processing form data using Ajax in WordPress.

## Features
- Uses Ajax
- Save submitted form data as post meta
- Email form data to website administrator and/or to the user submitting the form
- Google reCaptcha to prevent Spam
- Custom HTML email templates
- Custom email subjects
- Custom success / failure messages
- CC form data to another email address
- Client side validation using HTML5 require attributes
- Server side validation via Respect/Validation PHP library
- Add custom validation or additional form processing (e.g. Stripe checkout or send form data to an external API) via ``addValidator`` callback function.
- Multilingual ready
- Custom variables

## Files
- `src/forms/contact-form.php` - Example usage. Define expected fields, WP Customizer options, PHP function called on submit (via Ajax request).
- `templates/content-page-contact.php` - Example HTML contact form
- `templates/email/default-template.html` The default HTML email template
- `src/lib/AjaxForm.php` - The PHP class used to process the form submission
- `assets/scripts/ajax-form.js` - The jQuery plugin used for the Ajax request

## Quick start

To use the included contact form you need to:-

- Create a WordPress page in the admin area with the permalink / slug `contact`
- Review / update the HTML form fields in ``templates/content-page-contact.php``
- Populate the fields in the Contact Form theme options with the correct TO / FORM email addresses
- Review / update the ``ajax_contact_form()`` function in ``src/forms/contact-form.php``
- Test your form by browsing the contact page.

## Email templates

A default HTML email template can be found in `templates/email/default-template.html`.

The email template includes the following variables: ``%email_logo%, %form_content%, %ip_address%, %referrer%``. Each of these variables is replaced with the content inside the function ``$AjaxForm->buildEmailBody()``. If you need to create new variables you will need to update the function ``$AjaxForm->buildEmailBody()``.

You can create additional templates and pass in the filename of the template when calling the function ``$AjaxForm->sendEmail()``. See example usage in `src/forms/contact-form.php`.

You can add custom variables inside of the email template and pass in an array of keys and values directly via the sendEmail function called from your PHP function. See the contact-form.php example code.

## Additional info

If the To email address field has not been defined the email address defined in the Client Data theme options screen will be used.

If the From email address field has not been defined the default server email address will be used.

The HTML form is processed via Ajax if the class ``form-processor`` has been added to the form element, e.g. ``<form class="form-processor">``.

The Ajax response message is added to the HTML element ``<div class="js-form-result"></div>``. If successful an additional classes ``alert alert-success`` are added. If failed classes ``alert alert-danger`` are added.

The form is hidden on success.

During the Ajax request all form fields are disabled.

The PHP function called by the Ajax request is defined by the WordPress ``add_action`` function and linked to the form elements attribute data-wp-action.

Example:

``<form class="form-processor" data-wp-action="contact-form">``

```
function ajax_contact_form() {
  // Do processing
}
add_action('wp_ajax_contact-form', ajax_contact_form');
add_action('wp_ajax_nopriv_contact-form', ajax_contact_form');
```
For a more real world example see the code in `src/forms/contact-form.php`.

## Ajax Form JS Options

TBC
