/// <reference types="cypress" />
declare namespace Cypress {
  interface Chainable {
    checkElementExists(elm: string, callback: () => void): Chainable<Element>;
  }
}
