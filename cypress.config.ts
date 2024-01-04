import { defineConfig } from 'cypress';
import * as dotenv from 'dotenv';
import setupPlugins from './cypress/plugins/index';
import htmlvalidate from 'cypress-html-validate/plugin';

dotenv.config();

export default defineConfig({
  video: false,
  enableScreenshots: true,
  axeIgnoreContrast: true,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      htmlvalidate.install(on);

      setupPlugins(on, config);
    },
    baseUrl: process.env.VITE_ASSET_URL,
    specPattern: 'cypress/e2e/**/*{spec,cy}.{js,ts}',
  },
  // env: {
  //   baseUrl: process.env.VITE_ASSET_URL,
  // },
  component: {
    setupNodeEvents(on, config) {},
    specPattern: 'src/**/*{spec,cy}.{js,ts}',
  },
});
