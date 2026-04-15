<?php
$scroll_reveal = get_field('menu_scroll_reveal', 'option'); ?>

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
  Tofino\Alerts\render('top');

  // Check if sticky menu
  $menu_sticky = Tofino\Init\menu_sticky(); ?>

  <header <?php echo $scroll_reveal ? 'data-scroll-reveal' : ''; ?>
    class="duration-500 transition-transform lg:transform-gpu <?php echo esc_attr($menu_sticky ?? ''); ?>"
  >
    <nav class="relative flex justify-between w-full px-6 py-4 bg-gray-100">
      <a href="<?php echo esc_url(home_url()); ?>" title="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <?php echo esc_html(get_bloginfo('name')); ?>
      </a>

      <button type="button" class="flex lg:hidden" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation" data-menu-open="main-menu" data-playwright="open-mobile-menu">
        <!-- Hamburger Icon -->
        <span class="w-6 h-6">
          <?php echo svg(['sprite' => 'hamburger', 'title' => 'Open Menu', 'class' => 'w-full h-full']); ?>
        </span>

        <span class="sr-only"><?php esc_html_e('Toggle Navigation Button', 'tofino'); ?></span>
      </button>

      <div class="hidden absolute inset-0 overflow-y-auto bg-white w-full h-screen lg:bg-transparent lg:h-auto lg:relative lg:w-auto lg:flex lg:items-center" id="main-menu">
        <!-- Close Icon -->
        <button type="button" class="absolute z-10 w-4 h-4 top-5 right-7 lg:hidden"
          aria-label="Close navigation"
          data-menu-close="main-menu"
          data-playwright="close-mobile-menu"
        >
          <?php echo svg(['sprite' => 'close', 'class' => 'w-full h-full']); ?>
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
