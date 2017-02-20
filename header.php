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

<?php
// Wrapper for whole of body content. This is required because IE flexed items
// with flex-direction: column don't respect min-height values, unless they
// are themselves flex children
if (get_theme_mod('footer_sticky') === 'enabled') :
?>
  <div class="body-inner-wrapper">
<?php endif; ?>

<?php n\notification('top'); ?>

<!--[if lte IE 9]>
  <div class="alert alert-danger browser-warning">
    <p><?php _e('You are using an <strong>outdated</strong> browser. <a href="http://browsehappy.com/">Update your browser</a> to improve your experience.', 'tofino'); ?></p>
  </div>
<![endif]-->

<nav class="navbar navbar-light <?php echo m\menu_sticky(); ?> <?php echo m\menu_position(); ?>">
  <button class="navbar-toggler hidden-sm-up collapsed" type="button" data-toggle="collapse" data-target="#main-menu">
    <span class="bar-wrapper">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </span>
    <span class="sr-only"><?php _e('Toggle Navigation Button', 'tofino'); ?></span>
  </button>
  <a class="navbar-brand" href="<?php echo home_url(); ?>" title="<?php echo esc_attr(bloginfo('name')); ?>"><?php echo bloginfo('name'); ?></a>
  <div id="main-menu" class="collapse navbar-toggleable-xs navbar-wrapper">
    <?php
    if (has_nav_menu('primary_navigation')) :
      wp_nav_menu([
        'menu'            => 'nav_menu',
        'theme_location'  => 'primary_navigation',
        'depth'           => 2,
        'container'       => '',
        'container_class' => '',
        'container_id'    => '',
        'menu_class'      => 'nav navbar-nav',
        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'walker'          => new Tofino\Nav\NavWalker()
      ]);
    endif; ?>
  </div>
</nav>

<?php if (get_theme_mod('footer_sticky') === 'enabled') : ?>
  <div class="wrapper">
<?php endif; ?>
