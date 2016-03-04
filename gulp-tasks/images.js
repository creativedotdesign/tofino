var pngquant = require('imagemin-pngquant');

// Min / Crush images
module.exports = function (gulp, plugins, globs, path, production) {
  return function() {
    var stream = gulp.src(globs.images)
      .pipe(plugins.newer(path.dist + 'img'))
      .pipe(plugins.imagemin({
        progressive: true,
        use: [pngquant()]
      }))
      .pipe(gulp.dest(path.dist + 'img'))
      .pipe(plugins.if(!production, plugins.notify("Images task complete")));
    return stream;
  }
};
