[![Build Status](https://travis-ci.org/creativedotdesign/tofino.svg)](https://travis-ci.org/creativedotdesign/tofino) [![devDependency Status](https://david-dm.org/creativedotdesign/tofino/dev-status.svg)](https://david-dm.org/creativedotdesign/tofino#info=devDependencies) [![Deployment status from DeployBot](https://lambdacreatives.deploybot.com/badge/77558060036000/47551.svg)](http://deploybot.com)

<img src="https://raw.githubusercontent.com/creativedotdesign/tofino/master/screenshot.png" alt="Tofino" width="500">

# Tofino

A WordPress starter theme for jumpstarting custom theme development.

Developed by [Daniel Hewes](https://github.com/danimalweb), [Jake Gully](https://github.com/mrchimp).

Heavily inspired the by awesome WordPress starter theme [Sage](https://github.com/roots/sage) by [Roots](https://github.com/roots) from [Ben Word](https://github.com/retlehs) and [Scott Walkinshaw](https://github.com/swalkinshaw).

[Demo](http://tofino.lambdacreatives.com)

## Requirements

| Prerequisite              | How to check  | How to install                                  |
| ------------------------- | ------------- | ----------------------------------------------- |
| PHP >= 7.0.0              | `php -v`      | [php.net](http://php.net/manual/en/install.php) |
| Node.js >= 10.10.0        | `node -v`     | [nodejs.org](http://nodejs.org/)                |
| yarn >= 3.9               | `yarn -v`     | `brew install yarn` (MacOS)                     |
| Composer >= 1.9.0	        | `composer -V` | [getcomposer.org](http://getcomposer.org)       |

## Installation

* Download a pre-complied version of the dev branch: [Download Zip](http://tofino.lambdacreatives.com/tofino.zip).
* Download the latest [tagged release](https://github.com/creativedotdesign/tofino/releases).
* Clone the git repo and run `bin/setup.sh` (from your dev machine).

Once you have activated the theme, access Theme Options (WP Customizer) update an option and select save to commit the default values to the database.

## Features

* [Bootstrap 4](http://getbootstrap.com/) (v4.3)
* Multilingual ready (WPML)
* Responsive
* Theme Options via WP Customizer (Native)
	* Admin login screen logo
	* Custom Dashboard Widget
	* Google Analytics
	* Hotjar Tracking
	* Social links
	* Sticky menu
	* Sticky footer
	* Left/Center/Right menu positions
	* Telephone number
	* Email address
	* Company number
	* Footer text
	* Notification text / EU Cookie notice with top/bottom positions
	* Contact form with [Google reCAPTCHA](https://www.google.com/recaptcha) and custom email templates
	* Data Tables for viewing data submitted via the contact form
	* Maintenance mode
	* jQuery in footer
	* [Theme Tracker](https://github.com/lambdacreatives/tracker)
* JS ES6 compatible via Babel and Browserify.
* [DOM-based routing](http://goo.gl/EUTi53) for DOM Ready and Window Ready via advanced router
* [SCSS](http://sass-lang.com/)
* [Laravel Mix](https://laravel-mix.com/) build script
* [Composer](https://getcomposer.org/) for PHP package management
* Custom Nav-walker Bootstrap 4 ready
* Namespaced functions
* Auto post type / slug based template routing
* Shortcodes
* AjaxForm handler class. Easily handle form validation and processing with Wordpress Ajax. Send submitted data via email and/or save it as post meta. Add your own custom validator / processor via a simple function hook.
* [Web Font Loader](https://github.com/typekit/webfontloader) load Google, Typekit and custom fonts.
* Fragment Cache class

## Documentation

Docs are provided by README.md files in each directory.

## Deployment

We use [Deploybot](https://deploybot.com). The deployment VM is issued the following commands:

```
composer install
yarn install npm -g
yarn install
npm run prod
```

The following files and directories are excluded from being uploaded:

```
assets
bin
node_modules
.eslintrc.json
.gitattributes
.gitignore
.stylelintrc.yml
.hound.yml
composer.json
composer.lock
package.json
ruleset.xml
**/*.md
yarn.lock
```
