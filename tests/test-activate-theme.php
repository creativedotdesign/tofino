<?php
class Activate extends WP_UnitTestCase {
  function test_theme_activate() {
    $this->assertTrue('tofino' == wp_get_theme());
  }

  function test_theme_inactive() {
    $this->assertFalse('Twenty Fifteen' == wp_get_theme());
  }
}
