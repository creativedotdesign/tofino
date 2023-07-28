<?php
$data = (!empty($template_args) ? $template_args : null);

if ($data) : ?>
  <!-- Alert <?php echo $data['position']; ?> -->
  <div class="flex items-center bg-blue-400 text-white text-sm font-bold px-4 py-3 alert <?php echo $data['position']; ?>" role="alert" id="tofino-alert">
    <div class="container flex justify-between">
      <span><?php echo $data['message']; ?></span>

      <button type="button" class="w-5 h-5 js-close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true" class="text-white"><?php echo svg(['sprite' => 'icon-close', 'class' => 'w-full current-color h-full']); ?></span>
        <span class="sr-only"><?php _e('Close', 'tofino'); ?></span>
      </button>
    </div>
  </div><?php
endif;
