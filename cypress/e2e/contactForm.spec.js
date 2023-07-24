describe('Contact Form', () => {
  it('Check if form submits when all fields are valid', () => {
    // Go to the home page
    cy.baseUrl();

    // Add intercept to check json response
    cy.intercept('POST', '**/admin-ajax.php').as('ajaxForm');

    // Perform operations
    cy.get('[data-cy="contact-form"]').then((contactForm) => {
      // Fill in fields
      cy.wrap(contactForm).find('[name="contact-first-name"]').type('Testing First Name');
      cy.wrap(contactForm).find('[name="contact-last-name"]').type('Testing Last Name');
      cy.wrap(contactForm).find('[name="contact-email"]').type('testing@email.com');
      cy.wrap(contactForm).find('[name="contact-phone"]').type('555-555-5555');
      cy.wrap(contactForm).find('[name="contact-message"]').type('Testing textarea');

      // Submit form
      cy.wrap(contactForm).submit();
    });

    // Wait for response
    cy.wait('@ajaxForm');

    // Get response
    cy.get('@ajaxForm').then((xhr) => {
      // Check if method is POST
      expect(xhr.request.method).to.equal('POST');

      // Check if Status Code is 200
      expect(xhr.response.statusCode).to.equal(200);
      expect(xhr.response.statusMessage).to.equal('OK');

      // Check the success value
      expect(xhr.response.body.success).to.equal(true);

      // Check if there is a user message
      expect(xhr.response.body.message).not.to.be.empty;
    });

    // Check if user message is rendering and is not empty
    cy.get('[data-cy="contact-response-message"]').should('be.visible').should('not.be.empty');
  });
});
