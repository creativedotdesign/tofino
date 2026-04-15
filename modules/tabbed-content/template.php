<?php
$tabs = get_sub_field('tabs');

if ($tabs) : ?>
  <div data-module="tabbed-content">

    <div class="flex border-b border-gray-200" role="tablist">
      <?php foreach ($tabs as $i => $tab) : ?>
        <button
          class="-mb-px cursor-pointer border-b-2 border-transparent px-4 py-2 text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 <?php echo $i === 0 ? 'border-gray-900 text-gray-900' : ''; ?>"
          role="tab"
          data-tab="<?php echo esc_attr($i); ?>"
          aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
          aria-controls="tabbed-content-panel-<?php echo esc_attr($i); ?>"
          id="tabbed-content-tab-<?php echo esc_attr($i); ?>"
        >
          <?php echo esc_html($tab['tab_title']); ?>
        </button>
      <?php endforeach; ?>
    </div>

    <?php foreach ($tabs as $i => $tab) : ?>
      <div
        class="py-6"
        role="tabpanel"
        id="tabbed-content-panel-<?php echo esc_attr($i); ?>"
        aria-labelledby="tabbed-content-tab-<?php echo esc_attr($i); ?>"
        <?php echo $i !== 0 ? 'hidden' : ''; ?>
      >
        <div class="prose container max-w-none">
          <?php echo acf_esc_html($tab['tab_content']); ?>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
<?php endif; ?>
