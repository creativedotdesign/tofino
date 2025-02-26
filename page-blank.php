<?php

/**
 * Template: Blank page
 *
 *
 * @package Tofino
 * @since 5.0.0
 */

use \Tofino\Helpers as h; ?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>><?php

$template = h\get_page_name();

if (locate_template('templates/content-page-' . $template . '.php') != '') {
  get_template_part('templates/content-page', $template); // e.g. templates/content-page-members.php
} else {
  if (function_exists('icl_object_id')) { //WPML installed
    $original_page_id = apply_filters('wpml_object_id', get_the_ID(), 'page', false, 'en'); //Assumes english is the primary language.
    if ($original_page_id) {
      $template = h\get_page_name($original_page_id);
    }
  }
  get_template_part('templates/content-page', $template);
} ?>

<?php wp_footer(); ?>

<?php do_action('tofino_after_footer'); ?>

</body>
</html>
