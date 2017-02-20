var gulpif = require('gulp-if'),
    phpcs  = require('gulp-phpcs');

// Lint PHP files using ruleset.xml
module.exports = function (gulp, allowlint, production) {
  'use strict';
  gulp.task(
    'php:lint',
    'Lint theme PHP files based on PSR-2.',
    function() {
      return gulp
        .src(['**/*.php', '!vendor/**/*.*', '!node_modules/**/*.*'])
        .pipe(phpcs({ // Validate files using PHP Code Sniffer
          bin: 'vendor/bin/phpcs',
            standard: 'ruleset.xml',
            warningSeverity: 0
          }))
        .pipe(gulpif(!production, phpcs.reporter('log')))
        .pipe(gulpif(production, gulpif(!allowlint, phpcs.reporter('fail'))));
    }, {
      options: {
        'production': 'Fail on error.',
        'allowlint': 'Do not fail on error, when used with --production.'
      }
    }
  );
};
