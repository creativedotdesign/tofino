const { defineConfig } = require('cypress');

module.exports = defineConfig({
  video: false,
  enableScreenshots: true,
  axeIgnoreContrast: true,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config);
    },
    specPattern: 'cypress/e2e/**/*{spec,cy}.{js,ts}',
  },
  component: {
    setupNodeEvents(on, config) {},
    specPattern: 'src/**/*{spec,cy}.{js,ts}',
  },
});
