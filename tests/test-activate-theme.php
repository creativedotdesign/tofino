<?php
class Activate extends WP_UnitTestCase {
	function test_theme_activate() {
    $this->assertTrue( 'tofino' == wp_get_theme() );
	}
}
