<?php if (!empty($args)) : ?>
  <!-- Alert <?php echo esc_attr($args['position']); ?> | ID <?php echo esc_attr($args['id']); ?> -->
  <div class="items-center px-4 py-3 text-sm alert <?php echo esc_attr($args['position']); ?>" data-feature="alerts" data-alert-id="<?php echo esc_attr($args['id']); ?>" data-variant="<?php echo esc_attr($args['variant'] ?? 1); ?>" data-expires="<?php echo esc_attr($args['expires'] ?? ''); ?>" role="alert">
    <div class="flex items-center justify-between gap-4">
      <span><?php echo acf_esc_html($args['message']); ?></span>

      <button type="button" class="js-close flex h-3 w-3 shrink-0 items-center justify-center" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true"><?php echo svg(['sprite' => 'close', 'class' => 'current-color size-2.5']); ?></span>
        <span class="sr-only"><?php esc_html_e('Close', 'tofino'); ?></span>
      </button>
    </div>
  </div><?php
endif;
