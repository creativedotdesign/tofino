<footer>
  <div class="container">
    <div class="w-full text-center"><?php

      if (has_nav_menu('footer_navigation')) : ?>
        <!-- Nav Menu --><?php
        wp_nav_menu([
          'menu'            => 'nav_menu',
          'theme_location'  => 'footer_navigation',
          'depth'           => 1,
          'container'       => '',
          'container_class' => '',
          'container_id'    => '',
          'menu_class'      => 'footer-nav',
          'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        ]); ?>
        <!-- Close Nav Menu --><?php
      endif;

      $footer_text = get_field('footer_text', 'general-options');
      if ($footer_text) :
        echo do_shortcode($footer_text); // Shortcode wrapper function added to allow render of shortcodes added to theme theme options text field.
      endif; ?>

    </div>
  </div>
</footer>

<?php wp_footer(); ?>

<?php Tofino\Init\alerts('bottom'); ?>

</body>
</html>
