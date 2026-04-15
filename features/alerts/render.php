<?php

/**
 * Alert rendering functions.
 *
 * @package Tofino
 * @since 5.0.0
 */

namespace Tofino\Alerts;

/**
 * Display alerts at the requested top/bottom position.
 *
 * @since 5.0.0
 * @param string $position The alert position, for example top or bottom.
 * @return void
 */
function render(string $position): void
{
  $alerts = get_field('alerts', 'option');

  if (!$alerts) {
    return;
  }

  $index = 1;

  foreach ($alerts as $alert) {
    if (!$alert['enabled']) {
      $index++;
      continue;
    }

    $excluded_pages = [];

    if (is_array($alert['hide_alert_on_specific_pages'])) {
      $excluded_pages = $alert['hide_alert_on_specific_pages'];
    }

    if (in_array(get_the_ID(), $excluded_pages, true)) {
      $index++;
      continue;
    }

    $alert_position = strtolower($alert['position']);

    if ($alert['message'] && !isset($_COOKIE['tofino-alert-' . $index . '-closed']) && $position === $alert_position) {
      get_template_part('features/alerts/template', null, [
        'position' => $alert_position,
        'message' => $alert['message'],
        'id' => $index,
        'expires' => $alert['expires'] ?? '',
      ]);
    }

    $index++;
  }
}
