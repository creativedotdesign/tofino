var manifest  = require('../assets/manifest.json');

module.exports = function (gulp, browserSync) {
  'use strict';
  var config = manifest.config;
  gulp.task(
    'browser-sync',
    'Help text TBC.',
    function() {
      browserSync.init({
        port: 3000,
        open: false,
        proxy: config.devUrl,
        logLevel: "silent"
      });
    }
  );
};
