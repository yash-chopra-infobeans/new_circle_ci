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
  },
  selectedPosts: {
    type: 'array',
  },
  displayEyebrows: {
    type: 'boolean',
    default: true,
  },
  displayBylines: {
    type: 'boolean',
    default: true,
  },
  excludeSponsored: {
    type: 'boolean',
    default: false,
  },
};

registerBlockType('idg-base-theme/hero', {
  title: __('Hero', 'idg-base-theme'),
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
