var ngrok = require('ngrok');

module.exports = function (gulp) {
  'use strict';
  gulp.task(
    'ngrok',
    ['browser-sync'],
    function(cb) {
      return ngrok.connect(3000, function (err, url) {
        if (err !== null) {
          console.log(err);
        }
        console.log('Serving your tunnel from: ' + url);
        process.env.URL = url; // Set env vairbale, global shared between tasks
        cb();
      })
    }
  );
};
