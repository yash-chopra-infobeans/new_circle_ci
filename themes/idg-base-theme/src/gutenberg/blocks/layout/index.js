const { wp = {} } = window;
const { InnerBlocks } = wp.blockEditor;
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Icon } = wp.components;

const Layout = ({ renderLeft, renderRight }) => (
  <section className="layout--right-rail">
    <div className="wp-block-columns">
      <div className="wp-block-column">{renderLeft()}</div>
      <div className="wp-block-column">{renderRight()}</div>
    </div>
  </section>
);

const PlaceholderAd = () => (
  <div className="ad-placeholder">
    <div>
      <Icon size={32} icon="money-alt" />
    </div>
    <h4>{__('Advert', 'idg-base-theme')}</h4>
    <div>{__('The right rail is reserved for monetization.', 'idg-base-theme')}</div>
  </div>
);

const Ad = () => (
  <div className="ad page-ad ad-right-rail is-sticky" data-ad-template="right_rail" />
);

registerBlockType('idg-base-theme/layout', {
  title: __('Right Rail Layout', 'idg-base-theme'),
  category: 'layout',
  keywords: [__('layout'), __('right rail')],
  edit: () => <Layout renderLeft={() => <InnerBlocks />} renderRight={() => <PlaceholderAd />} />,
  save: () => <Layout renderLeft={() => <InnerBlocks.Content />} renderRight={() => <Ad />} />,
});
