var psi  = require('psi');

module.exports = function (gulp, util, slug, mobile) {
  'use strict';
  gulp.task(
    'psi',
    'Google Page Speed Insights.',
    ['browser-sync', 'ngrok'],
    function(cb) {
      var site = process.env.URL  + '/' + slug;
      util.log('Running Google PSI against URL: ' + util.colors.magenta(site));
      return psi.output(site, {
        nokey: 'true',
        strategy: (mobile ? 'mobile' : 'desktop'),
        threshold: 70 // Default threshold
      }).catch(function (err) {
        util.log(util.colors.red(err));
      }).then(function () {
        cb();
        process.exit(1);
      });
    }
  );
};
