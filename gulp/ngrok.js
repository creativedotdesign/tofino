var ngrok = require('ngrok');

module.exports = function (gulp, util) {
  'use strict';
  gulp.task(
    'ngrok',
    ['browser-sync'],
    function(cb) {
      return ngrok.connect(3000, function (err, url) {
        if (err !== null) {
          util.log(util.colors.red(err));
        }
        util.log('Serving your tunnel from: ' + util.colors.magenta(url));
        process.env.URL = url; // Set env vairbale, global shared between tasks
        cb();
      })
    }
  );
};
