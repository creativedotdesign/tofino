<?php

/**
 * Required Alt Text — runtime.
 *
 * Server-side half of the feature: marks the "Alternative Text" field required
 * for images on the server-rendered attachment compat fields (a no-JS cue on
 * the attachment edit screen / classic editor). The live validation + hard
 * block live in admin.ts / admin.css, which the theme's admin build bundles and
 * enqueues automatically (feature.json scope:admin, script:admin.ts).
 *
 * Required at boot by the FeatureRegistry; gets an enable/disable toggle on
 * General Options → Features automatically.
 *
 * @package Tofino
 */

namespace Tofino\RequiredAltText;

if (!defined('ABSPATH')) {
  exit;
}

// Everything here is wp-admin only.
if (!is_admin()) {
  return;
}

/**
 * Append a red "required" asterisk to the Alt Text label for images on the
 * server-rendered compat fields (the modal's own field is Backbone-rendered and
 * handled in admin.ts).
 *
 * @param array    $fields Attachment form fields.
 * @param \WP_Post $post   The attachment.
 * @return array
 */
function mark_alt_required(array $fields, \WP_Post $post): array
{
  if (isset($fields['image_alt']) && wp_attachment_is_image($post->ID)) {
    $fields['image_alt']['label'] = __('Alternative Text', 'tofino')
      . ' <span class="req-alt-star" aria-hidden="true">*</span>';
    $fields['image_alt']['required'] = true;
  }

  return $fields;
}
add_filter('attachment_fields_to_edit', __NAMESPACE__ . '\\mark_alt_required', 10, 2);
