var manifest = require('../assets/manifest.json'),
    svgmin   = require('gulp-svgmin');

// Minify SVGS + run sprite task
module.exports = function (gulp) {
  'use strict';
  var paths = manifest.paths;
  gulp.task('svgs',
    'Minify SVG files. Also runs svg:sprite.',
    ['svg:sprite'],
    function() {
      gulp.src(paths.svgs + '*.svg')
        .pipe(svgmin())
        .pipe(gulp.dest(paths.dist + 'svg'));
    }
  );
};
