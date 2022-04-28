/* eslint-disable no-undef */
Cypress.Commands.add('vue', () => {
  return cy.wrap(Cypress.vueWrapper);
});
