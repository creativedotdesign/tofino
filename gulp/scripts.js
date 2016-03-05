var manifest   = require('asset-builder')('./assets/manifest.json'),
    merge      = require('merge-stream'),
    fs         = require('fs'),
    concat     = require('gulp-concat'),
    gulpif     = require('gulp-if'),
    notify     = require('gulp-notify'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify     = require('gulp-uglify'),
    util       = require('gulp-util');

// Compile JS
module.exports = function (gulp, production, browserSync) {
  'use strict';
  var paths = manifest.paths;
  gulp.task(
    'scripts',
    'Concat js files with sourcemaps. Also runs scripts:lint.',
    ['scripts:lint'],
    function() {
      var merged = merge();

      manifest.forEachDependency('js', function(dep) {
        dep.globs.forEach(function (path) {
          try {
            fs.accessSync(path);
          } catch (e) {
            util.log(util.colors.red('Warning! ' + path + ' does not exist.'));
          }
        });

        merged.add(
          gulp.src(dep.globs, {base: 'scripts', merge: true})
            .pipe(sourcemaps.init({loadMaps: true}))
            .pipe(concat(dep.name))
            .pipe(gulpif(production, uglify()))
            .pipe(sourcemaps.write('.', {sourceRoot: paths.scripts}))
          )
        .pipe(gulp.dest(paths.dist + '/js'));
      });

    return merged
      .pipe(gulpif(!production, notify({
        "subtitle": "Task Complete",
        "message": "Scripts task complete",
        "onLast": true
      })));
    }, {
      options: {
        'production': 'Minified without sourcemaps.'
      }
    }
  );
};
