var gulp            = require('gulp'),
    manifest        = require('asset-builder')('./assets/manifest.json'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    merge           = require('merge-stream'),
    plugins         = gulpLoadPlugins();

var path  = manifest.paths, //path.source, path.dest etc
    globs = manifest.globs; //globs.images, globs.bower etc

function handleError(err) {
  plugins.util.log(plugins.util.colors.red('[ERROR] ' + err.toString()));
  plugins.util.beep();
  this.emit('end');
}

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
