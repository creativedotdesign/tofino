var manifest   = require('../assets/manifest.json'),
    merge      = require('merge-stream'),
    fs         = require('fs'),
    gulpif     = require('gulp-if'),
    notify     = require('gulp-notify'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify     = require('gulp-uglify'),
    util       = require('gulp-util'),
    browserify = require('browserify'),
    babelify   = require('babelify'),
    buffer     = require('vinyl-buffer'),
    source     = require('vinyl-source-stream');

// Compile JS
module.exports = function (gulp, production, browserSync) {
  'use strict';
  var paths = manifest.paths;
  gulp.task(
    'scripts',
    'Concat js files with sourcemaps. Also runs scripts:lint.',
    ['scripts:lint'],
    function() {
      var merged  = merge(),
          outputs = Object.keys(manifest.scripts);

      outputs.forEach(function(output) {
        // Define files and add scripts path
        var inputs = manifest['scripts'][output].map(
          function(file) {
            return paths.scripts + file
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

        var bundler = browserify({
          entries: inputs,
          debug: false
        });

        merged.add(
          bundler
            .transform(babelify, {presets: ["es2015"]})
            .bundle()
            .on('error', function (err) { console.error(err); })
            .pipe(source(output))
            .pipe(buffer())
            .pipe(sourcemaps.init({loadMaps: true}))
            .pipe(gulpif(production, uglify({
              compress: {
                drop_console: true
              }
            })))
            .pipe(sourcemaps.write('.', {sourceRoot: paths.scripts}))
            .pipe(gulp.dest(paths.dist + 'js'))
          );
      });

    return merged
      .pipe(gulpif(!production, notify({
        "subtitle": "Task Complete",
        "message": "Scripts task complete",
        "onLast": true
      })))
      .on('finish', browserSync.reload);

    }, {
      options: {
        'production': 'Minified without sourcemaps.'
      }
    }
  );
};
