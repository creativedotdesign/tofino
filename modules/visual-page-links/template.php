<?php
$links = get_sub_field('links');

if ($links) : ?>
  <div data-module="visual-page-links" class="py-8">
    <div class="container grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-4">
      <?php foreach ($links as $item) :
        $link  = $item['link'];
        $image = $item['image'];

        if (empty($link['url'])) continue; ?>

        <a
          href="<?php echo esc_url($link['url']); ?>"
          class="group flex flex-col gap-3 no-underline"
          target="<?php echo esc_attr(!empty($link['target']) ? $link['target'] : '_self'); ?>"
        >
          <?php if ($image) : ?>
            <div class="overflow-hidden rounded">
              <?php echo wp_get_attachment_image(
                $image['ID'],
                'medium',
                false,
                [
                  'class' => 'h-auto w-full object-cover transition-transform duration-300 group-hover:scale-105',
                  'alt'   => esc_attr($link['title']),
                ]
              ); ?>
            </div>
          <?php endif; ?>

          <span class="text-sm font-medium text-gray-900 group-hover:underline"><?php echo esc_html($link['title']); ?></span>
        </a>

      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
