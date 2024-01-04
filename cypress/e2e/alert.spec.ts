describe('Alert', () => {
  it('Check if Alert is visible', () => {
    cy.visit('/');

    cy.checkElementExists('[data-alert-id]', () => {
      cy.get('[data-alert-id]').should('be.visible');
    });
  });

  it('Check Alert is not visable on scroll', () => {
    cy.checkElementExists('[data-alert-id]', () => {
      cy.scrollTo(0, '50%');

      cy.get('[data-alert-id]').should('not.inViewport');
    });
  });

  it('Check Alert closes', () => {
    cy.checkElementExists('[data-alert-id]', () => {
      cy.get('[data-alert-id] .js-close').click();

      cy.get('[data-alert-id]').should('not.exist');

      cy.getCookie('tofino-alert-closed').should('have.property', 'value', 'yes');
    });
  });
});
