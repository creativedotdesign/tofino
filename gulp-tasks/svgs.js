//Minify SVGS + run sprite task
module.exports = function (gulp, plugins, path) {
  return function() {
    var stream = gulp.src(path.svgs + '*.svg')
      .pipe(plugins.svgmin())
      .pipe(gulp.dest(path.dist + 'svg'));
    return stream;
  }
};
