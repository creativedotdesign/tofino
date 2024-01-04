import { defineConfig } from 'cypress';
import * as dotenv from 'dotenv';
import setupPlugins from './cypress/plugins/index';

dotenv.config();

export default defineConfig({
  video: false,
  enableScreenshots: true,
  axeIgnoreContrast: true,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
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
