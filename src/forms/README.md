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
