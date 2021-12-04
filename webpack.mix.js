const mix = require('laravel-mix');
const SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const ImageMinimizerPlugin = require('image-minimizer-webpack-plugin');

// Public Path
mix.setPublicPath('./dist');

// Javascript
mix
  .js('src/js/app.js', 'js/app.js')
  .js('src/js/wp-admin.js', 'js/wp-admin.js')
  .autoload({
    // Autoload jQuery where required
    jquery: ['$', 'window.jQuery'],
  })
  .vue()
  .extract();

// Styles
mix.postCss('src/css/main.css', 'css/styles.css');

// Admin Styles
mix.postCss('src/css/base/wp-admin.css', 'css/wp-admin.css');

// Browsersync
mix.browserSync({
  proxy: process.env.BROWSERSYNC_PROXY_URL || 'tofino.test',
  files: ['**/*.{php,vue,js,css}'],
});

// Set up the spritemap and images plugins
mix.webpackConfig({
  plugins: [
    new SVGSpritemapPlugin('src/svgs/sprites/*.svg', {
      output: {
        filename: 'svg/sprite.svg',
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
          from: 'src/img/',
          to: 'img',
          globOptions: {
            expandDirectories: {
              extensions: ['png', 'jpg', 'gif'],
            },
          },
        },
      ],
    }),
    new ImageMinimizerPlugin({ test: /\.(jpe?g|png|gif)$/i }),
  ],
});

// SVGs
mix.copy('src/svgs/*.svg', 'dist/svg');

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
  output: {
    chunkFilename: 'js/chunks/[name].js',
    publicPath: process.env.THEME_PATH + '/dist/' || '/wp-content/themes/tofino/dist/',
  },
});

// Source maps when not in production.
if (!mix.inProduction()) {
  mix.webpackConfig({ devtool: 'inline-source-map' });
}

// Hash and version files in production.
if (mix.inProduction()) {
  mix.version();
}
