var browserSync = require('browser-sync').create();

module.exports = function (gulp, plugins, path, config, chill) {
  return function() {
    var ghost = false;

    //if gulp watch --chill then BrowserSync will not pass clicks, forms, scroll to other browsers.
    if(chill) {
      ghost = true;
    }

    browserSync.init({
      files: ['{lib,templates}/**/*.php', '*.php'],
      logLevel: "debug",
      proxy: config.devUrl,
      ghostMode: ghost,
      snippetOptions: {
        whitelist: ['/wp-admin/admin-ajax.php'],
        blacklist: ['/wp-admin/**']
      }
    });

    plugins.util.log('Watching source files for changes... Press ' + plugins.util.colors.cyan('CTRL + C') + ' to stop.');

    gulp.watch(path.source + 'styles/**/*.scss', ['styles']).on('change', function(file) {
      plugins.util.log('SCSS file changed: ' + file.path + ''),
      browserSync.reload({stream: true});
    });

    gulp.watch(path.svgs + '**/*.svg', ['svgs']).on('change', function(file) {
      plugins.util.log('SVG file changed: ' + file.path + '');
    });

    gulp.watch(path.images + '*.*', ['images']).on('change', function(file) {
      plugins.util.log('Image file changed: ' + file.path + '');
    });

    gulp.watch(path.scripts + '*.js', ['scripts']).on('change', function(file) {
      plugins.util.log('JS file changed: ' + file.path + '');
    });

    gulp.watch(['**/*.php', '!vendor/**/*.php'], ['php:lint']); //No need to log the filename. BrowserSync does this.
  }
};
