<img src="https://raw.githubusercontent.com/creativedotdesign/tofino/master/screenshot.png" alt="Tofino" width="500">

# Tofino

A WordPress starter theme for jumpstarting custom theme development.

Developed by [Daniel Hewes](https://github.com/danimalweb), [Jake Gully](https://github.com/mrchimp).

Ongoing development is sponsored by [Creative Dot](https://creativdotdesign.com)

Heavily inspired the by awesome WordPress starter theme [Sage](https://github.com/roots/sage) by [Roots](https://github.com/roots) from [Ben Word](https://github.com/retlehs) and [Scott Walkinshaw](https://github.com/swalkinshaw).

## Requirements

| Prerequisite      | How to check  | How to install                                  |
| ----------------- | ------------- | ----------------------------------------------- |
| PHP >= 3.0        | `php -v`      | [php.net](http://php.net/manual/en/install.php) |
| Node.js >= 22.0.0 | `node -v`     | [nodejs.org](http://nodejs.org/)                |
| Composer >= 2.0.0 | `composer -V` | [getcomposer.org](http://getcomposer.org)       |

## Quick Start

- Download the latest [tagged release](https://github.com/creativedotdesign/tofino/releases).
- Clone the git repo and run the following commands:

```bash
composer install
npm install
npm run dev
```

Vite runs with HMR in development and serves built assets from `dist/` in production.

For profile and tunnel setup details, see [`docs/dev-profiles.md`](docs/dev-profiles.md).

## Features

- Folder-based feature architecture via `features/*/feature.json`.
- Manifest-based module architecture via `module.json`.
- Automatic ACF module layout registration for `content_modules`.
- Admin feature toggles with persisted enabled/disabled state.
- Automatic feature/module asset discovery and compilation to `dist/assets`.
- Vite-powered dev + production asset loading (hot file + manifest).
- Cloudflare tunnel dev profile for public HTTPS QA against local work.
- Tailwind CSS v4, TypeScript, Vue 3, and Pinia support.
- SVG spritemap generation from `src/sprite` and feature icon folders.
- ACF-powered settings and field-group driven page building.
- Optional GraphQL integration hooks for headless/data workflows.
- Markdown export integration for module-based page content.
- Fragment cache integration and utility helpers.
- Playwright QA suite (smoke, accessibility, HTML, and overflow audits).
- WPML-ready and responsive baseline templates.
- Composer + npm build pipeline with GitHub Actions release/deploy workflow.

## Documentation

- Docs index: [`docs/README.md`](docs/README.md)
- Dev profiles: [`docs/dev-profiles.md`](docs/dev-profiles.md)
- Features: [`docs/features.md`](docs/features.md)
- Modules: [`docs/modules.md`](docs/modules.md)
- Theme internals: [`docs/theme.md`](docs/theme.md)
- Front-end source: [`docs/source.md`](docs/source.md)
- Templates: [`docs/templates.md`](docs/templates.md)

## Deployment

We use [GitHub Actions](https://github.com/features/actions). Build and deploy workflows run:

```bash
composer install
npm install
npm run build
```

Review the `.git-ftp-ignore` file to check which files and directories should not be deployed on the server.
