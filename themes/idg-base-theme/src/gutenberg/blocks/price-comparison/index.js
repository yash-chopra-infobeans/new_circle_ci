/**
 * Internal dependencies.
 */
import DisplayComponent from './DisplayComponent';

/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType('idg-base-theme/price-comparison-block', {
  title: __('Price comparison', 'idg-base-theme'),
  icon: 'feedback',
  category: 'embed',
  keywords: [
    __('product', 'idg-base-theme'),
    __('price', 'idg-base-theme'),
    __('comparison', 'idg-base-theme'),
  ],
  attributes: {
    productId: {
      type: 'number',
      default: null,
    },
    linksInNewTab: {
      type: 'boolean',
      default: true,
    },
    instanceId: {
      type: 'number',
      default: 0,
    },
    footerText: {
      type: 'string',
      default: __('Price comparison from over 24,000 stores worldwide', 'idg-base-theme'),
    },
  },
  usesContext: ['idg-base-theme/primaryProductId'],
  edit: DisplayComponent,
  save: () => null,
});
