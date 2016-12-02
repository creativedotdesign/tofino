# Theme Options

The Theme Options use the [WordPress Theme Customization API](https://codex.wordpress.org/Theme_Customization_API) and [Customizer API](https://developer.wordpress.org/themes/advanced-topics/customizer-api/).

## Admin Logo

Upload a custom admin logo to be displayed on the login screen.

This logo is also used in the email templates for branding.

You might need to add some additional css to tweak the logo size / position. Add your CSS in to the file ``assets/styles/base/wp-admin.scss``.

## Advanced

### No FOUT

Disable the flash of un-styled text if using the Web Font Loader. This adds a class to the body tag which hides the content until the font has been loaded. The class is then removed.

### Critical CSS

Inject the critical.css file as inline styles in the head tag. Defer the main CSS file in to loadCSS in the footer. Remember to run the styles:critical gulp task.

### Move jQuery to the footer

Move jQuery to the footer. Uncheck if you have compatibility issues with plugins.

## Client Data

Client data such as email address, office address, telephone number and company number that can be accessed using a shortcode or PHP function for use in contact pages, footers etc.

## Footer

### Sticky Footer

Enable / disable sticky footer. This adds a class with flexbox properties to stick the footer to the bottom of the viewport if the content is shorter than the viewport.

### Footer Text

A textarea to add text for the footer.

## Google Analytics

A field for your UA Code. The Javascript snippet will be added to just before the closing body tag if WP_DEBUG is false.

## Google reCAPTCHA

Site and Secret keys for using Google reCAPTCHA in web forms.

## Maintenance Mode

Add a warning message to the Admin Screen. Commonly used to advise that client that they are on a Staging environment and all data might be deleted.

## Menu

### Sticky Menu

Enable / disbale the primary nav bar sticking to the top of the viewport if the user scrolls down. Gaps in native browser support is polyfilled using Stickyfill an open source library.

### Menu Position (Alignment)

Left / Center / Right align the primary navigation. This uses a classes with flexbox properies.

## Notifications

Display a notification at the top of the viewport above the menu, or fixed at the bottom of the viewport. Notification is shown until dismissed (at which point a cookie is set).

Commonly used in the EU for the Cookie Law text or for general flash messages, promotions or email newsletter signup.

## Social Networks

Add links to various social networks. Remember to add the SVG icon for any new networks you add.

## Theme Tracker

Send theme name, theme version, site url, ip address and WP version to the tracker API every 7 days. This data is used to plan future updates.
