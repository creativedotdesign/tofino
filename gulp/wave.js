var AccessSniff = require('access-sniff');

module.exports = function (gulp) {
  'use strict';
  gulp.task(
    'wave',
    ['browser-sync', 'ngrok'],
    function() {
      var site = process.env.URL;
      console.log('Running WAVE accessibility against: ' + site);
      AccessSniff
        .default(site, {
          accessibilityLevel: 'WCAG2A',
          reportLevels: {
            notice: false,
            warning: false,
            error: true
          }
        });
      process.exit();
    }
  );
};
