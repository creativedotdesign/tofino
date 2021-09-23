# Assets

## /img

Images will be compressed, optimised and converted to progressive loading using Imagemin.

## /js

Javascript files belong here.

Scripts will be minified when `npm run prod` is run.

## /css

CSS files go here. Similarly to scripts, these will not automatically be added to `/dist`. To add your styles into `dist/css/main.css`, add your file name into `main.css`.

## /svgs

### Single SVGs

SVGs added to `svgs` will be minified and copied to dist/svg.

### Sprites

SVGs added to `svgs/sprite` will be processed by the main build task and output as a single file to `dist/svg/sprite.svg`. You can use the svg shortcode to insert them in your template.

### Font Loader

All fonts should be loaded using the [Web Font Loader](https://github.com/typekit/webfontloader).

A theme option has been added (In Advanced) to disable FOUT (Flash of un-styled text).
