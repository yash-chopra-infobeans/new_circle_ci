/**
 * Internal dependencies.
 */
import DisplayProductWidget from './DisplayProductWidget';

/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType('idg-base-theme/product-widget-block', {
  title: __('Product widget', 'idg-base-theme'),
  icon: 'welcome-widgets-menus',
  category: 'embed',
  keywords: [__('product', 'idg-base-theme'), __('widget', 'idg-base-theme')],
  attributes: {
    productId: {
      type: 'number',
      default: 0,
    },
    blockTitle: {
      type: 'string',
      default: '',
    },
    productImage: {
      type: 'number',
      default: 0,
    },
    imageFromOrigin: {
      type: 'boolean',
      default: false,
    },
    isHalfWidth: {
      type: 'boolean',
      default: false,
    },
    isFloatRight: {
      type: 'boolean',
      default: false,
    },
    linksInNewTab: {
      type: 'boolean',
      default: true,
    },
    activeReview: {
      type: 'number',
    },
    version: {
      type: 'string',
      default: '1.1.0',
    },
  },
  edit: DisplayProductWidget,
  save: () => null,
});
