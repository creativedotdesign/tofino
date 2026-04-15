# Modules

Modules are page-level content blocks used in the `content_modules` flexible content field.

Think of a module as a reusable section you can add to a page, such as:
- hero/content blocks
- tabbed content
- embeds/iframes

## How Modules Work

- Each module lives in `modules/<slug>/`.
- `module.json` is the source of truth for module files.
- `fields.php` registers the ACF sub-fields for that module.
- `template.php` renders the module on the front end.
- Optional `script.ts` and `style.css` are automatically discovered and compiled by Vite into `dist/assets` during build.
- Optional `markdown.php` supports markdown export.

## Typical Structure

```text
modules/my-module/
  module.json
  fields.php
  template.php
  markdown.php   (optional)
  style.css      (optional)
  script.ts      (optional)
```

## Simple Example

`modules/promo-card/module.json`

```json
{
  "title": "Promo Card",
  "acf": "fields.php",
  "template": "template.php",
  "css": "style.css",
  "script": "script.ts"
}
```

`fields.php` defines fields (for example heading, text, button link), and `template.php` outputs the markup using those values.

## Notes

- The module folder name becomes the layout name (`promo_card` for `promo-card`).
- Keep module UI, fields, and rendering together in one folder.
- Prefer small, composable modules over large multi-purpose ones.
