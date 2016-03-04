// Lints scss files
module.exports = function (gulp, plugins, path, production, allowlint) {
  return function() {
    var stream = gulp.src(path.source + 'styles/**/*.scss')
    .pipe(plugins.sassLint())
    .pipe(plugins.sassLint.format())
    .pipe(plugins.if(production, plugins.if(!allowlint, plugins.sassLint.failOnError())));
    return stream;
  }
}
