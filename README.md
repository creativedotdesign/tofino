<img src="https://raw.githubusercontent.com/creativedotdesign/tofino/master/screenshot.png" alt="Tofino" width="500">

# Tofino

A WordPress starter theme for jumpstarting custom theme development.

Developed by [Daniel Hewes](https://github.com/danimalweb), [Jake Gully](https://github.com/mrchimp).

Ongoing development is sponsored by [Creative Dot](https://creativdotdesign.com)

Heavily inspired the by awesome WordPress starter theme [Sage](https://github.com/roots/sage) by [Roots](https://github.com/roots) from [Ben Word](https://github.com/retlehs) and [Scott Walkinshaw](https://github.com/swalkinshaw).

## Requirements

| Prerequisite      | How to check  | How to install                                  |
| ----------------- | ------------- | ----------------------------------------------- |
| PHP >= 8.2.0      | `php -v`      | [php.net](http://php.net/manual/en/install.php) |
| Node.js >= 20.0.0 | `node -v`     | [nodejs.org](http://nodejs.org/)                |
| Composer >= 2.0.0 | `composer -V` | [getcomposer.org](http://getcomposer.org)       |

## Installation

- Download the latest [tagged release](https://github.com/creativedotdesign/tofino/releases).
- Clone the git repo and run the following commands:

```
composer install
npm install
npm run dev
```

Note that the Vite Dev Server runs on port 3000. You access the website via the hostname and Vite will HMR or refresh automatically. If the Vite Dev Server is not running the website will pull it's assets from the /dist directory.

Important: You MUST set `WP_ENVIRONMENT_TYPE` to `development` or `local` in your wp-config.php file for the Vite Dev Server to work. Local by Flywheel does this automatically.

## Features

- [TailwindCSS](http://tailwindcss.com/) (v3.4)
- Multilingual ready (WPML)
- Responsive
- General Options via ACF
  - Admin login screen logo
  - Custom Dashboard Widget
  - Social links
  - Sticky header menu
  - Client Data (Address, Telephone number, Email address, Company number)
  - Footer text
  - Alert Bar with top/bottom positions
  - Maintenance mode popup
  - Custom 404 page
- [Advanced Custom Fields](https://www.advancedcustomfields.com/resources/getting-started/)
- ACF JSON Folder
- [TypeScript](https://www.typescriptlang.org/)
- [Vite](https://vitejs.dev/guide/) build script
- [Vitest](https://vitest.dev/) for testing Vue components
- [Cypress](https://www.cypress.io/) for Integration and E2E tests
- [Composer](https://getcomposer.org/) for PHP package management
- Namespaced functions
- Auto post type / slug based template routing
- Shortcodes
- SVG Sprite
- [Web Font Loader](https://github.com/typekit/webfontloader) load Google, Typekit and custom fonts
- VueJS v3.x with Composition API
- Pinia State Management
- Form support via Tofino Form Builder plugin
- AjaxForm PHP Class
- Fragment Cache PHP Class

## Documentation

Docs are provided by README.md files in each directory.

## Deployment

We use [GitHub Actions](https://github.com/features/actions). The deployment script is issued the following commands:

```
composer install
npm install
npm run build
```

The following files and directories should not be deployed on the server:

```
src
node_modules
.vscode
.editorconfig
.env
.eslintrc.cjs
.git
.github
.gitignore
.gitkeep
.git-ftp-ignore
.git-ftp-include
.gitattributes
.gitignore
.prettierignore
.npmrc
composer.json
composer.lock
package.json
package-lock.json
postcss.config.cts
tsconfig.json
vite.config.ts
phpcs.xml
\*.md
cypress
cypress.config.ts
prettier.config.cjs
stylelint.config.ts
tailwind.config.ts
vite.config.ts
vite-env.d.ts
```
