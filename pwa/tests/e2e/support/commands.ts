Cypress.Commands.add(
  'getByTestId',
  (
    testId: string,
    options?: Partial<Cypress.Loggable & Cypress.Timeoutable>,
  ) => {
    return cy.get(`[data-testid="${testId}"]`, options);
  },
);
