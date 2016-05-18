var phpmd  = require('gulp-phpmd');

// Validate files using PHP Mess Dectector
module.exports = function (gulp) {
  'use strict';
  gulp.task(
    'php:md',
    'PHP mess dectector.',
    function() {
      gulp.src(['**/*.php', '!vendor/**/*.*'])
        .pipe(phpmd({
          bin: 'vendor/bin/phpmd',
          format: 'text',
          ruleset: 'codesize,unusedcode,naming'
        }))
        .on('error', console.error)
    }, {
      options: {
        'production': 'Fail on error.',
        'allowlint': 'Do not fail on error, when used with --production.'
      }
    }
  );
};
