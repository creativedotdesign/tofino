// Import tailwindcss config file
import resolveConfig from 'tailwindcss/resolveConfig';
import tailwindConfig from '../../tailwind.config.js';

const fullConfig = resolveConfig(tailwindConfig);
const screens = fullConfig.theme.screens;

describe('Navigation Tests', () => {
  it('Mobile Menu, check functionality', () => {
    cy.baseUrl();

    // Mobile Viewport
    const mobileHeight = 812;

    cy.viewport(parseInt(screens.sm, 10), mobileHeight);

    // Make sure there's no scroll lock on body
    cy.get('body').should('not.have.css', 'overflow', 'hidden');

    // Open menu
    cy.get('button[data-cy="open-mobile-menu"]').click();

    // Make sure there is scroll lock on body
    cy.get('body').should('have.css', 'overflow', 'hidden');

    // Check if open menu is viewport height
    cy.get('#main-menu').invoke('height').should('equal', mobileHeight);

    // Tablet Viewport
    const tabletHeight = 1024;

    cy.viewport(parseInt(screens.md, 10), tabletHeight);

    // Check if open menu is still viewport height
    cy.get('[id="main-menu"]').invoke('height').should('equal', tabletHeight);

    // Click close menu
    cy.get('nav').find('button[data-cy="close-mobile-menu"]').click();

    // Check if open state is hidden
    cy.get('#main-menu').should('not.be.visible');

    // Check that scroll lock is removed
    cy.get('body').should('not.have.css', 'overflow', 'hidden');

    // Check ESC closes menu
    cy.get('button[data-cy="open-mobile-menu"]').click();
    cy.get('body').type('{esc}', { force: true });

    // Check if open state is hidden
    cy.get('#main-menu').should('not.be.visible');

    // Check that scroll lock is removed
    cy.get('body').should('not.have.css', 'overflow', 'hidden');
  });

  it('Navbar is sticky or not sticky', () => {
    cy.baseUrl();

    // Desktop Viewport
    cy.viewport(parseInt(screens.lg, 10), 660);

    cy.get('body').then(($body) => {
      if ($body.hasClass('menu-fixed')) {
        // Check header for .nav-stuck class
        cy.get('header').should('have.class', 'sticky-top');

        // Check if visible
        cy.get('header').should('be.visible');

        // Scroll Down 30%, check if visible
        cy.scrollTo(0, '30%');
        cy.get('header').should('be.visible');

        // Scroll to bottom, check if visible
        cy.scrollTo('bottom');
        cy.get('header').should('be.visible');

        // Scroll to top, check if visible
        cy.scrollTo('top');
        cy.get('header').should('be.visible');
      } else {
        // Check if not visible
        cy.get('header').should('not.have.class', 'sticky-top');
      }
    });
  });
});
