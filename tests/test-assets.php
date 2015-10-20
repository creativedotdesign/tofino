<?php
class Assets extends WP_UnitTestCase {

  function test_styles() {
    $this->go_to('/');
    /*$this->assertTrue(wp_script_is('js-all', 'enqueued'));
    $this->assertNotFalse( has_action( 'wp_enqueue_scripts', 'Tofino\Assets\daniel' ) );
    global $wp_filter;
    $this->assertarrayHasKey( 'daniel', $wp_filter['wp_enqueue_scripts'][10] );
    $this->assertTrue( wp_style_is( 'base', 'registered' ) );*/
  }
}
