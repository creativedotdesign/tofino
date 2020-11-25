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
