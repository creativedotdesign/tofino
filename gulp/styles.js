var manifest     = require('../assets/manifest.json'),
    merge        = require('merge-stream'),
    fs           = require('fs'),
    autoprefixer = require('gulp-autoprefixer'),
    concat       = require('gulp-concat'),
    cssnano      = require('gulp-cssnano'),
    gulpif       = require('gulp-if'),
    notify       = require('gulp-notify'),
    sass         = require('gulp-sass'),
    sourcemaps   = require('gulp-sourcemaps'),
    util         = require('gulp-util');

// Compile SCSS to CSS
module.exports = function (gulp, production, browserSync) {
  'use strict';
  var paths = manifest.paths;
  gulp.task(
    'styles',
    'Compile and concat SCSS to CSS with sourcemaps and autoprefixer. Also runs styles:lint.',
    ['styles:lint'],
    function() {
      var merged = merge(),
          outputs = Object.keys(manifest.styles);

      outputs.forEach(function(output) {
        // Define files and add scripts path
        var inputs = manifest['styles'][output].map(
          function(file) {
            return paths.styles + file
          }
        );

        // Check files exist
        inputs.forEach(function (file) {
          try {
            fs.accessSync(file);
          } catch (e) {
            util.log(util.colors.red('Warning! ' + file + ' does not exist.'));
          }
        });

        merged.add(
          gulp.src(inputs)
            .pipe(sourcemaps.init({loadMaps: true}))
            .pipe(sass({outputStyle: 'nested'}))
            .pipe(autoprefixer({browsers: ['last 2 versions']}))
            .pipe(concat(output))
            .pipe(gulpif(production, cssnano({safe: true})))
        );
      });

      return merged
        .pipe(sourcemaps.write('.', {sourceRoot: paths.styles}))
        .pipe(gulp.dest(paths.dist + '/css'))
        .pipe(gulpif(!production, notify({
          "subtitle": "Task Complete",
          "message": "Styles task complete",
          "onLast": true
        })))
        .pipe(browserSync.stream({match: '**/*.css'}));
    }, {
      options: {
        'production': 'Minified without sourcemaps.'
      }
    }
  );
};
