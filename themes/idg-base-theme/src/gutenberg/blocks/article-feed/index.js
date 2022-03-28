import DisplayComponent from './DisplayComponent';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

const blockAttributes = {
  type: {
    type: 'string',
    default: 'category',
  },
  postType: {
    type: 'string',
    default: 'posts',
  },
  filters: {
    type: 'string',
  },
  amount: {
    type: 'integer',
    default: 1,
  },
  offset: {
    type: 'integer',
    default: 0,
  },
  selectedPosts: {
    type: 'array',
  },
  displayEyebrows: {
    type: 'boolean',
    default: true,
  },
  displayExcerpt: {
    type: 'boolean',
    default: true,
  },
  displayBylines: {
    type: 'boolean',
    default: true,
  },
  style: {
    type: 'string',
    default: 'list',
  },
  displayDate: {
    type: 'boolean',
    default: true,
  },
  displayScore: {
    type: 'boolean',
    default: true,
  },
  displayButton: {
    type: 'boolean',
    default: false,
  },
  buttonText: {
    type: 'string',
    default: 'More stories',
  },
  buttonLink: {
    type: 'string',
    default: '',
  },
  ajaxLoad: {
    type: 'boolean',
    default: false,
  },
  excludeSponsored: {
    type: 'boolean',
    default: false,
  },
};

registerBlockType('idg-base-theme/article-feed', {
  title: __('Article Feed', 'idg-base-theme'),
  icon: 'analytics',
  category: 'layout',
  supports: {
    multiple: true,
  },
  attributes: blockAttributes,

  edit: DisplayComponent,

  // Returns null due to the component being rendered server side
  save: () => null,
});
