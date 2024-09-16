describe('HTML Validation Tests', () => {
  it('should be valid', () => {
    cy.task('sitemapLocations').then((pages: string[]) => {
      pages.forEach((page: string) => {
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
