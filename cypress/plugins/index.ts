import * as dotenv from 'dotenv';
import axios from 'axios';
import { createHtmlReport } from 'axe-html-reporter';

dotenv.config();

export default (on: Cypress.PluginEvents, config: Cypress.PluginConfigOptions) => {
  on('task', {
    processAccessibilityViolations(violations: any[]) {
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

        // Check respose status
        if (response.status !== 200) {
          throw new Error('Error fetching sitemap');
        }

        const xml = response.data;
        const locs = [...xml.matchAll(`<loc>(.|\n)*?</loc>`)].map(([loc]) =>
          loc.replace('<loc>', '').replace('</loc>', '')
        );

        return locs;
      } catch (error) {
        console.error('Error fetching sitemap:', error);
        throw error;
      }
    },
  });

  on('task', {
    log(message: string) {
      console.log(message);

      return null;
    },
    table(message: any) {
      console.table(message);

      return null;
    },
  });

  return config;
};
