<?php
use \Tofino\ThemeOptions\Menu as m;
use \Tofino\ThemeOptions\Notifications as n; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php n\notification('top'); ?>

<!--[if lte IE 9]>
  <div class="flex items-center px-4 py-3 text-sm font-bold text-white bg-red" role="alert">
    <p><?php _e('To improve your experience <a href="https://browsehappy.com/" target="_blank" rel="noopener" class="font-bold">Update your browser</a>. Your browser is <strong>not supported</strong>', 'tofino'); ?></p>
  </div>
<![endif]-->

<nav class="w-full bg-white py-4 px-6 justify-between flex <?php echo m\menu_sticky(); ?>">
  <a href="<?php echo home_url(); ?>" title="<?php echo esc_attr(bloginfo('name')); ?>"><?php echo bloginfo('name'); ?></a>

  <button class="flex bg-red-100 md:hidden" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
     <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
      <!-- <path fill-rule="evenodd" d="M18.278 16.864a1 1 0 0 1-1.414 1.414l-4.829-4.828-4.828 4.828a1 1 0 0 1-1.414-1.414l4.828-4.829-4.828-4.828a1 1 0 0 1 1.414-1.414l4.829 4.828 4.828-4.828a1 1 0 1 1 1.414 1.414l-4.828 4.829 4.828 4.828z"/> -->
      <path fill-rule="evenodd" d="M4 5h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 0 1 0-2z"/>
    </svg>
    <span class="sr-only"><?php _e('Toggle Navigation Button', 'tofino'); ?></span>
  </button>

  <div class="hidden w-auto lg:flex md:items-center" id="main-menu"><?php
    if (has_nav_menu('primary_navigation')) :
      wp_nav_menu([
        'menu'            => 'nav_menu',
        'theme_location'  => 'primary_navigation',
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

<?php if (get_theme_mod('footer_sticky') === 'enabled') : ?>
  <div class="wrapper">
<?php endif; ?>
