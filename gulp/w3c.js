var w3cjs = require('w3cjs');

var site = 'http://lambdacreatives.com';

module.exports = function (gulp) {
  'use strict';
  gulp.task(
    'w3c',
    function() {
      w3cjs.validate({
        file: site,
        output: 'json', // Defaults to 'json', other option includes html
        callback: function (res) {
          console.log(res);
            // depending on the output type, res will either be a json object or a html string
        }
      });
  });
};
