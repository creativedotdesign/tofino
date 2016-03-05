var manifest = require('asset-builder')('./assets/manifest.json'),
    gulpif   = require('gulp-if'),
    sassLint = require('gulp-sass-lint');

// Lints scss files
module.exports = function (gulp, production, allowlint) {
  'use strict';
  var paths = manifest.paths;
  gulp.task('styles:lint',
    'Lints all SCSS files.',
    function() {
      gulp.src(paths.source + 'styles/**/*.scss')
        .pipe(sassLint())
        .pipe(sassLint.format())
        .pipe(gulpif(production, gulpif(allowlint, sassLint.failOnError())));
    }, {
      options: {
        'production': 'Fail on error.',
        'allowlint': 'Do not fail on error, when used with --production.'
      }
    }
  );
};
