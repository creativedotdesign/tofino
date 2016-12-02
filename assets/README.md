# Assets

## manifest.json

Contains arrays of JS and CSS files which are concatenated using gulp.

Contains an array of paths used by `gulpfile.js`.

Update the `devUrl` for use with the gulp watch task (uses browserSync proxy).

## /fonts

Fonts here will be copied to `dist/fonts`.

## /images

Images will be compressed, optimised and converted to progressive loading using [imagemin](https://github.com/gruntjs/grunt-contrib-imagemin).

## /scripts

Javascript files belong here. They will *not* automatically be brought into `/dist`. To do this you should add them to `manifest.json` or use require / import within an existing JS file.

Scripts will be minified when gulp is run with the `--production` flag.

## /styles

SCSS files go here. Similarly to scripts, these will not automatically be added to `/dist`. To add your styles into `dist/css/main.css`, add your file name into `main.scss`. If you want create a separate css file, add an entry into `manifest.json`.

## /svgs

### Single SVGs

SVGs added to `assets/svgs` will be minified and copied to dist/svg.

### Sprites

SVGs added to `assets/svgs/sprite` will be processed by the gulp svg-sprite task and output as a single file to `dist/svg/sprite.symbol.svg`. You can use the svg shortcode to insert them in your template.

### Font Loader

All fonts should be loaded using the [Web Font Loader](https://github.com/typekit/webfontloader). Example code can be found in `js/head.js`. For custom or other non-Google fonts checkout the docs in Web Font Loader.

A theme option has been added (In Advanced) to disable FOUT (Flash of un-styled text).
