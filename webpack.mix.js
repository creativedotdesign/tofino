const mix = require('laravel-mix');
const SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;

// Public Path
mix.setPublicPath('./dist');

// Javascript
mix
  .js('assets/scripts/main.js', 'js/scripts.js')
  .js('assets/scripts/wp-admin.js', 'js/wp-admin.js')
  .autoload({
    // Autoload jQuery where required
    jquery: ['$', 'window.jQuery'],
  })
  .vue()
  .extract(['vue', 'body-scroll-lock', 'js-cookie', 'webfontloader']);

// Styles
mix.postCss('assets/styles/main.css', 'css/styles.css');

// Admin Styles
mix.postCss('assets/styles/base/wp-admin.css', 'css/wp-admin.css');

// Browsersync
mix.browserSync({
  proxy: process.env.BROWSERSYNC_PROXY_URL || 'tofino.test',
  files: ['**/*.{php,vue,js,css}'],
});

// Set up the spritemap and images plugins
mix.webpackConfig({
  plugins: [
    new SVGSpritemapPlugin('assets/svgs/sprites/*.svg', {
      output: {
        filename: 'svg/sprite.symbol.svg',
        chunk: { keep: true },
        svg: { sizes: false },
        svgo: true,
      },
      sprite: {
        prefix: false,
        generate: {
          title: true,
          symbol: true,
        },
      },
    }),
    new CopyPlugin({
      patterns: [
        {
          from: 'assets/images/',
          to: 'img',
          globOptions: {
            expandDirectories: {
              extensions: ['png', 'jpg', 'gif'],
            },
          },
        },
      ],
    }),
    new ImageminPlugin({ test: /\.(jpe?g|png|gif)$/i }),
  ],
});

// SVGs
mix.copy('assets/svgs/*.svg', 'dist/svg');

// Fonts
mix.copy('src/fonts/**/*', 'dist/fonts');

// Disable success notification
mix.disableSuccessNotifications();

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
