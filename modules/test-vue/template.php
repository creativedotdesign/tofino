<?php
$props = [
  'title' => 'Hello World',
  'count' => 10,
];
?>

<div
  data-module="test-vue"
  data-props="<?php echo esc_attr(wp_json_encode($props)); ?>"
></div>
