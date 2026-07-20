# Templates

Theme template files live here.

- Root WordPress template files stay in the theme root.
- Reusable template parts and content templates live in `templates/`.
- Content modules do not live here anymore. They live in `modules/`.

## Responsive images

Render Media Library images with `wp_get_attachment_image()`. Pass a registered
image size for the `src` and a layout-specific `sizes` attribute so WordPress can
provide the intrinsic dimensions, `srcset`, decoding, and loading optimizations.
Use explicit `loading="eager"` and `fetchpriority="high"` only for the page's LCP
hero; images below the fold should use `loading="lazy"`.
