var manifest = ('./assets/manifest.json'),
    critical = require('critical'),
    gulpif   = require('gulp-if');

// Critical path css
module.exports = function (gulp, stagingUrl) {
  'use strict';
  var paths = manifest.paths,
  config    = manifest.config;
  gulp.task(
    'styles:critical',
    'Generates the Critical CSS file based on dimentions in the array.', [
      'styles',
      'styles:lint'
    ],
    function() {
      return critical.generate({
        src: gulpif(stagingUrl, config.stagingUrl, config.devUrl),
        dest: paths.dist + '/css/critical.css',
        ignore: ['@font-face',/url\(/],
        // pathPrefix: '/wp-content/themes/tofino/' + path.dist + 'fonts',
        minify: true,
        dimensions: [{
          height: 627,
          width: 370
        }, {
          height: 900,
          width: 1200
        }]
      });
    }
  );
};
