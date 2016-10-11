var manifest  = require('../assets/manifest.json'),
    util      = require('gulp-util');

module.exports = function (gulp, browserSync) {
  'use strict';
  var paths  = manifest.paths,
      config = manifest.config;
  gulp.task(
    'watch',
    'Watch SCSS, JS, SVG and PHP files. Uses browserSync via proxy.',
    function() {
      browserSync.init({
        files: ['{lib,templates}/**/*.php', '*.php'],
        proxy: config.devUrl,
        online: false,
        snippetOptions: {
          whitelist: ['/wp-admin/admin-ajax.php'],
          blacklist: ['/wp-admin/**']
        }
      });

      util.log('Watching source files for changes... Press ' + util.colors.cyan('CTRL + C') + ' to stop.');

      gulp.watch(paths.source + 'styles/**/*.scss', ['styles']).on('change', function(file) {
        util.log('SCSS file changed: ' + file.path + '');
      });

      gulp.watch(paths.svgs + '**/*.svg', ['svgs']).on('change', function(file) {
        util.log('SVG file changed: ' + file.path + '');
      });

      gulp.watch(paths.images + '*.*', ['images']).on('change', function(file) {
        util.log('Image file changed: ' + file.path + '');
      });

      gulp.watch(paths.scripts + '**/*.js', ['scripts']).on('change', function(file) {
        util.log('JS file changed: ' + file.path + '');
      });

      gulp.watch(['**/*.php', '!vendor/**/*.php'], ['php:lint']);
    }
  );
};
