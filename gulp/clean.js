var manifest = require('../assets/manifest.json'),
    del      = require('del');

module.exports = function (gulp) {
  'use strict';
  var paths = manifest.paths;
  gulp.task(
    'clean',
    'Deletes the dist directory.',
    function() {
      return del([paths.dist]);
    }
  );
};
