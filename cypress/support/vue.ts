Cypress.Commands.add('vue', () => {
  return cy.wrap(Cypress.vueWrapper);
});
