<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

  <?php wp_body_open(); ?>

  <?php Tofino\Init\alert('top'); ?>

  <header class="<?php echo Tofino\Init\menu_sticky(); ?>">
    <nav class="flex justify-between w-full px-6 py-4 bg-white">
      <a href="<?php echo home_url(); ?>" title="<?php echo esc_attr(bloginfo('name')); ?>"><?php echo bloginfo('name'); ?></a>

      <button class="flex lg:hidden js-menu-toggle" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
        <!-- Hamburger Icon -->
        <span class="w-6 h-6">
          <?php echo svg(['sprite' => 'icon-hamburger', 'class' => 'w-full h-full']); ?>
        </span>

        <span class="sr-only"><?php _e('Toggle Navigation Button', 'tofino'); ?></span>
      </button>

      <div class="absolute inset-0 hidden w-full h-screen bg-white lg:h-auto lg:relative lg:w-auto lg:flex lg:items-center" id="main-menu">
        <!-- Close Icon -->
        <button class="absolute right-0 z-10 w-6 h-6 top-6 md:right-4 md:hidden js-menu-toggle">
          <?php echo svg(['sprite' => 'icon-close', 'class' => 'w-full h-full']); ?>
        </button>

        <?php
        if (has_nav_menu('header_navigation')) :
          wp_nav_menu([
            'menu'            => 'nav_menu',
            'theme_location'  => 'header_navigation',
            'depth'           => 2,
            'container'       => '',
            'container_class' => '',
            'container_id'    => '',
            'menu_class'      => 'navbar-nav',
            'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
          ]);
        endif; ?>
      </div>
    </nav>
  </header>
  