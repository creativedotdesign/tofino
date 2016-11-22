var AccessSniff = require('access-sniff');

module.exports = function (gulp, util, slug) {
  'use strict';
  gulp.task(
    'wave',
    'WAVE Accessibility checking.',
    ['browser-sync', 'ngrok'],
    function() {
      var site = process.env.URL  + '/' + slug;
      util.log('Running WAVE accessibility against: ' + util.colors.magenta(site));
      AccessSniff
        .default(site, {
          accessibilityLevel: 'WCAG2A',
          reportType: 'json',
          domElement: true,
          reportLevels: {
            notice: false,
            warning: true,
            error: true
          }
        }).done(function() {
          process.exit();
        });
    }
  );
};
