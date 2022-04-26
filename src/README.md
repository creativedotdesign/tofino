# Assets

## /img

Images will be compressed, optimised and converted to progressive loading using Imagemin.

## /js

Javascript files belong here.

Scripts will be minified when `npm run build` is run.

## /css

CSS files go here. Similarly to scripts, these will not automatically be added to `/dist`. To add your styles into `dist/css/main.css`, add your file name into `main.css`.

## /svgs

### Single SVGs

SVGs added to `public/svgs` will be minified and copied to dist/svgs.

### Sprites

SVGs added to `sprite` will be processed by the main build task and output as a single SVG just before the closing `</body>` tag.

### Font Loader

All fonts should be loaded using the [Web Font Loader](https://github.com/typekit/webfontloader).

A theme option has been added to disable FOUT (Flash of un-styled text).
