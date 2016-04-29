# lib

Custom php code (that doesn't come from composer) goes here. The following files are base tofino code which you *probably* don't want to edit:

* `AjaxForm.php`
* `assets.php`
* `helpers.php`
* `init.php`
* `nav-walker.php`
* `relative-urls.php`
* `theme-tracker.php`

# Forms

See README file in the `forms` directory.

# Shortcodes

The following shortcodes are available as shortcodes in WordPress content and PHP functions in your template files.

## [copyright]

Generate copyright string, probably for use in the footer.

Example usage:

``[copyright]`` or ``copyright();``

HTML output:
```
&copy; 2016 <span class="copyright-site-name">Website Title</span>
```

## [option]

Get a theme option. Uses the WordPress function `get_theme_mod()`.

Example usage:

``[option id="optionid" default="Default value"]``

or

```
ot_shortcode([
  'id'      => 'optionid',
  'default' => 'Default value'
]);
```

## [svg]

Generate SVG sprite code for files from assets/svgs/sprites

Example usage:

``[svg sprite="facebook"]`` or ``[svg sprite="facebook" class="icon-facebook" title="Facebook" id="fb" preserveAspectRatio="align"]``

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
  <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="http://example.com/wp-content/themes/tofino/dist/svg/sprite.symbol.svg#facebook"></use>
</svg>
```

## [social_icons]

Generate a `<ul>` from the theme option social media links.

A default class will be applied to the UL element named social-icons. You can add additional classes by passing in the class attribute.

Example usage:

``[social_icons]`` or ``[social_icons class="social-icons-footer"]``

or

``social_icons(['class' => "social-icons-footer"]);``

HTML output:
```
<ul class="social-icons social-icons-footer">
  <li><a href="http://facebook.com"><svg><use xlink:href="http://example.com/wp-content/themes/tofino/dist/svg/stripe.symbol.svg#facebook"></svg></a></li>
</ul>
```
