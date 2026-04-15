<?php if (!empty($args)) : ?>
  <!-- Alert <?php echo esc_attr($args['position']); ?> | ID <?php echo esc_attr($args['id']); ?> -->
  <div class="items-center px-4 py-3 text-sm font-bold text-white alert <?php echo esc_attr($args['position']); ?>" data-feature="alerts" data-alert-id="<?php echo esc_attr($args['id']); ?>" data-expires="<?php echo esc_attr($args['expires'] ?? ''); ?>" role="alert">
    <div class="container flex justify-between">
      <span><?php echo acf_esc_html($args['message']); ?></span>

      <button type="button" class="js-close h-5 w-5" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true" class="text-white"><?php echo svg(['sprite' => 'close', 'class' => 'current-color h-full w-full']); ?></span>
        <span class="sr-only"><?php esc_html_e('Close', 'tofino'); ?></span>
      </button>
    </div>
  </div><?php
endif;
