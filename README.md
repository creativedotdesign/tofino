[![devDependency Status](https://david-dm.org/creativedotdesign/tofino/dev-status.svg)](https://david-dm.org/creativedotdesign/tofino#info=devDependencies)

<img src="https://raw.githubusercontent.com/creativedotdesign/tofino/master/screenshot.png" alt="Tofino" width="500">

# Tofino

A WordPress starter theme for jumpstarting custom theme development.

Developed by [Daniel Hewes](https://github.com/danimalweb), [Jake Gully](https://github.com/mrchimp).

Ongoing development is sponsored by [Creative Dot](https://creativdotdesign.com)

Heavily inspired the by awesome WordPress starter theme [Sage](https://github.com/roots/sage) by [Roots](https://github.com/roots) from [Ben Word](https://github.com/retlehs) and [Scott Walkinshaw](https://github.com/swalkinshaw).

## Requirements
| Prerequisite       | How to check  | How to install                                  |
| ------------------ | ------------- | ----------------------------------------------- |
| PHP >= 7.4.0       | `php -v`      | [php.net](http://php.net/manual/en/install.php) |
| Node.js >= 14.0.0  | `node -v`     | [nodejs.org](http://nodejs.org/)                |
| Composer >= 2.0.0  | `composer -V` | [getcomposer.org](http://getcomposer.org)       |

## Installation

- Download the latest [tagged release](https://github.com/creativedotdesign/tofino/releases).
- Clone the git repo and run the following commands:

```
composer install
npm install
npm run dev
```

Once you have activated the theme, access Theme Options (WP Customizer) update an option and select save to commit the default values to the database.

## Features

- [tailwindcss](http://tailwindcss.com/)(v3.0)
- Multilingual ready (WPML)
- Responsive
- General Options via ACF
  - Admin login screen logo
  - Custom Dashboard Widget
  - Social links
  - Sticky menu
  - Sticky footer
  - Client Data (Address, Telephone number, Email address, Company number)
  - Footer text
  - Alert Bar with top/bottom positions
  - Contact form and custom email templates
  - Data Tables for viewing data submitted via the contact form
  - Maintenance mode popup
  - Custom 404 page
- [Advanced Custom Fields](https://www.advancedcustomfields.com/resources/getting-started/)
- TypeSctipts (JS)
- [Vite](https://vitejs.dev/guide/) build script
- [Composer](https://getcomposer.org/) for PHP package management
- Namespaced functions
- Auto post type / slug based template routing
- Shortcodes
- [Web Font Loader](https://github.com/typekit/webfontloader) load Google, Typekit and custom fonts.
- Fragment Cache class

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
.eslintrc.js
.git
.github
.gitignore
.gitkeep
.git-ftp-ignore
.git-ftp-include
.gitattributes
.gitignore
.stylelintrc.js
.prettierrc.js
.npmrc
composer.json
composer.lock
package.json
package-lock.json
postcss.config.js
tailwind.config.js
vite.config.js
phpcs.xml
*.md
```
