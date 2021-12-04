<?php
use \Tofino\ThemeOptions\Notifications as n; ?>

<!-- Nav Menu -->
<div class="w-full">
  <div class="container"><?php
    if (has_nav_menu('footer_navigation')) :
      wp_nav_menu([
        'menu'            => 'nav_menu',
        'theme_location'  => 'footer_navigation',
        'depth'           => 1,
        'container'       => '',
        'container_class' => '',
        'container_id'    => '',
        'menu_class'      => 'footer-nav',
        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
      ]);
    endif; ?>
  </div>
</div>
<!-- Close Nav Menu -->

<footer>
  <div class="container">
    <div class="w-full text-center"><?php
    if (get_theme_mod('footer_text')) :
      echo do_shortcode(get_theme_mod('footer_text')); // Shortcode wrapper function added to allow render of shortcodes added to theme theme options text field.
    endif; ?>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>

<?php n\notification('bottom'); ?>

</body>
</html>
