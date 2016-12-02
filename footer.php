<?php
use \Tofino\ThemeOptions\Notifications as n;

if (get_theme_mod('footer_sticky') === 'enabled') : ?>
  </div>
<?php endif; ?>

<footer>
  <div class="container">
    <div class="row">
      <div class="col-xs-12"><?php
      if (get_theme_mod('footer_text')) :
        echo do_shortcode(get_theme_mod('footer_text')); // Shortcode wrapper function added to allow render of shortcodes added to theme theme options text field.
      endif; ?>
      </div>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>

<?php n\notification('bottom'); ?>

</body>
</html>
