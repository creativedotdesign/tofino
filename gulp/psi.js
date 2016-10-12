var psi  = require('psi');

module.exports = function (gulp, slug, mobile) {
  'use strict';
  gulp.task(
    'psi',
    'Google Page Speed Insights.',
    ['browser-sync', 'ngrok'],
    function() {
      var site = process.env.URL  + '/' + slug;
      console.log('Running Google PSI against URL: ' + site);
      psi.output(site, {
        nokey: 'true',
        strategy: (mobile ? 'mobile' : 'desktop')
      }).then(function() {
        process.exit();
      });
    }
  );
};
