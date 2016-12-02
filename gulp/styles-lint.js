var manifest  = require('../assets/manifest.json'),
    gulpif    = require('gulp-if'),
    stylelint = require('gulp-stylelint');

// Lints scss files
module.exports = function (gulp, production, allowlint) {
  'use strict';
  var paths = manifest.paths;
  gulp.task('styles:lint',
    'Lints all SCSS files.',
    function() {
      return gulp
        .src(paths.styles + '**/*.scss')
        .pipe(stylelint({
          syntax: 'scss',
          reporters: [
            {formatter: 'string', console: true}
          ]
        }))
        .pipe(gulpif(production, gulpif(allowlint, stylelint({
          failAfterError: false
        }))));
    }, {
      options: {
        'production': 'Fail on error.',
        'allowlint': 'Do not fail on error, when used with --production.'
      }
    }
  );
};
