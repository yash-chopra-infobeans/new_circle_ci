// variables used in component and contaner tests(s)
export const exampleTab = {
  name: 'headline',
  title: 'Headline 2',
  className: 'tab tab-headline',
  metaKey: 'multi_title_headline',
  combineCharLimit: true,
  prefix: {
    enabled: true,
    metaKey: 'multi_title_headline_prefix',
  },
};

export const exampleTabTwo = {
  name: 'seo',
  title: 'SEO',
  className: 'tab tab-seo',
  metaKey: 'multi_title_seo',
  combineCharLimit: true,
  inheritValueFrom: 'headline',
  prefix: {
    enabled: true,
    metaKey: 'multi_title_seo_prefix',
    inheritValueFrom: 'headline',
  },
};

export const tabs = [{
  name: 'headline',
  title: 'Headline',
  className: 'tab tab-headline',
  metaKey: 'multi_title_headline',
  prefix: {
    enabled: true,
    metaKey: 'multi_title_headline_prefix',
  },
}, {
  name: 'seo',
  title: 'SEO',
  charLimit: 3,
  metaKey: 'meta_seo',
  blockPublishOnError: true,
}, {
  name: 'shorttitle',
  title: 'Short Title',
  inheritPlaceholderFrom: 'headline',
  metaKey: 'multi_title_shortitle',
  prefix: {
    enabled: true,
    charLimit: 3,
    blockPublishOnError: true,
  },

}, {
  name: 'subtitle',
  title: 'Subtitle',
  inheritPlaceholderFrom: 'headline',
  blockPublishOnError: true,
  combineCharLimit: true,
  charLimit: 5,
  prefix: {
    enabled: true,
    charLimit: 5,
  },
}, {
  name: 'strapline',
  title: 'Strapline',
  inheritPlaceholderFrom: 'headline',
  metaKey: 'multi_title_strapline',
  inheritValueFrom: 'headline',
  combineCharLimit: true,
  blockPublishOnError: true,
  charLimit: 100,
  prefix: {
    inheritValueFrom: 'headline',
  },
  additionalFields: {
    metaKeys: [
      'additional_meta_multi_title',
    ],
  },
},];

export default exampleTab;
