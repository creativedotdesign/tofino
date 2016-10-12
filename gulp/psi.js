var psi  = require('psi');

module.exports = function (gulp, slug, mobile) {
  'use strict';
  gulp.task(
    'psi',
    'Google Page Speed Insights.',
    ['browser-sync', 'ngrok'],
    function(cb) {
      var site = process.env.URL  + '/' + slug;
      console.log('Running Google PSI against URL: ' + site);
      return psi.output(site, {
        nokey: 'true',
        strategy: (mobile ? 'mobile' : 'desktop'),
        // Set to low value as default 70 throws un-catchable error for
        // threshold not met due to local speed issues.
        threshold: 50
      }).then(function () {
        cb();
        process.exit(1);
      });
    }
  );
};
