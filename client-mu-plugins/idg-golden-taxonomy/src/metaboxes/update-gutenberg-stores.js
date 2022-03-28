/**
 * Hotfix for updating the Gutenberg store
 * to ensure that terms are saved as expected
 * with other taxonomies on post save.
 *
 * Gets the selected items from the given element
 * (in this case the multi-select) and inserts them
 * as the relevent taxonomy values on the Gutenberg
 * post/store.
 *
 * @param {string} selector     Element selector of the data to get.
 * @param {string} taxonomyName Name of the taxonomy to store against.
 */
export default (selector, taxonomyName) => {
  const { dispatch, select } = wp.data;
  const { editPost } = dispatch('core/editor');
  const { editEntityRecord } = dispatch('core');
  const { getCurrentPostId } = select('core/editor');

  const itemIds = [];
  const selections = document.querySelector(selector);

  selections.forEach(item => {
    if (item.selected) {
      itemIds.push(item.value);
    }
  });

  const selectedTerms = { [taxonomyName]: itemIds };

  editPost({
    ...selectedTerms,
    meta: {
      _idg_post_categories: itemIds,
    },
  });

  editEntityRecord('postType', 'post', getCurrentPostId(), selectedTerms);
};
