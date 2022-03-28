/**
 * Internal dependencies.
 */
import DisplayProductChartItem from './DisplayProductChartItem';
import SaveProductChartItem from './SaveProductChartItem';

/**
 * WordPress dependencies.
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType('idg-base-theme/product-chart-item', {
  title: __('Product chart item', 'idg-base-theme'),
  icon: 'products',
  attributes: {
    rank: {
      type: 'number',
      default: 0,
    },
    productId: {
      type: 'number',
      default: 0,
    },
    productTitle: {
      type: 'string',
      default: '',
    },
    titleOverride: {
      type: 'boolean',
      default: false,
    },
    productContent: {
      type: 'string',
      default: '',
    },
    productRating: {
      type: 'number',
      default: 0,
    },
    ratingOverride: {
      type: 'boolean',
      default: false,
    },
    productImageSize: {
      type: 'string',
      default: 'Small',
    },
    productImage: {
      type: 'string',
      default: '',
    },
    imageFromOrigin: {
      type: 'boolean',
      default: false,
    },
    activeReview: {
      type: 'number',
    },
    version: {
      type: 'string',
      default: '1.1.0',
    },
    productContentInner: {
      type: 'array',
      default: [],
    },
  },
  parent: ['idg-base-theme/product-chart-block'],
  edit: DisplayProductChartItem,
  save: SaveProductChartItem,
  deprecated: [
    {
      attributes: {
        rank: {
          type: 'number',
          default: 0,
        },
        productId: {
          type: 'number',
          default: 0,
        },
        productTitle: {
          type: 'string',
          default: '',
        },
        titleOverride: {
          type: 'boolean',
          default: false,
        },
        productContent: {
          type: 'string',
          default: '',
        },
        productRating: {
          type: 'number',
          default: 0,
        },
        ratingOverride: {
          type: 'boolean',
          default: false,
        },
        productImageSize: {
          type: 'string',
          default: 'Small',
        },
        productImage: {
          type: 'number',
          default: 0,
        },
        imageFromOrigin: {
          type: 'boolean',
          default: false,
        },
        version: {
          type: 'string',
          default: '1.0.0',
        },
      },
      save: () => null,
    },
  ],
});
