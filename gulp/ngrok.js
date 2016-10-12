var ngrok    = require('ngrok');

module.exports = function (gulp) {
  'use strict';
  gulp.task(
    'ngrok',
    function(cb) {
      return ngrok.connect(3000, function (err, url) {
        var site = url;
        console.log('serving your tunnel from: ' + site);
        cb();
      })
    }
  );
};
