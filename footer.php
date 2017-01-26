<?php
use \Tofino\ThemeOptions\Notifications as n;

if (get_theme_mod('footer_sticky') === 'enabled') : ?>
  </div>
<?php endif; ?>

<footer style="height: 30px; background-color: purple;">
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

<?php
// Wrapper for whole of body content. This is required because IE flexed items
// with flex-direction: column don't respect min-height values, unless they
// are themselves flex children
if (get_theme_mod('footer_sticky') === 'enabled') :
?>
  </div>
<?php endif; ?>

</body>
</html>
