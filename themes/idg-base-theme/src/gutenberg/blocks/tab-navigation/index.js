import DisplayComponent from './DisplayComponent';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

const amountDefault = [
  {
    title: 'Link',
    url: '#',
    opensInNewTab: false,
    id: 0,
  },
];

const blockAttributes = {
  items: {
    type: 'array',
    default: amountDefault,
  },
};

registerBlockType('idg-base-theme/tab-navigation', {
  title: __('Tab Navigation', 'idg-base-theme'),
  icon: 'welcome-add-page',
  category: 'layout',
  supports: {
    multiple: true,
  },
  attributes: blockAttributes,
  edit: DisplayComponent,
});
