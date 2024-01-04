Cypress.Commands.add('checkElementExists', (elm, callback) => {
  cy.get('body').then(($body) => {
    if ($body.find(elm).length > 0) {
      callback();
    } else {
      cy.log('Element not found, stopping further tests in this spec.', elm);

      Cypress.runner.stop();
    }
  });
});
