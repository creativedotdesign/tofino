# lib

Custom php code (that doesn't come from composer) goes here. The following files are base tofino code which you *probably* don't want to edit:

* `AjaxForm.php`
* `assets.php`
* `helpers.php`
* `init.php`
* `nav-walker.php`
* 'pagination.php'

# Pagination

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
