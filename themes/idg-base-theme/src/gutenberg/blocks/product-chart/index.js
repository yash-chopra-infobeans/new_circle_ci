/**
 * Internal dependencies.
 */
import DisplayComponent from './DisplayComponent';
import SaveComponent from './SaveComponent';

/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType('idg-base-theme/product-chart-block', {
  title: __('Product chart', 'idg-base-theme'),
  icon: 'chart-bar',
  category: 'embed',
  attributes: {
    productData: {
      type: 'array',
      default: [],
    },
    isShowingRank: {
      type: 'boolean',
      default: true,
    },
    linksInNewTab: {
      type: 'boolean',
      default: true,
    },
  },
  edit: DisplayComponent,
  save: SaveComponent,
});
