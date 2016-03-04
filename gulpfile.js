var gulp            = require('gulp-help')(require('gulp'), {hideDepsMessage: true}),
    manifest        = require('asset-builder')('./assets/manifest.json'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    merge           = require('merge-stream'),
    argv            = require('yargs').argv,
    //browserSync     = require('browser-sync').create(),
    fs              = require('fs'),
    plugins         = gulpLoadPlugins();

var path       = manifest.paths, //path.source, path.dest etc
    globs      = manifest.globs, //globs.images, globs.bower etc
    config     = manifest.config || {},
    production = argv.production || false,
    minify     = (production ? true : false),
    allowlint  = argv.allowlint || false,
    stagingUrl = argv.stagingUrl || false,
    chill      = argv.chill || false;

var gulpHelp = {
  styles         : 'Compile and concat SCSS to CSS with sourcemaps and autoprefixer. Also runs styles:lint.',
  stylesLint     : 'Lints all SCSS files.',
  stylesCritical : 'Generates the Critical CSS file based on dimentions in the array.',
  scripts        : 'Concat js files with sourcemaps. Also runs scripts:lint.',
  scriptsLint    : 'Lints all js files.',
  images         : 'Compress JPG and PNG files.',
  svgs           : 'Minify SVG files. Also runs svg:sprite.',
  svgSprite      : 'Concat and minify SVG files in to a single SVG sprite file.',
  fonts          : 'Copy the fonts directory to dist.',
  phpLint        : 'Lint theme PHP files based on PSR-2.',
  clean          : 'Deletes the dist directory.',
  build          : 'Main build task. Runs styles, scripts, images, svgs, fonts and php:lint. Does NOT delete dist directory.',
  watch          : 'Watch SCSS, JS, SVG and PHP files. Uses browserSync via proxy.',
  default        : 'Runs the build task. Deleting the dist directory first.'
};

//Override standard gulp.src task
//Use notify and gulp util as error notifcation
var _gulpsrc = gulp.src;
gulp.src = function() {
  return _gulpsrc.apply(gulp, arguments)
    .pipe(plugins.if(!production, plugins.plumber({
      errorHandler: function(err) {
        //plugins.notify.onError("Error: <%= error.message %>")(err);
        plugins.notify.onError("Error: " + err.toString())(err);
        this.emit('end');
      }
    }))); // Seriously?
};

//Compile SCSS to CSS
gulp.task('styles', gulpHelp.styles, ['styles:lint'],
  require('./gulp-tasks/styles.js')(gulp, plugins, path, production, minify), {
  options: {
    'production': 'Minified without sourcemaps.'
  }
});

// Lints scss files
gulp.task('styles:lint', gulpHelp.stylesLint,
  require('./gulp-tasks/styles-lint.js')(gulp, plugins, path, production, allowlint), {
  options: {
    'production': 'Fail on error.',
    'allowlint': 'Do not fail on error, when used with --production.'
  }
});

// Critical path css
gulp.task(
  'styles:critical',
  ['styles', 'styles:lint'],
  require('./gulp-tasks/styles-critical.js')(gulp, plugins, path, config, stagingUrl)
);

// Concatenate & Minify JS
gulp.task('scripts', gulpHelp.scripts, ['scripts:lint'],
  require('./gulp-tasks/scripts.js')(gulp, plugins, path, production, minify), {
  options: {
    'production': 'Minified without sourcemaps.'
  }
});

// Lints project JS.
gulp.task('scripts:lint', gulpHelp.scriptsLint,
  require('./gulp-tasks/php-lint.js')(gulp, plugins, path, production, allowlint), {
  options: {
    'production': 'Fail on error.',
    'allowlint': 'Do not fail on error, when used with --production.'
  }
});

// Min / Crush images
gulp.task('images', gulpHelp.images, require('./gulp-tasks/images')(gulp, plugins, globs, path, production));

//Minify SVGS + run sprite task
gulp.task('svgs', gulpHelp.svgs, ['svg:sprite'], require('./gulp-tasks/svgs')(gulp, plugins, path));

// Convert SVGs to Sprites
gulp.task('svg:sprite', gulpHelp.svgSprite, require('./gulp-tasks/svg-sprite')(gulp, plugins, path, production));

//Copy font files from assets to dist
gulp.task('fonts', gulpHelp.fonts, require('./gulp-tasks/fonts')(gulp, plugins, path));

//Lint PHP files using ruleset.xml.
gulp.task('php:lint', gulpHelp.phpLint,
    require('./gulp-tasks/php-lint.js')(gulp, plugins, allowlint, production), {
    options: {
      'production': 'Fail on error.',
      'allowlint': 'Do not fail on error, when used with --production.'
    }
  }
);

// Deletes the build folder entirely.
gulp.task('clean', gulpHelp.clean, require('del').bind(null, [path.dist]));

// Generic build task. Use with '--production' for minified js / css
gulp.task('build', gulpHelp.build, ['images', 'svgs', 'styles', 'scripts', 'fonts', 'php:lint']);

// Watch Files For Changes
gulp.task('watch', gulpHelp.watch,
  require('./gulp-tasks/watch.js')(gulp, plugins, path, config, chill), {
  options: {
    'chill': 'Do not pass clicks, forms or scroll to other browsers.'
  }
});

gulp.task('default', gulpHelp.default, ['clean'], function() {
  gulp.start('build');
});
