// Convert SVGs to Sprites
module.exports = function (gulp, plugins, path, production) {
  return function() {
    var stream = gulp.src(path.svgs + 'sprites/*.svg')
      .pipe(plugins.svgmin())
      .pipe(plugins.svgSprite({
        mode: {
          symbol: {
            dest: '.' //Sets default path (svg) and stopped the folders nesting.
          }
        }
      }))
      .pipe(gulp.dest(path.dist))
      .pipe(plugins.if(!production, plugins.notify("SVG task complete")));
    return stream;
  }
};
