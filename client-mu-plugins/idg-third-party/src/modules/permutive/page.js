import { isEmpty } from 'lodash';

const page = () => {
  const primaryIds = window.IDG.getItemFromDataLayer('gtaxPrimaryIdsList');
  const secondaryIds = window.IDG.getItemFromDataLayer('gtaxIdList');
  const pageType = window.IDG.getItemFromDataLayer('page_type');
  const publishedAt = window.IDG.getItemFromDataLayer('dateTimePublished');
  const modifiedAt = window.IDG.getItemFromDataLayer('dateTimeUpdate');

  const data = {
    page: {
      type: window.IDG.getItemFromDataLayer('displayType'),
      language: 'en',
      tags: window.IDG.getItemFromDataLayer('tags'),
      audience: window.IDG.getItemFromDataLayer('audience'),
      description: window.IDG.getItemFromDataLayer('description'),
      article: {
        authors: [window.IDG.getItemFromDataLayer('author')],
        description: window.IDG.getItemFromDataLayer('description'),
        id: window.IDG.getItemFromDataLayer('articleId'),
        // isInsiderContent: stringFromDataLayer('isInsiderContent') == 'true', // CIO requirement? post-MVP
        source: window.IDG.getItemFromDataLayer('source'),
        title: window.IDG.getItemFromDataLayer('articleTitle'),
        type: window.IDG.getItemFromDataLayer('articleType'),
        // purchaseIntent: '', // post-MVP.
        ...(!isEmpty(publishedAt) ? { publishedAt } : {}),
        ...(!isEmpty(modifiedAt) ? { modifiedAt } : {}),
      },
      // Only need to add category and golden taxonomy data to article pages as there not really used on other pages.
      ...(pageType === 'article'
        ? {
            gTax: {
              primaryIds: isEmpty(primaryIds)
                ? []
                : primaryIds.split(',').map(function (id) {
                    return parseInt(id, 10);
                  }), // Primary category and anchestors golden id's.
              secondaryIds: isEmpty(secondaryIds)
                ? []
                : secondaryIds.split(',').map(function (id) {
                    return parseInt(id, 10);
                  }), // All categories and there anchestors golden id's.
            },
            tax: {
              primaryCategories: window.IDG.getItemFromDataLayer(
                'primaryAncestorCategoryListSlugs',
              ).split(','), // Primary category slug + ancestor category slugs.
              secondaryCategories: window.IDG.getItemFromDataLayer('categoriesSlugs').split(','), // All category slugs + all ancestor category slugs.
            },
          }
        : {}),
      ads: {
        adblocker: window.IDG.getItemFromDataLayer('adBlockerEnabled'),
        enabled: true, // always enabled
      },
    },
  };

  window.permutive.addon('web', data);
};

export default page;
