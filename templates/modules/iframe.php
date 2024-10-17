<?php
$iframe_class = get_sub_field('iframe_custom_class');
$iframe_url = get_sub_field('iframe_url'); ?>

<div data-iframe>
  <div class="js-main-div">
    <!-- Loading -->
    <div class="flex flex-col justify-center js-loading">
      <svg xmlns="http://www.w3.org/2000/svg" class="stroke-4 mx-auto h-14 w-14 stroke-current stroke-2 text-blue-800" viewBox="0 0 38 38">
        <g transform="translate(1 1)" fill="none">
          <circle cx="18" cy="18" r="17" />
          <path d="M35 18c0-9.3-7.6-17-17-17">
            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite" />
          </path>
        </g>
      </svg>

      <span class="text-center text-sm text-gray-500 block mt-2">Loading...</span>
    </div>

    <!-- iFrame -->
    <iframe <?php echo ($iframe_class ? ' class="' . $iframe_class . '"' : ''); ?> onload="loaded()" loading="lazy" title="iframe module" src="<?php echo $iframe_url; ?>"></iframe>
  </div>
</div>

<script>
  const loaded = () => {
    const iframe = document.querySelector('.module-iframe iframe');
    const loading = document.querySelector('.js-loading');

    iframe.classList.add('loaded');
    loading.style.display = 'none';
  };
</script>