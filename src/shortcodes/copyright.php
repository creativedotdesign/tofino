<?php
/**
 * Copyright Shortcode
 *
 * @since 1.0.0
 * @return string HTML output copyright string.
 */
function copyright() {
  return '&copy; ' . date('Y') . ' <span class="copyright-site-name">' . get_bloginfo('name') . '</span>.';
}
add_shortcode('copyright', 'copyright');
