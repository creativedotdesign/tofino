<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<nav class="navbar navbar-light navbar-fixed-top bg-faded">
  <button class="navbar-toggler hidden-sm-up" type="button" data-toggle="collapse" data-target="main-menu">
    &#9776;
  </button>
  <div id="main-menu" class="collapse navbar-toggleable-xs">
    <a class="navbar-brand" href="<?php echo home_url() ?>"><?php echo bloginfo('name') ?></a>
    <?php
      wp_nav_menu( array(
        'menu'              => 'nav_menu',
        'theme_location'    => 'primary_navigation',
        'depth'             => 2,
        'container'         => '',
        'container_class'   => '',
        'container_id'      => '',
        'menu_class'        => 'nav navbar-nav',
        'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
        'items_wrap'        => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'walker'            => new wp_bootstrap_navwalker()
      ));
    ?>
  </div>
</nav>

