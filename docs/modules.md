# Modules

Modules are page-level content blocks used in the `content_modules` flexible content field.

Think of a module as a reusable section you can add to a page, such as:
- hero/content blocks
- tabbed content
- embeds/iframes

## How Modules Work

- Theme modules live in `modules/<slug>/`; plugin modules register their own `module.json` files with `tofino/register_modules`.
- `module.json` is the source of truth for the module name and files.
- The declared `acf` file registers the ACF sub-fields for that module.
- The declared `template` file renders the module on the front end.
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
  "name": "promo_card",
  "acf": "fields.php",
  "template": "template.php",
  "css": "style.css",
  "script": "script.ts"
}
```

`fields.php` defines fields (for example heading, text, button link), and `template.php` outputs the markup using those values.

## Notes

- `module.json` must include a valid snake_case `name`; that value becomes the ACF layout name.
- The ACF field group key must be `group_module_{name}`.
- Keep module UI, fields, and rendering together in one folder.
- Prefer small, composable modules over large multi-purpose ones.
