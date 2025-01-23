<?php
$scroll_reveal = get_field('menu_scroll_reveal', 'general-options'); ?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>><?php
  // Open body hook
  wp_body_open();

  // Alerts
  Tofino\Init\alerts('top');

  // Check if sticky menu
  $menu_sticky = Tofino\Init\menu_sticky(); ?>

  <header <?php echo ($scroll_reveal ? 'data-scroll-reveal' : ''); ?>
    class="duration-500 transition-transform transform-gpu <?php echo Tofino\Init\menu_sticky(); ?>"
  >
    <nav class="flex justify-between w-full px-6 py-4 bg-gray-100">
      <a href="<?php echo esc_url(home_url()); ?>" title="<?php echo esc_attr(bloginfo('name')); ?>">
        <?php echo bloginfo('name'); ?>
      </a>

      <div class="w-[400px] h-10 bg-blue-200">LOGO</div>

      <button class="flex lg:hidden js-menu-toggle" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation" data-playwright="open-mobile-menu">
        <!-- Hamburger Icon -->
        <span class="w-6 h-6">
          <?php echo svg(['sprite' => 'hamburger', 'title' => 'Open Menu', 'class' => 'w-full h-full']); ?>
        </span>

        <span class="sr-only"><?php _e('Toggle Navigation Button', 'tofino'); ?></span>
      </button>

      <div class="inactive absolute inset-0 bg-white lg:bg-transparent w-full h-screen lg:h-auto lg:relative lg:w-auto lg:flex lg:items-center" id="main-menu">
        <!-- Close Icon -->
        <button class="absolute z-10 w-4 h-4 top-5 right-7 lg:hidden js-menu-toggle"
          data-playwright="close-mobile-menu"
        >
          <?php echo svg(['sprite' => 'icon-close', 'class' => 'w-full h-full']); ?>
        </button>

        <?php if (has_nav_menu('header_navigation')):
          wp_nav_menu([
            'menu' => 'nav_menu',
            'theme_location' => 'header_navigation',
            'depth' => 2,
            'menu_class' => 'navbar-nav',
            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
          ]);
        endif; ?>
      </div>
    </nav>
  </header>
