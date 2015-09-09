var gulp            = require('gulp'),
    gulpLoadPlugins = require('gulp-load-plugins'),
    plugins         = gulpLoadPlugins();

function handleError(err) {
  plugins.util.log(plugins.util.colors.red('[ERROR] ' + err.toString()));
  plugins.util.beep();
  this.emit('end');
}
