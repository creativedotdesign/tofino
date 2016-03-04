var manifest    = require('asset-builder')('./assets/manifest.json'),
    merge       = require('merge-stream'),
    browserSync = require('browser-sync').create(),
    fs          = require('fs');

//Compile SCSS to CSS
module.exports = function (gulp, plugins, path, production, minify) {
  return function() {
    var merged = merge();

    manifest.forEachDependency('js', function(dep) {
      dep.globs.forEach(function (path) {
        try {
          fs.accessSync(path);
        } catch (e) {
          plugins.util.log(plugins.util.colors.red('Warning! ' + path + ' does not exist.'));
        }
      });

      merged.add(
        gulp.src(dep.globs, {base: 'scripts', merge: true})
          .pipe(plugins.sourcemaps.init({loadMaps: true}))
          .pipe(plugins.concat(dep.name))
          .pipe(plugins.if(minify, plugins.uglify())) //If prod minify
          .pipe(plugins.sourcemaps.write('.'))
      )
      .pipe(gulp.dest(path.dist + '/js'));
    });

    return merged
    .pipe(plugins.if(!production, plugins.notify({
      "subtitle": "Task Complete",
      "message": "Scripts task complete",
      "onLast": true
    })));

  }
};
