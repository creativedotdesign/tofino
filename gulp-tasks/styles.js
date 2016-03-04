var manifest    = require('asset-builder')('./assets/manifest.json'),
    merge       = require('merge-stream'),
    browserSync = require('browser-sync').create(),
    fs          = require('fs');

//Compile SCSS to CSS
module.exports = function (gulp, plugins, path, production, minify) {

  return function() {
    var merged = merge();

    manifest.forEachDependency('css', function(dep) {
      dep.globs.forEach(function (path) {
        try {
          fs.accessSync(path);
        } catch (e) {
          plugins.util.log(plugins.util.colors.red('Warning! ' + path + ' does not exist.'));
        }
      });

      merged.add(
        gulp.src(dep.globs)
        .pipe(plugins.sourcemaps.init({loadMaps: true}))
        .pipe(plugins.sass({outputStyle: 'nested'}))
        .pipe(plugins.concat(dep.name))
        .pipe(plugins.if(minify, plugins.cssnano({safe: true}))) //If prod minify
      );
    });
    return merged

    .pipe(plugins.autoprefixer({
      browsers: ['last 2 versions']
    }))
    .pipe(plugins.sourcemaps.write('.'))
    .pipe(gulp.dest(path.dist + '/css'))
    /*.pipe(plugins.if(!production, plugins.notify({
      "subtitle": "Task Complete",
      "message": "Styles task complete",
      "onLast": true
    })))*/
    .pipe(browserSync.stream());
    //.pipe(browserSync.reload);
  }
};
