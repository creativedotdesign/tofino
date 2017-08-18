var manifest = require('../assets/manifest.json'),
    critical = require('critical');

// Critical path css
module.exports = function (gulp, util) {
  'use strict';
  var paths = manifest.paths;
  gulp.task(
    'styles:critical',
    'Generates the Critical CSS file based on dimentions in the array.', [
      'styles',
      'styles:lint',
      'browser-sync',
      'ngrok'
    ],
    function() {
      var site = process.env.URL;
      return critical.generate({
        src: site,
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
      }).done(function () {
        util.log(util.colors.green('Critical CSS Generated!'));
        process.exit();
      });
    }
  );
};
