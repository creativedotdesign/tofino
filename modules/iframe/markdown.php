<?php

$iframe_url = esc_url_raw((string) get_sub_field('iframe_url'));

if ($iframe_url === '') {
  return;
}

echo "## Embedded iFrame\n\n";
echo '- Source: [Open iFrame URL](' . $iframe_url . ")\n";
