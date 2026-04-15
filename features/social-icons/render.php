<?php

/**
 * Social icons rendering.
 *
 * @package Tofino
 * @since 5.0.0
 */

/**
 * Returns the supported social platforms keyed by field name.
 *
 * @since 5.0.0
 * @return array<string, string>
 */
function social_icons_platforms(): array
{
  return [
    'facebook'   => 'Facebook',
    'x'          => 'X',
    'instagram'  => 'Instagram',
    'linkedin'   => 'LinkedIn',
    'pinterest'  => 'Pinterest',
    'youtube'    => 'Youtube',
    'soundcloud' => 'SoundCloud',
    'vimeo'      => 'Vimeo',
    'tiktok'     => 'TikTok',
  ];
}

/**
 * Renders a list of social media icon links from ACF options.
 *
 * Usage: [social_icons class="my-class" platforms="facebook,x"]
 *
 * @since 5.0.0
 *
 * @param array|string $atts {
 *   Optional. Shortcode attributes.
 *   @type string $class     CSS class to add to the UL element.
 *   @type string $platforms Comma-separated list of platforms to display.
 * }
 * @return string HTML output of unordered list with social icons.
 */
function social_icons(array|string $atts = []): string
{
  $atts = shortcode_atts([
    'class'     => '',
    'platforms' => '',
  ], $atts, 'social_icons');

  $social_links = get_field('social_media_links', 'option');

  if (empty($social_links) || !array_filter($social_links)) {
    return '';
  }

  $available_platforms = social_icons_platforms();
  $ordered_keys = array_keys($available_platforms);

  if (!empty($atts['platforms'])) {
    $requested = array_map('trim', explode(',', $atts['platforms']));
    $ordered_keys = array_values(array_intersect($requested, $ordered_keys));
  }

  $items = [];

  foreach ($ordered_keys as $platform) {
    $url = $social_links[$platform] ?? '';

    if (!$url) {
      continue;
    }

    $items[] = [
      'label' => $available_platforms[$platform],
      'platform' => $platform,
      'url' => $url,
    ];
  }

  if (!$items) {
    return '';
  }

  ob_start();

  get_template_part('features/social-icons/template', null, [
    'class' => trim('social-icons ' . (string) $atts['class']),
    'items' => $items,
  ]);

  return (string) ob_get_clean();
}
add_shortcode('social_icons', 'social_icons');
