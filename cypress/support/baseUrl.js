const baseUrl = Cypress.env('baseUrl');

Cypress.Commands.add('baseUrl', (path = '') => {
  cy.visit(baseUrl + '/' + path);
});
