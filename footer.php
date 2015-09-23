<?php dynamic_sidebar('sidebar-below-content'); ?>

<footer>
  <div class="container">
    <div class="row">
      <div class="col-xs-12"><?php
          if ( ot_get_option( 'footer_text' ) ) :
            echo ot_get_option( 'footer_text' );
          endif; ?>
      </div>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
