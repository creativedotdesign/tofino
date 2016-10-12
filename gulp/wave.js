var AccessSniff = require('access-sniff');

var site = 'http://lambdacreatives.com';

module.exports = function (gulp) {
  'use strict';
  gulp.task(
    'wave',
    function() {
      AccessSniff
      .default(site, {
        accessibilityLevel: 'WCAG2A',
        reportLevels: {
          notice: false,
          warning: false,
          error: true
        }
      })
      .then(function(report) {
        console.log(report);
        //AccessSniff.report(report, reportOptions);
      });
    }
  );
};
