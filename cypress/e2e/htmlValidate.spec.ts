describe('HTML Validation Tests', () => {
  it('should be valid', () => {
    cy.task('sitemapLocations').then((pages) => {
      pages.forEach((page) => {
        cy.visit(page);
        cy.htmlvalidate({
          rules: {
            'require-sri': 'off',
            'element-permitted-content': 'off',
          },
        });
      });
    });
  });
});
