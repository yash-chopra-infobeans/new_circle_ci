const el = wp.element.createElement;
const { registerBlockType } = wp.blocks;
const { InnerBlocks } = wp.blockEditor;
const { __ } = wp.i18n;

const BLOCKS_TEMPLATE = [
  ['core/image'],
  ['core/pullquote', { className: 'editor-pull-quote-block pull-quote-block' }],
];

registerBlockType('idg-base-theme/image-pull-quote', {
  title: __('Image with Pull Quote', 'idg-base-theme'),
  icon: 'cover-image',
  category: 'design',

  edit: () => {
    return el(
      'div',
      { style: { display: 'flow-root' }, className: 'image-pull-quote' },
      el(InnerBlocks, {
        template: BLOCKS_TEMPLATE,
        templateLock: 'all',
      }),
    );
  },

  save: () => {
    return el('div', { className: 'image-pull-quote' }, el(InnerBlocks.Content, {}));
  },
});
