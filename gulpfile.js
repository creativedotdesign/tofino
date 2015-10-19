var gulp            = require('gulp-help')(require('gulp'), {hideDepsMessage: true}),
    manifest        = require('asset-builder')('./assets/manifest.json'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    merge           = require('merge-stream'),
    argv            = require('yargs').argv,
    pngquant        = require('imagemin-pngquant'),
    browserSync     = require('browser-sync').create(),
    plugins         = gulpLoadPlugins();

var path    = manifest.paths, //path.source, path.dest etc
    globs   = manifest.globs, //globs.images, globs.bower etc
    config  = manifest.config || {};

var gulpHelp = {
  styles: 'Compile and concat SCSS to CSS with sourcemaps and autoprefixer. Also runs styles:lint.',
  stylesLint: 'Lints all SCSS files.',
  scripts: 'Contact js files with sourcemaps. Also runs scripts:lint.',
  scriptsLint: 'Lints all js files.',
  images: 'Compress JPG and PNG files.',
  svgs: 'Minify SVG files. Also runs svg:sprite.',
  svgSprite: 'Concat and minify SVG files in to a single SVG sprite file.',
  fonts: 'Copy the fonts directory to dist.',
  phpLint: 'Lint theme PHP files based on PSR-2.',
  phpFix: 'Fix all fixable PHP lint errors. This will update existing files.',
  clean: 'Deletes the dist directory.',
  build: 'Main build task. Runs clean, styles, scripts, images, svgs, fonts and php:lint.',
  watch: 'Watch SCSS, JS, SVG and PHP files. Uses browserSync via proxy.',
  default: 'Runs the build task.'
};

//Override standard gulp.src task
//Use notify and gulp util as error notifcation
var _gulpsrc = gulp.src;
gulp.src = function() {
  return _gulpsrc.apply(gulp, arguments)
    .pipe(plugins.plumber({
      errorHandler: function(err) {
        plugins.notify.onError({
          title:    "Gulp Error",
          message:  "Error: <%= error.message %>",
          sound:    "Bottle"
        })(err);
        plugins.util.log(plugins.util.colors.red('[ERROR] ' + err.toString()));
        this.emit('end');
      }
    }));
};

//Compile SCSS to CSS
gulp.task('styles', gulpHelp.styles, ['styles:lint', 'clean'], function() {
  var merged = merge();

  manifest.forEachDependency('css', function(dep) {
    merged.add(
      gulp.src(dep.globs)
      .pipe(plugins.if(!argv.production, plugins.sourcemaps.init())) //If NOT prod use maps
      .pipe(plugins.sass({ style: 'nested' }))
      .pipe(plugins.concat(dep.name))
      .pipe(plugins.autoprefixer({
          browsers: ['last 2 versions']
        }))
      .pipe(plugins.if(argv.production, plugins.minifyCss())) //If prod minify
    );
  });
  return merged

  .pipe(plugins.if(!argv.production, plugins.sourcemaps.write('.')))
  .pipe(gulp.dest(path.dist + '/css'))
  .pipe(browserSync.reload({stream:true}))
  .pipe(plugins.if(!argv.production, plugins.notify({
      "title": "Gulp Notification",
      "subtitle": "Task Complete",
      "message": "Styles task complete",
      "sound": "Frog",
      "onLast": true,
      "wait": true
    })));
}, {
  options: {
    'production': 'Minified without sourcemaps.'
  }
});

// Lints scss files
gulp.task('styles:lint', gulpHelp.stylesLint, function() {
  return gulp.src(path.source + 'styles/**/*.scss')
    .pipe(plugins.sassLint())
    .pipe(plugins.sassLint.format())
    .pipe(plugins.if(argv.production, plugins.sassLint.failOnError()));
}, {
  options: {
    'production': 'Fail on error.'
  }
});

// Concatenate & Minify JS
gulp.task('scripts', gulpHelp.scripts, ['scripts:lint', 'clean'], function() {
  var merged = merge();

  manifest.forEachDependency('js', function(dep) {
    merged.add(
      gulp.src(dep.globs, {base: 'scripts', merge: true})
        .pipe(plugins.if(!argv.production, plugins.sourcemaps.init())) //If NOT prod use maps
        .pipe(plugins.concat(dep.name))
        .pipe(plugins.if(argv.production, plugins.uglify())) //If prod minify
        .pipe(plugins.if(!argv.production, plugins.sourcemaps.write('.', {
          sourceRoot: path.scripts
        })))
    )
    .pipe(gulp.dest(path.dist + '/js'));
  });

  return merged
  .pipe(plugins.if(!argv.production, plugins.notify({
      "title": "Gulp Notification",
      "subtitle": "Task Complete",
      "message": "Scripts task complete",
      "sound": "Frog",
      "onLast": true,
      "wait": true
    })));
}, {
  options: {
    'production': 'Minified without sourcemaps.'
  }
});

// Lints configuration JSON and project JS.
gulp.task('scripts:lint', gulpHelp.scriptsLint, function() {
  return gulp.src(['bower.json', 'gulpfile.js', path.scripts + '/**/*.js'])
    .pipe(plugins.jshint())
    .pipe(plugins.jshint.reporter('jshint-stylish'))
    .pipe(plugins.if(argv.production, plugins.jshint.reporter('fail')));
}, {
  options: {
    'production': 'Fail on error.'
  }
});

// Min / Crush images
gulp.task('images', gulpHelp.images, ['clean'], function () {
  return gulp.src(globs.images)
    .pipe(plugins.imagemin({
      progressive: true,
      use: [pngquant()]
    }))
    .pipe(gulp.dest(path.dist + 'img'))
    .pipe(plugins.if(!argv.production, plugins.notify("Images task complete")));
});

//Minify SVGS + run sprite task
gulp.task('svgs', gulpHelp.svgs, ['svg:sprite'], function () {
  return gulp.src(path.svgs + '*.svg')
    .pipe(plugins.svgmin())
    .pipe(gulp.dest(path.dist + 'svg'));
});

// Convert SVGs to Sprites
gulp.task('svg:sprite', gulpHelp.svgSprite, ['clean'], function () {
  return gulp.src(path.svgs + 'sprites/*.svg')
    .pipe(plugins.svgmin())
    .pipe(plugins.svgSprite({
      mode: {
        symbol: {
          dest: '.' //Sets default path (svg) and stopped the folders nesting.
        }
      }
    }))
    .pipe(gulp.dest(path.dist))
    .pipe(plugins.if(!argv.production, plugins.notify("SVG task complete")));
});

//Copy font files from assets to dist
gulp.task('fonts', gulpHelp.fonts, ['clean'], function () {
  return gulp.src(path.fonts + '*')
    .pipe(gulp.dest(path.dist + 'fonts'));
});

//Lint PHP files using ruleset.xml.
gulp.task('php:lint', gulpHelp.phpLint, function () {
  return gulp.src(['**/*.php', '!vendor/**/*.*', '!tests/**/*.*'])
    .pipe(plugins.phpcs({ // Validate files using PHP Code Sniffer
      bin: 'vendor/bin/phpcs',
        standard: 'ruleset.xml',
        warningSeverity: 0
      }))
    .pipe(plugins.phpcs.reporter('log'));
});

//Fix PHP based on ruleset.xml. This will update existing PHP files
gulp.task('php:fix', gulpHelp.phpFix, function () {
  return gulp.src(['**/*.php', '!vendor/**/*.*', '!tests/**/*.*'])
    .pipe(plugins.phpcbf({
      bin: 'vendor/bin/phpcbf',
      standard: 'ruleset.xml',
      warningSeverity: 0
    }))
    .on('error', plugins.util.log)
    .pipe(gulp.dest('.'));
});

// Deletes the build folder entirely.
gulp.task('clean', gulpHelp.clean, require('del').bind(null, [path.dist]));

// Generic build task. Use with '--production' for minified js / css
gulp.task('build', gulpHelp.build, ['clean', 'images', 'svgs', 'styles', 'scripts', 'fonts', 'php:lint']);

// Watch Files For Changes
gulp.task('watch', gulpHelp.watch, function() {

  var ghost = false;

  //if gulp watch --chill then BrowserSync will not pass clicks, forms, scroll to other browsers.
  if(argv.chill) {
    ghost = true;
  }

  browserSync.init({
    files: ['{lib,templates}/**/*.php', '*.php'],
    proxy: config.devUrl,
    ghostMode: ghost,
    snippetOptions: {
      whitelist: ['/wp-admin/admin-ajax.php'],
      blacklist: ['/wp-admin/**']
    }
  });

  plugins.util.log('Watching source files for changes... Press ' + plugins.util.colors.cyan('CTRL + C') + ' to stop.');

  gulp.watch(path.source + 'styles/**/*.scss', ['styles']).on('change', function(file) {
    plugins.util.log('File Changed: ' + file.path + '');
  });

  gulp.watch(path.source + 'svgs/**/*.svg', ['svgs']).on('change', function(file) {
    plugins.util.log('File Changed: ' + file.path + '');
  });

  gulp.watch(path.source + 'scripts/*.js', ['scripts']).on('change', function(file) {
    plugins.util.log('File Changed: ' + file.path + '');
  });

}, {
  options: {
    'chill': 'Do not pass clicks, forms or scroll to other browsers.'
  }
});

gulp.task('default', gulpHelp.default, ['build']);
