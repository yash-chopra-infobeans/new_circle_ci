describe('MultiTitle block validation', () => {
  it('Should block publishing if field value exceeds character limit and blockPublishOnError is set to true', () => {
    cy.editPost(10);

    cy.get('#0-headline-view textarea').last()
      .type('longer than alllowed limit').should('have.value', 'longer than alllowed limit');

    cy.get('button.editor-post-publish-button').should('have.attr', 'aria-disabled', 'true');

    cy.get('#0-headline-view .title-input .charLimit-count').invoke('text').should('contain', '-11');
  });

  it('Should allow publish if field value is within character limit and blockPublishOnError is set to true', () => {
    cy.editPost(10);

    cy.get('#0-headline-view textarea').last()
      .type('allowed').should('have.value', 'allowed');

    cy.get('button.editor-post-publish-button').should('have.attr', 'aria-disabled', 'false');

    cy.get('#0-headline-view .title-input .charLimit-count').invoke('text').should('contain', '8');
  });

  it('If the combined field value is greater than the combined character limit then prevent the post from being published', () => {
    cy.editPost(10);

    cy.get('button#0-combinedChar').click({ force: true });

    cy.get('#0-combinedChar-view textarea').first()
      .type('1234567').should('have.value', '1234567');

    cy.get('#0-combinedChar-view textarea').last()
    .type('123456789').should('have.value', '123456789');

    cy.get('#0-combinedChar-view .title-input .charLimit-count').invoke('text').should('contain', '-1');
  });
});
