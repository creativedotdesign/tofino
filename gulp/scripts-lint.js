var manifest = require('../assets/manifest.json'),
    eslint   = require('gulp-eslint'),
    gulpif   = require('gulp-if');

// Lints JS files.
module.exports = function (gulp, production, allowlint) {
  'use strict';
  var paths = manifest.paths;
  gulp.task(
    'scripts:lint',
    'Lints all js files.',
    function() {
      gulp.src(paths.scripts + '/**/*.js')
      .pipe(eslint())
      .pipe(eslint.format())
      .pipe(gulpif(production, gulpif(!allowlint, eslint.failAfterError())))
    }, {
      options: {
        'production': 'Fail on error.',
        'allowlint': 'Do not fail on error, when used with --production.'
      }
    }
  );
};
