<?php
$form_id = get_sub_field('form_select');

if (!is_plugin_active('form-builder/index.php')) {
  echo 'Please install and activate the Tofino Form Builder Plugin';
} else {
  \Tofino\Helpers\hm_get_template_part('../../plugins/form-builder/template', ['form_id' => $form_id]);
}
