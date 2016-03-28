var manifest  = require('../assets/manifest.json'),
    gulpif    = require('gulp-if'),
    notify    = require('gulp-notify'),
    svgmin    = require('gulp-svgmin'),
    svgSprite = require('gulp-svg-sprite');

// Create SVG sprite file
module.exports = function (gulp, production) {
  'use strict';
  var paths = manifest.paths;
  gulp.task(
    'svg:sprite',
    'Concat and minify SVG files in to a single SVG sprite file.',
    function() {
      gulp.src(paths.svgs + 'sprites/*.svg')
        .pipe(svgmin())
        .pipe(svgSprite({ mode: { symbol: { dest: '.' } } })) //Sets default path (svg) and stops the folders nesting.
        .pipe(gulp.dest(paths.dist))
        .pipe(gulpif(!production, notify("SVG task complete")));
    }
  );
};
