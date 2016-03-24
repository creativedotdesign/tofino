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
      var merged = merge();

      manifest.forEachDependency('css', function(dep) {
        dep.globs.forEach(function (path) {
          try {
            fs.accessSync(path);
          } catch (e) {
            util.log(util.colors.red('Warning! ' + path + ' does not exist.'));
          }
        });

        merged.add(
          gulp.src(dep.globs)
            .pipe(sourcemaps.init({loadMaps: true}))
            .pipe(sass({outputStyle: 'nested'}))
            .pipe(concat(dep.name))
            .pipe(gulpif(production, cssnano({safe: true})))
        );
      });

      return merged
        .pipe(autoprefixer({browsers: ['last 2 versions']}))
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
