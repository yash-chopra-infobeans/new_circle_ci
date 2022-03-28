import Multititle from './containers/TabsContainer';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { InnerBlocks } = wp.editor;
const { addFilter } = wp.hooks;

/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType('bigbite/multi-title', {
  title: __('Article Titles', 'multi-title'),
  icon: 'editor-table',
  category: 'common',
  keywords: [__('Section', 'multi-title'), __('Grey', 'multi-title')],
  edit: Multititle,
  supports: {
    inserter: true,
    multiple: false,
    reusable: false,
    html: false,
  },
  save: () => (
    <section>
      <div className="container">
        <InnerBlocks.Content />
      </div>
    </section>
  ),
});

addFilter('bigbite.revisions.hideCoreTitle', 'bigbite/multi-title', () => true);
