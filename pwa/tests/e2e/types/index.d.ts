/// <reference types="cypress" />

declare namespace Cypress {
  interface Chainable {
    getByTestId(
      testId: string,
      options?: Partial<Cypress.Loggable & Cypress.Timeoutable>,
    ): Chainable<JQuery<HTMLElement>>;
  }
}
