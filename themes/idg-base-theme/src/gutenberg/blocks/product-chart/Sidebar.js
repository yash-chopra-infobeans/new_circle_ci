/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, ToggleControl } = wp.components;
const { is_origin: isOrigin } = window.baseTheme;

const Sidebar = ({ attributes, setAttributes }) => {
  if (!isOrigin) {
    return null;
  }

  const { isShowingRank, linksInNewTab } = attributes;

  return (
    <InspectorControls>
      <PanelBody
        className="product-chart-item-product-data-settings"
        title={__('Product data settings', 'idg-base-theme')}
      >
        <ToggleControl
          label={__('Show product rank', 'idg-base-theme')}
          checked={isShowingRank}
          onChange={() => {
            setAttributes({ isShowingRank: !isShowingRank });
          }}
        />
        <ToggleControl
          label={__('Open product links in new tab', 'idg-base-theme')}
          checked={linksInNewTab}
          onChange={() => setAttributes({ linksInNewTab: !linksInNewTab })}
        />
      </PanelBody>
    </InspectorControls>
  );
};

export default Sidebar;
