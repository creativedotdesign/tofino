var psi = require('psi');

var site = 'http://danimalweb.co.uk';

module.exports = function (gulp, mobile) {
  'use strict';
  gulp.task(
    'psi',
    function() {
      psi.output(site, {
        nokey: 'true',
        strategy: (mobile ? 'mobile' : 'desktop')
      });
    }
  );
};
