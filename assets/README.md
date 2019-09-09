# Assets

## /fonts

Fonts here will be copied to `dist/fonts`.

## /images

Images will be compressed, optimised and converted to progressive loading using Imagemin.

## /scripts

Javascript files belong here.

Scripts will be minified when `npm run prod` is run.

## /styles

SCSS files go here. Similarly to scripts, these will not automatically be added to `/dist`. To add your styles into `dist/css/main.css`, add your file name into `main.scss`.

## /svgs

### Single SVGs

SVGs added to `assets/svgs` will be minified and copied to dist/svg.

### Sprites

SVGs added to `assets/svgs/sprite` will be processed by the main build task and output as a single file to `dist/svg/sprite.symbol.svg`. You can use the svg shortcode to insert them in your template.

### Font Loader

All fonts should be loaded using the [Web Font Loader](https://github.com/typekit/webfontloader). Example code can be found in `js/head.js`. For custom or other non-Google fonts checkout the docs in Web Font Loader.

A theme option has been added (In Advanced) to disable FOUT (Flash of un-styled text).
