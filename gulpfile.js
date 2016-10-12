var gulp        = require('gulp-help')(require('gulp'), {hideDepsMessage: true}),
    util        = require('gulp-util'),
    gulpif      = require('gulp-if'),
    plumber     = require('gulp-plumber'),
    notify      = require('gulp-notify'),
    browserSync = require('browser-sync').create();

var production = util.env.production || false,
    allowlint  = util.env.allowlint || false,
    stagingUrl = util.env.stagingUrl || false,
    mobile     = util.env.desktop || false;

// Override standard gulp.src task to use plumber
var _gulpsrc = gulp.src;
gulp.src = function() {
  return _gulpsrc.apply(gulp, arguments)
    .pipe(gulpif(!production, plumber({
      errorHandler: function(err) {
        notify.onError("Error: " + err.toString())(err);
        this.emit('end');
      }
    })));
};

// Compile SCSS to CSS
require('./gulp/styles')(gulp, production, browserSync);

// Lints scss files
require('./gulp/styles-lint')(gulp, production, allowlint);

// Critical path css
require('./gulp/styles-critical')(gulp, stagingUrl);

// Concatenate & Minify JS
require('./gulp/scripts')(gulp, production, browserSync);

// Lint js files
require('./gulp/scripts-lint')(gulp, production, allowlint);

// Min / Crush images
require('./gulp/images')(gulp, production);

// Minify SVGS + run sprite task
require('./gulp/svgs')(gulp);

// Create SVG sprite file
require('./gulp/svg-sprite')(gulp, production);

// Copy font files from assets to dist
require('./gulp/fonts')(gulp);

// Lint PHP files using ruleset.xml
require('./gulp/php-lint')(gulp, allowlint, production);

// Validate files using PHP Mess Dectector
require('./gulp/php-md')(gulp);

// Deletes the build folder entirely.
require('./gulp/clean')(gulp);

require('./gulp/ngrok')(gulp);

require('./gulp/psi')(gulp, mobile);

require('./gulp/w3c')(gulp);

require('./gulp/wave')(gulp);

// Watch Files For Changes
require('./gulp/watch')(gulp, browserSync);

// Generic build task. Use with '--production' for production builds
gulp.task('build',
  'Main build task. Runs styles, scripts, images, svgs, fonts and php:lint. Does not delete dist directory.', [
    'images',
    'svgs',
    'styles',
    'scripts',
    'fonts',
    'php:lint'
  ]
);

gulp.task('default',
  'Runs the build task. Deleting the dist directory first.',
  ['clean'],
  function() {
    gulp.start('build');
  }
);
