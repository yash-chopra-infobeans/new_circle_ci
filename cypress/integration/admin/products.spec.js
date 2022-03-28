const ADD_NEW_PRODUCT_SELECTOR =
  "#menu-posts-product .wp-submenu a[href='post-new.php?post_type=product']";

describe('Products.', () => {
  before(() => {
    cy.seed('ProductsSeeder');
    cy.seed('CategoriesSeeder');
    cy.seed('ManufacturerSeeder');
  });

  it('I can add a new product.', () => {
    const productTitle = 'iPhone 12';

    cy.visitAdmin();
    cy.get(ADD_NEW_PRODUCT_SELECTOR).click({ force: true });
    cy.get('#post-title-0').type('iPhone 12');
    cy.get('.components-button.edit-post-sidebar__panel-tab').contains('Product').click();

    cy.get('.components-panel__body-title').contains('Categories').click();
    cy.wait(2000);
    cy.get('.components-base-control__field').contains('iPhone').click();

    cy.get('.components-panel__body-title').contains('Manufacturers').click();
    cy.wait(2000);
    cy.get('.components-panel__body')
      .contains('Manufacturers')
      .get('.components-form-token-field__input')
      .type('Apple');
    cy.wait(200);
    cy.get('.components-form-token-field__suggestion').contains('Apple').click();

    cy.saveCurrentPost();
    cy.visit('/wp-admin/edit.php?post_type=product');
    cy.get('#the-list > tr .row-title').contains(productTitle);
  });
});
