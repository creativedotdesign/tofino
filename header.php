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
  <div class="alert alert-danger browser-warning">
    <p><?php _e('You are using an <strong>outdated</strong> browser. <a href="http://browsehappy.com/">Update your browser</a> to improve your experience.', 'tofino'); ?></p>
  </div>
<![endif]-->

<nav class="navbar navbar-expand-lg navbar-light bg-light <?php echo m\menu_headroom(); ?> <?php echo m\menu_sticky(); ?> <?php echo m\menu_position(); ?>">
  <a class="navbar-brand" href="<?php echo home_url(); ?>" title="<?php echo esc_attr(bloginfo('name')); ?>"><?php echo bloginfo('name'); ?></a>

  <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="bar-wrapper">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </span>
    <span class="sr-only"><?php _e('Toggle Navigation Button', 'tofino'); ?></span>
  </button>

  <div class="collapse navbar-collapse" id="main-menu">
    <?php
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
        'walker'          => new Tofino\Nav\NavWalker()
      ]);
    endif; ?>
  </div>
</nav>

<?php if (get_theme_mod('footer_sticky') === 'enabled') : ?>
  <div class="wrapper">
<?php endif; ?>
