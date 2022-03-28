const TERRITORY_TAXONOMY_SELECTOR =
  "#menu-posts-product .wp-submenu a[href='edit-tags.php?taxonomy=territory&post_type=product']";

describe('Territories.', () => {
  it('I can add a new territory.', () => {
    cy.visitAdmin();
    cy.get(TERRITORY_TAXONOMY_SELECTOR).click({ force: true });
    cy.get('#tag-name').type('Canada');
    cy.get('#country-selector').select('CA');

    cy.server();
    cy.route('POST', '/wp-admin/admin-ajax.php').as('addTerritory');
    cy.get('#submit').click();
    cy.wait(['@addTerritory']).should('have.property', 'status', 200);

    cy.get('#the-list > tr .column-name').contains('Canada');
    cy.get('#the-list > tr .column-slug').contains('ca');
    cy.get('#the-list > tr .column-country').contains('Canada');
    cy.get('#the-list > tr .column-currency').contains('Canadian Dollar');
  });
});
