<?php

/**
 * Template Name: Blank page
 *
 *
 * @package Tofino
 * @since 5.0.0
 */ 
?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php get_template_part('templates/content', 'page'); ?>

<?php wp_footer(); ?>

<?php do_action('tofino_after_footer'); ?>

</body>
</html>
