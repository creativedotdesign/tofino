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

  if ($position === 'top') {
    echo '<div data-alert-region>';
  }

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

    // Always include eligible alerts in the cached HTML. Dismissal is a
    // visitor-specific preference and is resolved in the browser; checking the
    // cookie here could let one visitor prime a shared full-page cache with the
    // alert omitted for everyone.
    if ($alert['message'] && $position === $alert_position) {
      get_template_part('features/alerts/template', null, [
        'position' => $alert_position,
        'message' => $alert['message'],
        'id' => $index,
        // First alert = Alert 1 colour, second = darker Alert 2 (see Figma).
        'variant' => $index === 1 ? 1 : 2,
        'expires' => $alert['expires'] ?? '',
      ]);
    }

    $index++;
  }

  if ($position === 'top') {
    ?>
    </div>
    <script data-cfasync="false">
      (() => {
        const region = document.querySelector('[data-alert-region]');

        if (!region) {
          return;
        }

        const cookieNames = new Set(
          document.cookie
            .split(';')
            .map((cookie) => cookie.trim().split('=', 1)[0])
            .filter(Boolean)
        );

        region.querySelectorAll('[data-alert-id]').forEach((alert) => {
          const alertId = alert.dataset.alertId;
          const dismissed = alertId
            ? cookieNames.has(`tofino-alert-${alertId}-closed`)
            : false;

          alert.hidden = dismissed;

          if (!dismissed) {
            alert.style.display = 'block';
          }
        });

        document.documentElement.style.setProperty(
          '--alert-height',
          `${region.offsetHeight}px`
        );
      })();
    </script>
    <?php
  }
}
