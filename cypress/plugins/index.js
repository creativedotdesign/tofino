const { startDevServer } = require('@cypress/vite-dev-server');
require('dotenv').config();
const axios = require('axios');
const { createHtmlReport } = require('axe-html-reporter');

module.exports = (on, config) => {
  on('dev-server:start', (options) => {
    return startDevServer({ options });
  });

  on('task', {
    processAccessibilityViolations(violations) {
      createHtmlReport({
        results: { violations: violations },
        options: {
          outputDir: './cypress/axe-reports',
          reportFileName: 'a11yReport.html',
        },
      });

      return null;
    },
  });

  on('task', {
    async sitemapLocations() {
      try {
        const response = await axios.get(`${process.env.VITE_ASSET_URL}/page-sitemap.xml`, {
          headers: {
            'Content-Type': 'application/xml',
          },
        });

        const xml = response.data;
        const locs = [...xml.matchAll(`<loc>(.|\n)*?</loc>`)].map(([loc]) =>
          loc.replace('<loc>', '').replace('</loc>', '')
        );

        return locs;
      } catch (error) {
        console.error('Error fetching sitemap:', error);
        throw error; // Re-throw the error to ensure Cypress is aware of the failure
      }
    },
  });

  on('task', {
    log(message) {
      console.log(message);

      return null;
    },
    table(message) {
      console.table(message);

      return null;
    },
  });

  config.env.baseUrl = process.env.VITE_ASSET_URL;

  return config;
};
