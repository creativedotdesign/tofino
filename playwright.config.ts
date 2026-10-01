import { defineConfig, devices } from '@playwright/test';
import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';

/**
 * The suite can be run from this theme or from a child theme
 * (`playwright test --config ../tofino/playwright.config.ts`). The generic
 * specs always come from here; manifests, reports and .env come from the
 * working directory, falling back to this theme's .env for the site URL.
 */
const themeDir = path.dirname(fileURLToPath(import.meta.url));
const siteDir = process.cwd();

dotenv.config();
dotenv.config({ path: path.join(themeDir, '.env') });

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
// import dotenv from 'dotenv';
// import path from 'path';
// dotenv.config({ path: path.resolve(__dirname, '.env') });

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: path.join(themeDir, 'tests'),
  outputDir: path.join(siteDir, 'tests/output/artifacts'),
  /* Run tests in files in parallel */
  fullyParallel: true,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,
  /* Maximum time one test can run for */
  timeout: 60000,
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  // reporter: 'html',

  reporter: [
    // Terminal progress + failure detail; the html reporter alone prints nothing.
    ['line'],
    ['html', { open: 'never', outputFolder: path.join(siteDir, 'tests/output/playwright') }],
    ['./tests/support/reporters/html-validation-reporter.ts'],
    ['./tests/support/reporters/overflow-detection-reporter.ts'],
    ['./tests/support/reporters/dom-lint-reporter.ts'],
  ],

  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.VITE_ASSET_URL,

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',

    /* Navigation timeout */
    navigationTimeout: 60000,

    /* Action timeout */
    actionTimeout: 10000,
  },

  /* Configure projects for major browsers */
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
      // The smoke specs exercise this theme's own header/alert markup; a child
      // theme running the suite has its own header, so skip them there.
      testIgnore: siteDir === themeDir ? [] : [/smoke\//],
    },

    // Specs a module or feature keeps in its own folder
    // (modules/<slug>/a11y.spec.ts, features/<slug>/a11y.spec.ts) for
    // assertions beyond the generic manifest contract.
    {
      name: 'theme',
      testDir: siteDir,
      testMatch: /(modules|features)\/[^/]+\/a11y\.spec\.ts$/,
      use: { ...devices['Desktop Chrome'] },
    },

    // WebKit is opt-in (`npm run test:webkit`): ADA audits are done with
    // VoiceOver in Safari, so behavioural specs (overlays, carousels, focus)
    // should also pass there. The sitemap-wide audits stay Chromium-only.
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
      testIgnore: /audits\/(accessibility|html|overflow|dom-lint)\.spec\.ts/,
    },

    // {
    //   name: 'firefox',
    //   use: { ...devices['Desktop Firefox'] },
    // },

    /* Test against mobile viewports. */
    // {
    //   name: 'Mobile Chrome',
    //   use: { ...devices['Pixel 5'] },
    // },
    // {
    //   name: 'Mobile Safari',
    //   use: { ...devices['iPhone 12'] },
    // },

    /* Test against branded browsers. */
    // {
    //   name: 'Microsoft Edge',
    //   use: { ...devices['Desktop Edge'], channel: 'msedge' },
    // },
    // {
    //   name: 'Google Chrome',
    //   use: { ...devices['Desktop Chrome'], channel: 'chrome' },
    // },
  ],

  /* Run your local dev server before starting the tests */
  // webServer: {
  //   command: 'npm run start',
  //   url: 'http://127.0.0.1:3000',
  //   reuseExistingServer: !process.env.CI,
  // },
});
