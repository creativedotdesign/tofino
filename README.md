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
| PHP >= 5.5.9              | `php -v`      | [php.net](http://php.net/manual/en/install.php) |
| Node.js >= 6.x.x          | `node -v`     | [nodejs.org](http://nodejs.org/)                |
| gulp >= 3.9               | `gulp -v`     | `npm install -g gulp`                           |
| Composer >= 1.0.0	        | `composer -V` | [getcomposer.org](http://getcomposer.org)       |

## Installation

* Download a pre-complied version of the dev branch: [Download Zip](http://tofino.lambdacreatives.com/tofino.zip).
* Download the latest [tagged release](https://github.com/creativedotdesign/tofino/releases).
* Clone the git repo and run `bin/setup.sh` (from your dev machine).

Once you have activated the theme, access Theme Options (WP Customizer) update an option and select save to commit the default values to the database.

## Features

* [Bootstrap 4](http://getbootstrap.com/) (Pre-release Alpha 4)
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
	* Critical CSS (with loadCSS function)
	* [Theme Tracker](https://github.com/lambdacreatives/tracker)
* JS ES6 compatible via Babel and Browserify.
* [DOM-based routing](http://goo.gl/EUTi53) for DOM Ready and Window Ready via advanced router
* [SCSS](http://sass-lang.com/)
* [Gulp](http://gulpjs.com/) build script
	* Includes [eslint](https://github.com/eslint/eslint), [stylelint](https://github.com/stylelint/stylelint), and [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) for keeping source code up to standard. Custom rulesets for adding additional / tweaking rules.
	* Includes [Google Page Speed Insights](https://github.com/addyosmani/psi), [W3C Validation](https://github.com/addyosmani/psi) and [AccessSniff](https://github.com/yargalot/AccessSniff) via [Ngrok](https://github.com/bubenshchykov/ngrok)
	* Use `gulp help` for a full task list with descriptions
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
npm install npm -g
npm install --loglevel error
gulp --production
```

The following files and directories are excluded from being uploaded:

```
assets
bin
gulp
node_modules
.eslintrc.yml
.gitattributes
.gitignore
.stylelintrc.yml
.travis.yml
.npmrc
.hound.yml
bitbucket-pipelines.yml
composer.json
composer.lock
gulpfile.js
package.json
ruleset.xml
**/*.md
yarn.lock
```
