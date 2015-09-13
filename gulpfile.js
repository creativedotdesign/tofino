var gulp            = require('gulp'),
    manifest        = require('asset-builder')('./assets/manifest.json'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    merge           = require('merge-stream'),
    argv            = require('yargs').argv,
    pngquant        = require('imagemin-pngquant'),
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
gulp.task('styles', ['sass-lint'], function() {
  var merged = merge();
  manifest.forEachDependency('css', function(dep) {
    merged.add(
      gulp.src(dep.globs, {base: 'styles'})
      .pipe(plugins.sass({ style: 'nested' }))
      .on('error', handleError)
        .pipe(plugins.if(!argv.production, plugins.sourcemaps.init())) //If NOT prod use maps
        .pipe(plugins.concat(dep.name))
        .pipe(plugins.autoprefixer({
            browsers: ['last 2 versions']
          }))
        .pipe(plugins.if(!argv.production, plugins.sourcemaps.write('.', {
          includeContent: false,
          sourceRoot: path.styles
        })))
        .pipe(plugins.if(argv.production, plugins.minifyCss())) //If prod minify
        .pipe(plugins.if(argv.production, plugins.rename({suffix: '.min'}))) //If prod add .min
    );
  });
  return merged
  .pipe(gulp.dest(path.dist + '/css'));
});

// Lints scss files
gulp.task('sass-lint', function() {
  return gulp.src(path.source + 'styles/**/*.scss')
    .pipe(plugins.sassLint())
    .pipe(plugins.sassLint.format())
    .pipe(plugins.if(argv.production, plugins.sassLint.failOnError()));
});

// Concatenate & Minify JS
gulp.task('scripts', ['jshint'], function() {
  var merged = merge();
  manifest.forEachDependency('js', function(dep) {
    merged.add(
      gulp.src(dep.globs, {base: 'scripts', merge: true})
        .pipe(plugins.concat(dep.name))
        .pipe(plugins.if(!argv.production, plugins.sourcemaps.write('.', {
          sourceRoot: path.scripts
        })))
        .pipe(plugins.if(argv.production, plugins.uglify())) //If prod minify
        .pipe(plugins.if(argv.production, plugins.rename({suffix: '.min'}))) //If prod add .min
    );
  });
  return merged
  .pipe(gulp.dest(path.dist + '/js'));
});

// Lints configuration JSON and project JS.
gulp.task('jshint', function() {
  return gulp.src(['bower.json', 'gulpfile.js'].concat(project.js))
    .pipe(plugins.jshint())
    .pipe(plugins.jshint.reporter('jshint-stylish'))
    .pipe(plugins.if(argv.production, plugins.jshint.reporter('fail')));
});

// Min / Crush images
gulp.task('images', function () {
  return gulp.src(globs.images)
    .pipe(plugins.imagemin({
      progressive: true,
      use: [pngquant()]
    }))
    .pipe(gulp.dest(path.dist + 'img'));
});

// Convert SVGs to Sprites
gulp.task('svgs', function () {
  return gulp.src(path.svgs + '**/*.svg')
    .pipe(plugins.svgmin())
    .pipe(plugins.svgSprite({ mode: { symbol: true } }))
    .on('error', handleError)
    .pipe(gulp.dest(path.dist + 'svg'));
});

// Deletes the build folder entirely.
gulp.task('clean', require('del').bind(null, [path.dist]));

// Generic build task. Use with '--production' for minified js / css
gulp.task('build', ['clean', 'images', 'svgs', 'styles', 'scripts']);

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

gulp.task('default', ['build']);
