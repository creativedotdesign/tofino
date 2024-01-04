import 'cypress-axe';
import resolveConfig from 'tailwindcss/resolveConfig';
import tailwindConfig from '../../tailwind.config';
import { screenshotViolations, cypressLog, terminalLog } from '../support/helpers/index';

// Type for viewport sizes
type ViewportSize = {
  name: string;
  width: number;
  height: number;
};

// Type for violation callback
type ViolationCallback = (violations: any[]) => void; // Replace 'any' with the actual type of violations if known

const fullConfig = resolveConfig(tailwindConfig);
const screens: { [key: string]: string } = fullConfig.theme.screens;

let allViolations: any[] = []; // Replace 'any' with the actual type of violations

before(() => {
  allViolations = []; // Reset before the test suite runs
});

export const createAccessibilityCallback = (
  pageName: string,
  breakpointName: string
): ViolationCallback => {
  cy.task('log', `Running accessibility checks for ${pageName} at ${breakpointName} breakpoint`);

  return (violations) => {
    cypressLog(violations);
    terminalLog(violations);

    if (Cypress.config('enableScreenshots')) {
      screenshotViolations(violations, pageName, breakpointName);
    }

    allViolations.push(...violations);
  };
};

const viewportSizes: ViewportSize[] = [
  {
    name: 'Mobile',
    width: 320,
    height: 812,
  },
  {
    name: 'Tablet',
    width: parseInt(screens.md, 10),
    height: 1024,
  },
  {
    name: 'Desktop',
    width: parseInt(screens.lg, 10),
    height: 660,
  },
];

describe('Accessibility Tests', () => {
  it('should be accessible', () => {
    cy.task('sitemapLocations').then((pages) => {
      pages.forEach((page) => {
        cy.visit(page);
        cy.injectAxe();

        if (Cypress.config('axeIgnoreContrast')) {
          cy.configureAxe({
            rules: [
              {
                id: 'color-contrast',
                enabled: false,
              },
            ],
          });
        }

        const url = new URL(page);
        const path = url.pathname;

        viewportSizes.forEach((viewport) => {
          cy.viewport(viewport.width, viewport.height);

          cy.checkA11y(
            null,
            null,
            // {
            //   runOnly: {
            //     type: 'tag',
            //     values: ['wcag2a', 'wcag2aa', 'best-practice', 'section508'],
            //   },
            // },
            createAccessibilityCallback(path, viewport.name),
            true //  Do not fail the test when there are accessibility failures
          );
        });
      });
    });
  });
});

after(() => {
  // Send the accumulated violations to the custom task
  cy.task('processAccessibilityViolations', allViolations);
});
