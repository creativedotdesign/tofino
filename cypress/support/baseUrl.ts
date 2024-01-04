const baseUrl = Cypress.env('baseUrl');

Cypress.Commands.add('baseUrl', (value = '') => {
  cy.visit(baseUrl + '/' + value);
});
