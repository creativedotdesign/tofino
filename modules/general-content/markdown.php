<?php

use function Tofino\Helpers\convert_html_to_markdown;

$content = (string) get_sub_field('general_content', true, true);

if ($content === '') {
  return;
}

echo convert_html_to_markdown($content);
