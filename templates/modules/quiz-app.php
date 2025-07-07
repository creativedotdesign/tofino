<?php
if (!is_plugin_active('dci-quiz-app/index.php')) {
  echo 'Please install and activate the DCI Quiz Plugin.';
} else {
  \Tofino\Helpers\hm_get_template_part('../../plugins/dci-quiz-app/template');
}