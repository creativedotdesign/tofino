var critical = require('critical');

// Critical path css
module.exports = function (gulp, plugins, path, config, stagingUrl) {
  return function() {
    return critical.generate({
      src: plugins.if(stagingUrl, config.stagingUrl, config.devUrl),
      dest: path.dist + '/css/critical.css',
      ignore: ['@font-face',/url\(/],
      // pathPrefix: '/wp-content/themes/tofino/' + path.dist + 'fonts',
      minify: true,
      dimensions: [{
        height: 627,
        width: 370
      }, {
        height: 900,
        width: 1200
      }]
    })
  }
};
