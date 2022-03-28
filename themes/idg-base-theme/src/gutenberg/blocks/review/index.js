import DisplayComponent from './DisplayComponent';
import SaveComponent from './SaveComponent';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType('idg-base-theme/review-block', {
  title: __('Review', 'idg-base-theme'),
  icon: 'analytics',
  category: 'layout',
  attributes: {
    primaryProductId: {
      type: 'number',
      default: null,
    },
    comparisonProductId: {
      type: 'number',
      default: null,
    },
    heading: {
      type: 'string',
      default: 'At a glance',
    },
    rating: {
      type: 'number',
      default: 0,
    },
    editorsChoice: {
      type: 'boolean',
      default: false,
    },
    pros: {
      source: 'html',
      selector: '.pros',
    },
    cons: {
      source: 'html',
      selector: '.cons',
    },
    verdict: {
      source: 'html',
      selector: '.verdict',
    },
    pricingTitle: {
      type: 'string',
      default: __('Price When Reviewed', 'idg-base-theme'),
    },
    bestPricingTitle: {
      type: 'string',
      default: __('Best Pricing Today', 'idg-base-theme'),
    },
  },
  providesContext: {
    'idg-base-theme/primaryProductId': 'primaryProductId',
  },
  supports: {
    multiple: false,
    customClassName: false,
  },
  edit: DisplayComponent,
  save: SaveComponent,
});
