//Lint PHP files using ruleset.xml.
module.exports = function (gulp, plugins, allowlint, production) {
  return function() {
    var stream = gulp.src(['**/*.php', '!vendor/**/*.*', '!tests/**/*.*'])
    .pipe(plugins.phpcs({ // Validate files using PHP Code Sniffer
      bin: 'vendor/bin/phpcs',
        standard: 'ruleset.xml',
        warningSeverity: 0
      }))
    .pipe(plugins.if(!production, plugins.phpcs.reporter('log')))
    .pipe(plugins.if(production, plugins.if(!allowlint, plugins.phpcs.reporter('fail'))));
    return stream;
  }
};
