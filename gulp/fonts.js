var manifest = require('../assets/manifest.json');

// Copy font files from assets to dist
module.exports = function (gulp) {
  'use strict';
  var paths = manifest.paths;
  gulp.task(
    'fonts',
    'Copy the fonts directory to dist.',
    function() {
      gulp.src(paths.fonts + '*')
        .pipe(gulp.dest(paths.dist + 'fonts'));
    }
  );
};
