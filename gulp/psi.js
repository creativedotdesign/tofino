var psi  = require('psi');

module.exports = function (gulp, mobile) {
  'use strict';
  gulp.task(
    'psi',
    ['browser-sync', 'ngrok'],
    function() {
      var site = process.env.URL;
      console.log('Running Google PSI against URL: ' + site);
      psi.output(site, {
        nokey: 'true',
        strategy: (mobile ? 'mobile' : 'desktop')
      });
      process.exit();
    }
  );
};
