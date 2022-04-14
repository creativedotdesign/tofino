# lib

Custom php code (that doesn't come from composer) goes here. The following files are base tofino code which you *probably* don't want to edit:

* `AjaxForm.php`
* `assets.php`
* `FragmentCache.php`
* `helpers.php`
* `init.php`

## Assets

The enqueuing of JS and CSS files happens in this file.

If you need to add a new local JS variable, find the function named ``localize_scripts`` in this file.

## Helpers

Helper functions / wrappers to assist with development. Current functions include: ``get_id_by_slug``, ``get_page_name``, ``get_complete_meta`` and sanitize functions used in the Theme Options.

## Init

Theme setup functions. Includes a PHP Version Check, registration of navigation menus, ``add_theme_support``, global content width and hide admin bar on the front end.

## Fragment Cache

Cache a template fragment.
Uses Transients. If persistent caching is configured, then the transients functions will use the wp_cache.

Example usage:

```
$frag = new \Tofino\FragmentCache('unique-key', 3600); // Second param is TTL in seconds

if (!$frag->output()) { // Testing for a return of false
  functions_that_do_stuff_live();
  these_should_echo();

  // IMPORTANT YOU CANNOT FORGET THIS. If you do, the site will break.
  $frag->store();
} else {
  echo frag->output();
}
```

# Shortcodes

The following shortcodes are available as shortcodes in WordPress content and PHP functions in your template files.

## [copyright]

Generate copyright string, probably for use in the footer.

Example usage:

`[copyright]` or `copyright();`

HTML output:

```
&copy; 2020
```

## [svg]

Generate SVG sprite code for files from assets/svgs/sprites

Example usage:

`[svg sprite="facebook"]` or `[svg sprite="facebook" class="icon-facebook" title="Facebook" id="fb" preserveAspectRatio="align"]`

or

```
svg([
  'sprite' => 'facebook',
  'class'  => 'icon-facebook'
]);
```

HTML output:

```
<svg class="icon-facebook" title="Facebook" id="fb">
  <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="http://example.com/wp-content/themes/tofino/dist/svg/sprite.svg#facebook"></use>
</svg>
```

## [social_icons]

Generate a `<ul>` from the theme option social media links.

A default class will be applied to the UL element named social-icons. You can add additional classes by passing in the class attribute.

The href for each of the social icon links is pulled from the Theme Options.

Example usage:

```
[social_icons]
[social_icons class="social-icons-footer"]
[social_icons platforms="facebook,twitter,pinterest"]
[social_icons platforms="instagram"]

<?php echo social_icons(['class' => "social-icons-footer"]); ?>
```

HTML output:

```
<ul class="social-icons social-icons-footer">
  <li><a href="http://facebook.com"><svg><use xlink:href="http://example.com/wp-content/themes/tofino/dist/svg/stripe.symbol.svg#facebook"></svg></a></li>
</ul>
```

# Theme Options

The Theme Options use ACF to store the data.

## Admin Logo

Upload a custom admin logo to be displayed on the login screen.

This logo is also used in the email templates for branding.

You might need to add some additional css to tweak the logo size / position. Add your CSS in to the file ``src/css/base/admin.scss``.

## No FOUT

Disable the flash of un-styled text if using the Web Font Loader. This adds a class to the body tag which hides the content until the font has been loaded. The class is then removed.

## Client Data

Client data such as email address, office address, telephone number and company number that can be accessed using a shortcode or PHP function for use in contact pages, footers etc.

## Footer Text

A textarea to add text for the footer.

## Maintenance Mode

Add a warning message to the Admin Screen. Commonly used to advise that client that they are on a Staging environment and all data might be deleted.

## Sticky Menu

Enable / disbale the primary nav bar sticking to the top of the viewport if the user scrolls down.

## Alerts

Display a alert at the top of the viewport above the menu, or fixed at the bottom of the viewport. Alert is shown until dismissed (at which point a cookie is set).

Commonly used in the EU for the Cookie Law text or for general flash messages, promotions or email newsletter signup.

## Social Networks

Add links to various social networks. Remember to add the SVG icon for any new networks you add.

