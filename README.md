# <<<<<<< Updated upstream

[![devDependency Status](https://david-dm.org/creativedotdesign/tofino/dev-status.svg)](https://david-dm.org/creativedotdesign/tofino#info=devDependencies)

> > > > > > > Stashed changes
> > > > > > > <img src="https://raw.githubusercontent.com/creativedotdesign/tofino/master/screenshot.png" alt="Tofino" width="500">

# Tofino

A WordPress starter theme for jumpstarting custom theme development.

Developed by [Daniel Hewes](https://github.com/danimalweb), [Jake Gully](https://github.com/mrchimp).

Heavily inspired the by awesome WordPress starter theme [Sage](https://github.com/roots/sage) by [Roots](https://github.com/roots) from [Ben Word](https://github.com/retlehs) and [Scott Walkinshaw](https://github.com/swalkinshaw).

## Requirements

| Prerequisite       | How to check  | How to install                                  |
| ------------------ | ------------- | ----------------------------------------------- |
| PHP >= 7.0.0       | `php -v`      | [php.net](http://php.net/manual/en/install.php) |
| Node.js >= 14.10.0 | `node -v`     | [nodejs.org](http://nodejs.org/)                |
| Composer >= 1.9.0  | `composer -V` | [getcomposer.org](http://getcomposer.org)       |

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

- [tailwindcss](http://tailwindcss.com/)(v2.0)
- Multilingual ready (WPML)
- Responsive
- Theme Options via WP Customizer (Native)
  _ Admin login screen logo
  _ Custom Dashboard Widget
  _ Google Analytics
  _ Social links
  _ Sticky menu
  _ Sticky footer
  _ Left/Center/Right menu positions
  _ Client Data (Address, Telephone number, Email address, Company number)
  _ Footer text
  _ Notification text / EU Cookie notice with top/bottom positions
  _ Contact form with [Google reCAPTCHA](https://www.google.com/recaptcha) and custom email templates
  _ Data Tables for viewing data submitted via the contact form
  - Maintenance mode popup
  - jQuery in footer
- JS ES6 compatible via Babel and Browserify.
- [DOM-based routing](http://goo.gl/EUTi53) for DOM Ready and Window Ready via advanced router
- [Laravel Mix](https://laravel-mix.com/) build script d
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
npm run prod
```

The following files and directories should not be deployed on the server:

```
.babelrc
.editorconfig
.env
.git
.github
.eslintrc.js
.gitignore
.gitkeep
.git-ftp-ignore
.git-ftp-include
.gitattributes
.gitignore
.prettierrc.js
.stylelintrc.js
assets
node_modules
composer.json
composer.lock
package.json
package-lock.json
tailwind.config.js
webpack.mix.js
**/*.md
```
