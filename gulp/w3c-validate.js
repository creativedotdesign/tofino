var w3cjs = require('w3cjs'),
    prettyjson = require('prettyjson');

module.exports = function (gulp, util, slug) {
  'use strict';
  gulp.task(
    'w3c-validate',
    'W3C HTML validation',
    ['browser-sync', 'ngrok'],
    function() {
      var site = process.env.URL  + '/' + slug;
      util.log('Running W3C HTML Validation against: ' + util.colors.magenta(site));
      w3cjs.validate({
        file: site,
        output: 'json', // Defaults to 'json', other option includes html
        callback: function (res) {
          if (res.messages.length > 0) {
            console.log(prettyjson.render(res.messages));
          } else {
            util.log(util.colors.green('Well done! No Validation Errors!'));
          }
          process.exit();
        }
      });
  });
};
