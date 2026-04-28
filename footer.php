<footer>
  <div class="container">
    <div class="w-full text-center"><?php

      if (has_nav_menu('footer_navigation')) : ?>
        <!-- Nav Menu --><?php
        wp_nav_menu([
          'menu'            => 'nav_menu',
          'theme_location'  => 'footer_navigation',
          'depth'           => 1,
          'menu_class'      => 'footer-nav',
          'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        ]); ?>
        <!-- Close Nav Menu --><?php
      endif;

      $footer_text = get_field('footer_text', 'option');
      if ($footer_text) :
        echo wp_kses_post(do_shortcode($footer_text)); // Shortcode wrapper function added to allow render of shortcodes added to theme theme options text field.
      endif; ?>

    </div>
  </div>
</footer>

<?php if (function_exists('Tofino\Alerts\render')) {
  Tofino\Alerts\render('bottom');
} ?>

<?php wp_footer(); ?>

<?php do_action('tofino_after_footer'); ?>

</body>
</html>
