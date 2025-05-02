describe('Homepage', () => {
  it('loads successfully', () => {
    cy.visit('/');
    cy.getByTestId('fake').should('exist');
  });
});
