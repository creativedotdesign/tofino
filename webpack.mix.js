const mix = require('laravel-mix');

require('laravel-mix-imagemin');
require('@ayctor/laravel-mix-svg-sprite');

// Public Path
mix.setPublicPath('./dist');

// Browsersync
mix.browserSync({
  proxy: process.env.BROWSERSYNC_PROXY_URL || 'tofino.lambda.host',
  files: [
    'css/**/*.css',
    'js/**/*.js',
    '**/*.php',
    '!vendor/**/*.php'
  ],
});

// Javascript
mix.js('assets/scripts/main.js', 'js/scripts.js')
  .js('assets/scripts/head.js', 'js/head-scripts.js')
  .js('assets/scripts/wp-admin.js', 'js/wp-admin.js')
  .autoload({ // Autoload jQuery where required
    jquery: ['$', 'window.jQuery'],
    Tether: 'Tether'
  });

// Styles
mix.sass('assets/styles/main.scss', 'css/styles.css')
  .sass('assets/styles/base/wp-admin.scss', 'css/wp-admin.css');

// SVGs
// mix.svgSprite('assets/svgs/sprites/*.svg', {
//   output: {
//     filename: 'svg/sprite.symbol.svg'
//   }
// });

// Images
mix.imagemin({ // Compress and copy images
  from: 'assets/images/**/*',
  to: 'img',
  flatten: true,
});

// Fonts
mix.copyDirectory('assets/fonts', 'fonts') // Copy fonts

// Options
mix.options({
  processCssUrls: false,
});

// Webpack config
mix.webpackConfig({
  externals: {
    "jquery": "jQuery"
  }
});

// Source maps when not in production.
if (!mix.inProduction()) {
  mix.sourceMaps(true, 'source-map')
}

// Hash and version files in production.
if (mix.inProduction()) {
  mix.version();
}