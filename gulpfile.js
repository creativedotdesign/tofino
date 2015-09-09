var gulp            = require('gulp'),
    manifest        = require('asset-builder')('./assets/manifest.json'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    plugins         = gulpLoadPlugins();

var path  = manifest.paths, //path.source, path.dest etc
    globs = manifest.globs; //globs.images, globs.bower etc

function handleError(err) {
  plugins.util.log(plugins.util.colors.red('[ERROR] ' + err.toString()));
  plugins.util.beep();
  this.emit('end');
}
