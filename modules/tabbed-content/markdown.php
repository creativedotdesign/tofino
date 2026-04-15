<?php

use function Tofino\Helpers\convert_html_to_markdown;
use function Tofino\Helpers\escape_markdown_text;

$tabs = get_sub_field('tabs');

if (!is_array($tabs) || !$tabs) {
  return;
}

echo "## Tabbed Content\n\n";

foreach ($tabs as $tab) {
  $title = sanitize_text_field((string) ($tab['tab_title'] ?? ''));
  $title = $title !== '' ? $title : __('Tab', 'tofino');
  $content = (string) ($tab['tab_content'] ?? '');

  echo '### ' . escape_markdown_text($title) . "\n\n";

  $tab_markdown = convert_html_to_markdown($content);
  if ($tab_markdown !== '') {
    echo $tab_markdown . "\n\n";
  }
}
