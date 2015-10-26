<?php dynamic_sidebar('sidebar-below-content'); ?>

<?php if (ot_get_option('footer_sticky_checkbox')) : ?>
  </div>
<?php endif; ?>

<footer>
  <div class="container">
    <div class="row">
      <div class="col-xs-12"><?php
      if (ot_get_option('footer_text')) :
        echo do_shortcode(ot_get_option('footer_text')); //Shortcode wrapper function added to allow render of shortcodes added to theme theme options text field.
        endif; ?>
      </div>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>

<?php \Tofino\ThemeOptions\notification('bottom'); ?>

</body>
</html>
