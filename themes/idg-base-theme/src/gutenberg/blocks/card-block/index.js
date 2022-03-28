import DisplayComponent from './DisplayComponent';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

const content = [
  {
    card_content_image: '',
    card_content_eyebrow: '',
    card_content_title: '',
    card_content_text: '',
    card_content_url: '',
  },
];

const blockAttributes = {
  items: {
    type: 'array',
    default: content,
  },
  blockTitle: {
    type: 'string',
    default: '',
  },
  blockStyle: {
    type: 'string',
    default: '',
  },
  ctaLink: {
    type: 'string',
    default: '',
  },
  ctaTitle: {
    type: 'string',
    default: '',
  },
  ctaStyle: {
    type: 'sting',
    default: '',
  },
};

registerBlockType('idg-base-theme/card-block', {
  title: __('Card Block', 'idg-base-theme'),
  icon: 'editor-table',
  category: 'layout',
  supports: {
    multiple: true,
  },
  attributes: blockAttributes,
  edit: DisplayComponent,

  save: () => null,
});
