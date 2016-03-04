//Copy font files from assets to dist
module.exports = function (gulp, plugins, path) {
  return function() {
    var stream = gulp.src(path.fonts + '*')
      .pipe(gulp.dest(path.dist + 'fonts'));
    return stream;
  }
};
