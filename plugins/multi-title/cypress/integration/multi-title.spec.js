describe('MultiTitle block', () => {
  it('SEO field should inherit title field when field values match', () => {
    cy.editPost(10);

    cy.get('#0-headline-view textarea').last()
    .should('have.attr', 'placeholder', 'Add article title');

    cy.get('#0-headline-view textarea').last()
      .type('test title').should('have.value', 'test title');

    cy.get('button#0-seo').click({ force: true });

    cy.get('#0-seo-view textarea').should('have.value', 'test title');
  });

  it('SEO field should not inherit title field when field values do not match', () => {
    cy.editPost(10);

    cy.get('#0-headline-view textarea').last()
    .should('have.attr', 'placeholder', 'Add article title');

    cy.get('#0-headline-view textarea').last()
      .type('test title').should('have.value', 'test title');

    cy.get('button#0-seo').click({ force: true });

    cy.get('#0-seo-view textarea').eq(0)
      .type(' test').should('have.value', 'test title test');

    cy.get('button#0-headline').click({ force: true });

    cy.get('#0-headline-view textarea').last()
      .type(' two').should('have.value', 'test title two');

    cy.get('button#0-seo').click({ force: true });

    cy.get('#0-seo-view textarea').eq(0).should('have.value', 'test title test');
  });

  it('Title field and fields that inherit the title which values are equal should update when title is edited via quick edit on post list page', () => {
    cy.editPost(11);

    cy.get('#0-headline-view textarea').last()
    .should('have.attr', 'placeholder', 'Add article title');

    cy.get('#0-headline-view textarea').last()
    .type('test title').should('have.value', 'test title');

    cy.get('button.editor-post-publish-button').click();

    cy.visit(`/wp-admin/edit.php`);

    cy.get('#post-11').trigger('mouseover');

    cy.get('#post-11 button.editinline').click({ force: true });

    cy.get('#edit-11 input[name="post_title"]').type(' 123');

    cy.get('#edit-11 button.save').click();

    cy.editPost(11);

    cy.get('#0-headline-view textarea').last().should('have.value', 'test title 123');

    cy.get('button#0-seo').click({ force: true });

    cy.get('#0-seo-view textarea').should('have.value', 'test title 123');

  });

  it('Title field should update when title is edited via quick edit on post list page but fields which inherit the title but have different values should not update', () => {
    cy.editPost(12);

    cy.get('#0-headline-view textarea').last()
    .should('have.attr', 'placeholder', 'Add article title');

    cy.get('#0-headline-view textarea').last()
    .type('test title').should('have.value', 'test title');

    cy.get('button#0-seo').click({ force: true });

    cy.get('#0-seo-view textarea').eq(0)
    .type(' seo').should('have.value', 'test title seo');

    cy.get('button.editor-post-publish-button').click();

    cy.visit(`/wp-admin/edit.php`);

    cy.get('#post-12').trigger('mouseover');

    cy.get('#post-12 button.editinline').click({ force: true });

    cy.get('#edit-12 input[name="post_title"]').type(' 123');

    cy.get('#edit-12 button.save').click();

    cy.editPost(12);

    cy.get('#0-headline-view textarea').last().should('have.value', 'test title 123');

    cy.get('button#0-seo').click({ force: true });

    cy.get('#0-seo-view textarea').eq(0).should('have.value', 'test title seo');
  });

  it('Placeholder should equal the value of the headline field', () => {
    cy.editPost(13);

    cy.get('#0-headline-view textarea').last()
    .should('have.attr', 'placeholder', 'Add article title');

    cy.get('#0-headline-view textarea').last()
    .type('test title').should('have.value', 'test title');

    cy.get('button#0-placeholder').click({ force: true });

    cy.get('#0-placeholder-view textarea').should('have.attr', 'placeholder', 'test title');
  });

  it('Prefix tab\'s prefix input should inherit it\' value from the headline tab\'s prefix input', () => {
    cy.editPost(14);

    cy.get('#0-headline-view textarea').first()
    .type('test prefix').should('have.value', 'test prefix');

    cy.get('button#0-prefix').click({ force: true });

    cy.get('#0-prefix-view textarea').first()
    .should('have.value', 'test prefix');
  });

  it('Edit and save additional field value(s)', () => {
    cy.editPost(12);

    cy.get('button#0-seo').click({ force: true });

    cy.get('#0-seo-view textarea').eq(1)
    .type('seo additional field').should('have.value', 'seo additional field');

    cy.get('button.editor-post-publish-button').click();

    cy.editPost(12);

    cy.get('button#0-seo').click({ force: true });

    cy.get('#0-seo-view textarea').eq(1).should('have.value', 'seo additional field');
  });

  it('On undo input value should be reverted', () => {
    cy.editPost(15);

    cy.get('#0-headline-view textarea').eq(1)
    .type('test').should('have.value', 'test');

    cy.get('button.editor-history__undo').click();

    if (parseFloat(Cypress.wp.version) === 5.2) {
      cy.get('#0-headline-view textarea').eq(1).should('have.value', '');
    } else {
      cy.get('#0-headline-view textarea').eq(1).should('have.value', 'tes');
    }
  });
});
