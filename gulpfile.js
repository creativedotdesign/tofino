var gulp            = require('gulp'),
    manifest        = require('asset-builder')('./assets/manifest.json'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    merge           = require('merge-stream'),
    plugins         = gulpLoadPlugins();

var path  = manifest.paths, //path.source, path.dest etc
    globs = manifest.globs, //globs.images, globs.bower etc
    project = manifest.getProjectGlobs();

function handleError(err) {
  plugins.util.log(plugins.util.colors.red('[ERROR] ' + err.toString()));
  plugins.util.beep();
  this.emit('end');
}

//Compile SCSS to CSS
gulp.task('styles', function() {
  var merged = merge();

  manifest.forEachDependency('css', function(dep) {
   merged.add(
     gulp.src(dep.globs, {base: 'styles'})
       .pipe(plugins.sass({ style: 'expanded' }))
       .pipe(plugins.concat(dep.name))
        //.pipe(plugins.autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
       .pipe(plugins.minifyCss())
   );
  });

  return merged
    .pipe(gulp.dest(path.dist));
});

// Concatenate & Minify JS
gulp.task('scripts', function() {
  var merged = merge();
  manifest.forEachDependency('js', function(dep) {
    merged.add(
      gulp.src(dep.globs, {base: 'scripts', merge: true})
        .pipe(plugins.concat(dep.name))
        .pipe(plugins.uglify())
    );
  });
  return merged
  .pipe(gulp.dest(path.dist));
});

// Min / Crush images
gulp.task('images', function () {
  return gulp.src(globs.images)
    .pipe(plugins.imagemin({ progressive: true }))
    .pipe(gulp.dest(path.dist + 'images'));
});

// Convert SVGs to Sprites
gulp.task('svgs', function () {
  return gulp.src(globs.svgs)
    .pipe(plugins.svgmin())
    .pipe(plugins.svgSprites({
      mode: "symbols",
      svgId: "svg-%f",
      preview: false,
      svg: {
        symbols: "svg-sprite.svg"
      }
    }))
    .pipe(gulp.dest(path.dist));
});

// Watch Files For Changes
gulp.task('watch', function() {
  plugins.livereload.listen(35729, function(err) {
      if(err) return plugins.util.log(err);
  });

  plugins.util.log('Watching source files for changes... Press ' + plugins.util.colors.cyan('CTRL + C') + ' to stop.');

  gulp.watch(path.source + 'styles/**/*.scss', ['styles']).on('change', function(file) {
      plugins.util.log('File Changed: ' + file.path + '');
  });

  gulp.watch(path.source + 'scripts/*.js', ['scripts']).on('change', function(file) {
      plugins.util.log('File Changed: ' + file.path + '');
  });

  gulp.watch('*.php').on('change', function(file) {
      plugins.util.log('File Changed: ' + file.path + '');
      plugins.livereload.changed(file.path);
  });
});
