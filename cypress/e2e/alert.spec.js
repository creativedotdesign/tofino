describe('Alert', () => {
  it('Check if Alert is visible', () => {
    cy.baseUrl();

    cy.checkElementExists('#tofino-alert', () => {
      cy.get('#tofino-alert').should('be.visible');
    });
  });

  it('Check Alert is not visable on scroll', () => {
    cy.checkElementExists('#tofino-alert', () => {
      cy.scrollTo(0, '50%');

      cy.get('#tofino-alert').should('not.inViewport');
    });
  });

  it('Check Alert closes', () => {
    cy.checkElementExists('#tofino-alert', () => {
      cy.get('#tofino-alert .js-close').click();

      cy.get('#tofino-alert').should('not.exist');

      cy.getCookie('tofino-alert-closed').should('have.property', 'value', 'yes');
    });
  });
});
