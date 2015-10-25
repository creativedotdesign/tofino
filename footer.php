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

<?php if (ot_get_option('notification_text') && !isset($_COOKIE['tofino-notification-closed'])) : ?>
    <div class="alert alert-info fixed-bottom" id="tofino-notification">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
        </button>
        <?php echo ot_get_option('notification_text') ?>
    </div>
<?php endif; ?>

</body>
</html>
