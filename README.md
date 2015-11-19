[![Build Status](https://travis-ci.org/lambdacreatives/tofino.svg)](https://travis-ci.org/lambdacreatives/tofino) [![devDependency Status](https://david-dm.org/lambdacreatives/tofino/dev-status.svg)](https://david-dm.org/lambdacreatives/tofino#info=devDependencies) [![Deployment status from DeployBot](https://lambdacreatives.deploybot.com/badge/77558060036000/47551.svg)](http://deploybot.com)

![Tofino](https://raw.githubusercontent.com/mrchimp/tofino/master/screenshot.png)

# Tofino

A WordPress starter theme for jumpstarting custom theme development.

Developed by [Daniel Hewes](https://github.com/danimalweb), [Jake Gully](https://github.com/mrchimp).

## Requirements

| Prerequisite    | How to check | How to install
| --------------- | ------------ | ------------- |
| PHP >= 5.3.x    | `php -v`     | [php.net](http://php.net/manual/en/install.php) |
| Node.js >= 4.x.x  | `node -v`    | [nodejs.org](http://nodejs.org/) |
| gulp >= 3.9  | `gulp -v`    | `npm install -g gulp` |
| Bower >= 1.5.x | `bower -v`   | `npm install -g bower` |
| Composer >= 1.0.0-alpha10 | `composer -V`   | [getcomposer.org](http://getcomposer.org) |

## Installation

* Download a pre-complied version of the master branch: [Download Zip](http://tofino.lambdacreatives.com/tofino.zip)
* Download the latest tagged release (Coming Soon).
* Clone the git repo and run `bin/setup.sh`

## Features

* [Bootstrap 4](http://getbootstrap.com/) (Pre-release)
* Theme Options Panel ([Option Tree](https://github.com/valendesigns/option-tree))
	* Admin login screen logo
	* Google Analytics
	* Social links
	* Sticky menu
	* Sticky footer
	* Left/Center/Right Menu Positions
	* Telephone number
	* Email address
	* Company number
	* Footer text
	* Notification text / EU Cookie notice with top/bottom positions
	* Contact form with [Google reCaptcha](https://www.google.com/recaptcha) and custom email template
	* [Theme Tracker](https://github.com/lambdacreatives/tracker)
* [DOM-based routing](http://goo.gl/EUTi53)
* [SCSS](http://sass-lang.com/)
* [Gulp](http://gulpjs.com/) build script
	* Includes [JSHint](https://github.com/spalger/gulp-jshint) and [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
* [Bower](http://bower.io/) for front-end package management
* [Composer](https://getcomposer.org/) for PHP package management
* [TGM Plugin Activation](https://github.com/TGMPA/TGM-Plugin-Activation)
* [Asset Builder](https://github.com/austinpray/asset-builder) for easy asset pipeline management
* Custom Nav-walker Bootstrap 4 ready
* Relative URLs
* Namespaced functions
* 2 Widget areas
* Auto post type / slug based template routing
* SVG Sprite Shortcode `[svg sprite="my-sprite-icon"]`
* SVG Sprite helper for templates `svg('sprite-name')` or `svg(['sprite'=>'sprite-name'])`
