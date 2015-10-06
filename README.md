[![Build Status](https://travis-ci.org/mrchimp/tofino.svg)](https://travis-ci.org/mrchimp/tofino)  [![Dev Dependencies](https://david-dm.org/lambdacreatives/tofino.svg)](https://devid-dm.org/lambdacreatives/tofino)  [![Deployment status from DeployBot](https://lambdacreatives.deploybot.com/badge/77558060036000/47551.svg)](http://deploybot.com)

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

## Features

* [Bootstrap 4](http://getbootstrap.com/)
* Theme Options Panel ([Option Tree](https://github.com/valendesigns/option-tree))
	* Admin login screen logo
	* Google Analytics
	* Social links
	* Sticky menu
	* Sticky footer
	* Left/Center/Right Menu Positions
	* Telephone number
	* Email address
	* Footer text
	* Notification Text
* [DOM-based routing](http://goo.gl/EUTi53)
* SCSS
* [Gulp](http://gulpjs.com/) build script
* [Bower](http://bower.io/) for front-end package management
* [Composer](https://getcomposer.org/) for PHP package management
* [TGM Plugin Activation](https://github.com/TGMPA/TGM-Plugin-Activation)
* [WP-Bootstrap-Navwalker](https://github.com/twittem/wp-bootstrap-navwalker)
* SVG Sprite Shortcode `[svg sprite="my-sptite-icon"]`
* SVG Sprite helper for templates `svg('sprite-name')` or `svg(['sprite'=>'sprite-name'])`
