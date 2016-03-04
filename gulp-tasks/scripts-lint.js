// Lints project JS.
module.exports = function (gulp, plugins, path, production, allowlint) {
  return function() {
    var stream = gulp.src(path.scripts + '/**/*.js')
    .pipe(plugins.eslint())
    .pipe(plugins.eslint.format())
    .pipe(plugins.if(production, plugins.if(!allowlint, plugins.eslint.failAfterError())))
    return stream;
  }
}
