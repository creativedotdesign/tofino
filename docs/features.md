# Features

Features are reusable theme capabilities that can hook into WordPress runtime.

Think of a feature as behavior you want to enable globally, such as:
- admin customizations
- global UI elements
- site-wide helpers or integrations

## How Features Work

- Each feature lives in `features/<slug>/`.
- `feature.json` is the source of truth for what files belong to that feature.
- `render.php` is loaded at runtime for enabled features.
- `fields.php` is loaded on ACF init for enabled features.
- Feature `style.css`, `script.ts`, and `admin.ts` files are automatically discovered and compiled by Vite into `dist/assets` during build.
- Features can be toggled on/off from the Features options screen.

## Typical Structure

```text
features/my-feature/
  feature.json
  fields.php
  render.php
  style.css      (optional)
  script.ts      (optional)
  admin.ts       (optional)
```

## Simple Example

`features/hello-banner/feature.json`

```json
{
  "title": "Hello Banner",
  "scope": "frontend",
  "css": "style.css",
  "acf": "fields.php",
  "render": "render.php"
}
```

`render.php` can register hooks (for example `wp_footer`) and output a small banner.
`fields.php` can define ACF fields used by that feature.

## Notes

- `scope` can be `frontend`, `admin`, or `both`.
- Keep feature logic self-contained in its own folder.
- Prefer one clear responsibility per feature.
