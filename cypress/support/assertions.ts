const isInViewport = (_chai) => {
  function assertIsInViewport() {
    const subject = this._obj;

    const windowHeight = Cypress.$(cy.state('window')).height();
    const bottomOfCurrentViewport = windowHeight;
    const rect = subject[0].getBoundingClientRect();

    this.assert(
      (rect.top > 0 && rect.top < bottomOfCurrentViewport) ||
        (rect.bottom > 0 && rect.bottom < bottomOfCurrentViewport),
      'expected #{this} to be in viewport',
      'expected #{this} to not be in viewport',
      subject
    );
  }

  _chai.Assertion.addMethod('inViewport', assertIsInViewport);
};

chai.use(isInViewport);
