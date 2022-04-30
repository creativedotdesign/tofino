const { startDevServer } = require('@cypress/vite-dev-server');
require('dotenv').config();

module.exports = (on, config) => {
  on('dev-server:start', (options) => {
    return startDevServer({ options });
  });

  config.env.baseUrl = process.env.VITE_ASSET_URL;

  return config;
};
