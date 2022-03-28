/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const { InspectorControls } = wp.blockEditor;
const { ToggleControl, PanelBody } = wp.components;
const { is_origin: isOrigin } = window.baseTheme;

const { Selector: ProductSelector } = window.IDGProducts.components;

const Sidebar = ({ attributes, setAttributes, context }) => {
  if (!isOrigin) {
    return null;
  }

  const { linksInNewTab, productId } = attributes;
  const selector = typeof context['idg-base-theme/primaryProductId'] === 'undefined' && (
    <ProductSelector
      id={productId}
      prefix={__('Primary Product')}
      title={__('Select a Primary Product')}
      onSelect={id => setAttributes({ productId: Number(id) })}
    />
  );

  return (
    <InspectorControls>
      {selector}
      <PanelBody>
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
