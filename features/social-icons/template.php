<?php if (!empty($args['items'])) : ?>
  <ul class="<?php echo esc_attr($args['class'] ?? 'social-icons'); ?>"><?php
    foreach ($args['items'] as $item) : ?>
      <li>
        <a href="<?php echo esc_url($item['url']); ?>" target="_blank" rel="noopener noreferrer">
          <span class="sr-only"><?php echo esc_html($item['label']); ?></span>
          <?php echo svg($item['platform']); ?>
        </a>
      </li><?php
    endforeach; ?>
  </ul><?php
endif;
