/**
 * External dependencies.
 */
import 'select2';
import updateGutenbergStores from './update-gutenberg-stores';

/**
 * WordPress dependencies.
 */
const { removeEditorPanel } = wp.data.dispatch('core/edit-post');

// eslint-disable-next-line no-undef
jQuery($ => {
  // Localized from PHP.
  // eslint-disable-next-line no-undef
  if (postType !== 'post') {
    removeEditorPanel('taxonomy-panel-territory');
    return;
  }

  // Remove default category and tag selection panels.
  removeEditorPanel('taxonomy-panel-category');
  removeEditorPanel('taxonomy-panel-post_tag');
  removeEditorPanel('taxonomy-panel-territory');

  const categorySelectionElement = $('#_idg_category_selection_metabox');
  const categorySelectionMetabox = $('#idg_category_selection_metabox');
  const tagSelectionElement = $('#_idg_tag_selection_metabox');
  const tagSelectionMetabox = $('#idg_tag_selection_metabox');

  let tagPage = 0;

  // Initialize select2 for tag selection.
  tagSelectionElement.select2({
    closeOnSelect: false,
    minimumInputLength: 1,
    ajax: {
      // eslint-disable-next-line no-undef
      url: `${wpApiSettings.root}wp/v2/tags`,
      dataType: 'json',
      cache: true,
      data(params) {
        tagPage += 1;
        return {
          context: 'view',
          search: params.term,
          page: tagPage,
          per_page: 20,
        };
      },
      beforeSend(xhr) {
        // eslint-disable-next-line no-undef
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      },
      processResults(data) {
        let more = false;
        let results = [];
        if (data) {
          results = data.map(term => {
            return { id: term.id, text: term.name };
          });

          if (data.length === 20) {
            more = true;
          }
        }

        return { results, pagination: { more } };
      },
    },
  });

  tagSelectionMetabox
    .find('.select2-selection__rendered')
    .on('input', '.select2-search__field', () => {
      tagPage = 0;
    });

  // Append newly added items at the end of selection list.
  categorySelectionElement.on('select2:select', evt => {
    const { element } = evt.params.data;
    const $element = $(element);

    categorySelectionElement.append($element);
    categorySelectionElement.trigger('change');
  });

  // Update tag data on Gutenberg store.
  tagSelectionElement.on('change', () => {
    updateGutenbergStores('#_idg_tag_selection_metabox', 'tags');
    tagSelectionMetabox.find('.select2-search__field').val('');
  });

  // Update category data on Gutenberg store.
  categorySelectionElement.on('change', () => {
    updateGutenbergStores('#_idg_category_selection_metabox', 'categories');
    categorySelectionMetabox.find('.select2-search__field').val('');
  });

  // Prevent select2 from auto-sorting on item selection.
  $.fn.select2.amd.require(['select2/utils'], Utils => {
    const container = categorySelectionElement
      // Initialize select2
      .select2({
        closeOnSelect: false,
      })
      .data('select2').$container;

    container.find('ul').sortable({
      containment: 'parent',
      update: (event, ui) => {
        $(ui.item[0])
          .parent()
          .find('.select2-selection__choice')
          // Can't use arrow func as we need 'this' reference.
          // eslint-disable-next-line func-names
          .each(function () {
            // Disabled because it's an internal variable of select2
            // eslint-disable-next-line no-underscore-dangle
            const elm = Utils.__cache[$(this).data('select2Id')];
            const { element } = elm.data;
            const $element = $(element);

            categorySelectionElement.append($element);

            categorySelectionElement.trigger('change');
          });
      },
    });
  });
});
