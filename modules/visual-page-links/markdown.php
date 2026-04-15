<?php

use function Tofino\Helpers\escape_markdown_text;

$links = get_sub_field('links');

if (!is_array($links) || !$links) {
  return;
}

echo "## Visual Page Links\n\n";

foreach ($links as $item) {
  $link = is_array($item['link'] ?? null) ? $item['link'] : [];
  $url = esc_url_raw((string) ($link['url'] ?? ''));

  if ($url === '') {
    continue;
  }
  $title_raw = (string) ($link['title'] ?? '');
  $title = sanitize_text_field($title_raw);
  $title = $title !== '' ? $title : $url;

  echo '- [' . escape_markdown_text($title) . '](' . $url . ")\n";
}
