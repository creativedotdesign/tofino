var w3cjs = require('w3cjs');

module.exports = function (gulp) {
  'use strict';
  gulp.task(
    'w3c-validate',
    ['browser-sync', 'ngrok'],
    function() {
      var site  = process.env.URL;
      console.log('Running W3C HTML Validation against: ' + site);
      w3cjs.validate({
        file: site,
        output: 'json', // Defaults to 'json', other option includes html
        callback: function (res) {
          console.log(res);
          process.exit();
        }
      });
  });
};
