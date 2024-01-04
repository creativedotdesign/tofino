// Define types for better type-checking and readability
type Violation = {
  id: string;
  nodes: Array<{ target: string[] }>;
};

type HtmlDetails = {
  tagName: string | null;
  textContent: string | null;
};

export const screenshotViolations = (
  violations: Violation[],
  pageName: string,
  breakpointName: string
): void => {
  cy.task('log', `Screenshotting violations for ${pageName} at ${breakpointName} breakpoint`);

  violations.forEach((violation, index) => {
    violation.nodes.forEach((node, nodeIndex) => {
      const elementSelector = node.target[0];

      if (Cypress.$(elementSelector).length) {
        cy.get(elementSelector).then(($el) => {
          // Check selector is not :root
          if ($el.is(':root')) {
            return;
          }

          // Scroll to the element
          cy.get($el).scrollIntoView();

          $el.addClass('highlight-violation');

          if (pageName === '/') {
            pageName = 'home';
          }

          // Remove leading slash
          if (pageName.charAt(0) === '/') {
            pageName = pageName.substr(1);
          }

          // Remove trailing slash
          if (pageName.charAt(pageName.length - 1) === '/') {
            pageName = pageName.substr(0, pageName.length - 1);
          }

          // convert the pageName to a valid filename
          pageName = pageName.replace(/\//g, '-');

          // Ensure the element is visible
          cy.get($el).then(() => {
            const screenshotName = `${pageName}-${
              violation.id
            }-${breakpointName.toLowerCase()}-${index}-${nodeIndex}`;
            cy.screenshot(screenshotName, {
              capture: 'viewport',
              onAfterScreenshot($el) {
                $el.removeClass('highlight-violation'); // Remove highlight class
              },
            });
          });
        });
      } else {
        cy.log(`No element selector found for violation ${violation.id}. Skipping screenshot.`);
      }
    });
  });
};

const extractHtmlDetails = (htmlString: string): HtmlDetails => {
  // Using regular expressions to find the tag name and text content
  const tagNameRegex = /<(\w+)/;
  const textContentRegex = />([^<]+)</;

  // Extracting tag name
  const tagNameMatch = htmlString.match(tagNameRegex);
  const tagName = tagNameMatch ? tagNameMatch[1] : null;

  // Extracting text content
  const textContentMatch = htmlString.match(textContentRegex);
  let textContent = textContentMatch ? textContentMatch[1].trim() : null;

  // Replacing HTML entities with their character equivalents for text content
  if (textContent) {
    const htmlEntities = {
      '&amp;': '&',
      '&lt;': '<',
      '&gt;': '>',
      '&quot;': '"',
      '&#39;': "'",
    };
    textContent = textContent.replace(/&amp;|&lt;|&gt;|&quot;|&#39;/g, function (match) {
      return htmlEntities[match];
    });
  }

  return { tagName, textContent };
};

// Define a type for the violations parameter
type ViolationData = {
  description: string;
  id: string;
  impact: string;
  nodes: number;
  domNodes: string[];
};

/**
 * Display the accessibility violation table in the terminal
 * @param violations array of results returned by Axe
 * @link https://github.com/component-driven/cypress-axe#in-your-spec-file
 */
export const terminalLog = (violations: ViolationData[]): void => {
  cy.task('log', 'Violations: ' + violations.length);

  // pluck specific keys to keep the table readable
  const violationData = violations.map(({ description, id, impact, nodes }) => ({
    description,
    id,
    impact,
    nodes: nodes.length,
    domNodes: nodes.map(({ html }) => {
      const { tagName, textContent } = extractHtmlDetails(html);
      return `${tagName}: ${textContent}`;
    }),
  }));

  cy.task('table', violationData);
};

// Define an enum for severity indicators
const severityIndicators = {
  minor: '⚪️',
  moderate: '🟡',
  serious: '🟠',
  critical: '🔴',
};

export const cypressLog = (violations: ViolationData[]): void => {
  violations.forEach((violation) => {
    const targets = violation.nodes.map(({ target }) => target);
    const nodes = Cypress.$(targets.join(','));
    const consoleProps = () => violation;
    const { help, helpUrl, impact } = violation;

    Cypress.log({
      $el: nodes,
      consoleProps,
      message: `[${help}](${helpUrl})`,
      name: `${severityIndicators[impact]} A11Y`,
    });

    targets.forEach((target) => {
      const el = Cypress.$(target.join(','));

      Cypress.log({
        $el: el,
        consoleProps,
        message: target,
        name: '🔧',
      });
    });
  });
};
