<?php dynamic_sidebar('sidebar-below-content'); ?>

<div class="container">
  <div class="row">
    <div class="col-xs-12">
       <footer><?php
        if ( ot_get_option( 'footer_text' ) ) :
          echo ot_get_option( 'footer_text' );
        endif; ?>
      </footer>
    </div>
  </div>
</div>
<?php wp_footer(); ?>
</body>
</html>
