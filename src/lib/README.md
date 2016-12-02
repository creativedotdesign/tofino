# lib

Custom php code (that doesn't come from composer) goes here. The following files are base tofino code which you *probably* don't want to edit:

* `AjaxForm.php`
* `assets.php`
* `FragmentCache.php`
* `helpers.php`
* `init.php`
* `nav-walker.php`
* `pagination.php`

## Assets

The enqueuing of JS and CSS files happens in this file.

If you need to add a new local JS variable, find the function named ``localize_scripts`` in this file.

## Helpers

Helper functions / wrappers to assist with development. Current functions include: ``get_id_by_slug``, ``get_page_name``, ``get_complete_meta`` and sanitize functions used in the Theme Options.

## Init

Theme setup functions. Includes a PHP Version Check, registration of navigation menus, ``add_theme_support``, global content width and hide admin bar on the front end.

## Nav Walker

Bootstrap 4 compatible Nav Walker. See [Codex](https://developer.wordpress.org/reference/classes/walker_nav_menu/) for more information on nav walkers.

## Pagination

A Bootstrap 4 compatible pagination function.

Example usage:

`<?php \Tofino\Pagination\wp_bootstrap_pagination(); ?>`

You can filter the Previous / Next button text using the following function.

```
function customize_wp_bootstrap_pagination($args) {
  $args['previous_string'] = 'Previous';
  $args['next_string'] = 'Next';
  return $args;
}
add_filter('wp_bootstrap_pagination_defaults', 'customize_wp_bootstrap_pagination');
```

You can also update the default range, set a custom query and control the before / after output. See the parameters in the function for full details.

Don't forget to uncomment the reference to the SCSS file.

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
