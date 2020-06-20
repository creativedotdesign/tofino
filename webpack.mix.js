const mix = require('laravel-mix');

require('@ayctor/laravel-mix-svg-sprite');

// Public Path
mix.setPublicPath('./dist');

// Browsersync
mix.browserSync({
  proxy: process.env.BROWSERSYNC_PROXY_URL || 'tofino.test',
  files: ['css/**/*.css', 'js/**/*.js', '**/*.php', '!vendor/**/*.php'],
});

// Javascript
mix
  .js('assets/scripts/main.js', 'js/scripts.js')
  .js('assets/scripts/wp-admin.js', 'js/wp-admin.js')
  .autoload({
    // Autoload jQuery where required
    jquery: ['$', 'window.jQuery'],
  });
// .extract(['vue', 'stickyfill-web-module', 'js-cookie']);

// Styles
mix.postCss('assets/styles/main.css', 'css/styles.css', [
  /* eslint-disable global-require */
  require('postcss-import'),
  require('tailwindcss'),
  require('postcss-mixins'),
  require('postcss-nested'),
]);

// SVGs
mix.svgSprite('assets/svgs/sprites/*.svg', {
  output: {
    filename: 'svg/sprite.symbol.svg',
  }
});

// Images
mix.copy('assets/images/**/*.{jpg,jpeg,png,gif}', 'img');

// Options
mix.options({
  processCssUrls: false,
});

// Webpack config
mix.webpackConfig({
  externals: {
    jquery: 'jQuery',
  },
});

// Source maps when not in production.
if (!mix.inProduction()) {
  mix.sourceMaps(true, 'source-map');
}

// Hash and version files in production.
if (mix.inProduction()) {
  mix.version();
}
